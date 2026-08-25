<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Setting;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Default low-stock Telegram caption template.
     * Editable via the Telegram setup page (saved in settings table).
     * Placeholders: {status_emoji} {status} {name} {name_km} {category}
     *               {sub_type} {stock} {unit} {min} {date} {datetime}
     */
    public const DEFAULT_TEMPLATE =
        "{status_emoji} {status}\n"
        . "📦 {name} ({name_km})\n"
        . "📂 {category}\n"
        . "━━━━━━━━━━━━━━━━\n"
        . "📊 Stock: {stock} {unit}\n"
        . "━━━━━━━━━━━━━━━━\n"
        . "🕐 {date}";

    /**
     * Render the alert template with a material's values.
     */
    public static function renderTemplate(string $template, Material $material, float $stock, float $min): string
    {
        $isOut = $stock <= 0;

        $replacements = [
            '{status_emoji}' => $isOut ? '🔴' : '⚠️',
            '{status}'       => $isOut ? 'Stock អស់' : 'Stock ទាប',
            '{name}'         => $material->name,
            '{name_km}'      => $material->name_km ?? '',
            '{category}'     => $material->categoryLabelShort(),
            '{sub_type}'     => $material->sub_type ?? '',
            '{stock}'        => rtrim(rtrim(number_format($stock, 2), '0'), '.'),
            '{unit}'         => $material->unit,
            '{min}'          => rtrim(rtrim(number_format($min, 2), '0'), '.'),
            '{date}'         => now()->format('d/m/Y'),
            '{datetime}'     => now()->format('d/m/Y H:i'),
        ];

        $msg = strtr($template, $replacements);

        // Clean up empty parentheses left by a missing name_km, and stray double spaces
        $msg = preg_replace('/\(\s*\)/', '', $msg);
        $msg = preg_replace('/[ \t]+\n/', "\n", $msg);
        $msg = preg_replace('/[ \t]{2,}/', ' ', $msg);

        return trim($msg);
    }
    /**
     * Check a material after a stock movement and alert if low/out.
     *
     * Rules to prevent spam:
     *  1. Only alert if stock ≤ min_stock (low) or ≤ 0 (out).
     *  2. Skip if already alerted within the cooldown window (default 24h).
     *  3. Send Telegram only to the designated alert group/topic — NOT broadcast.
     *     Configure TELEGRAM_ALERT_CHAT_ID in .env. If blank, Telegram is silent.
     */
    public function checkAndAlert(Material $material): void
    {
        if (! $material->isLowStock()) {
            return; // stock is fine
        }

        $stock    = $material->currentStock();
        $min      = (float) $material->min_stock;
        $cooldown = (int) (Setting::get('alert_cooldown_hours') ?: config('services.telegram.alert_cooldown', 24));

        // ── Cooldown check ────────────────────────────────────────────
        if ($material->last_alerted_at && $material->last_alerted_at->diffInHours(now()) < $cooldown) {
            // Already alerted recently — skip Telegram, still log internally
            return;
        }

        // ── System notification (in-app) ──────────────────────────────
        $isOut   = $stock <= 0;
        $title   = $isOut
            ? '🔴 Stock អស់ — ' . $material->name
            : '⚠️ Stock ទាប — ' . $material->name;
        $body    = "{$material->categoryLabelShort()}: {$material->name}"
            . " — Stock: {$stock} {$material->unit}"
            . ($isOut ? ' (អស់ហើយ)' : " (Min: {$min})");

        SystemNotification::notify('warning', 'stock', $title, $body, null);

        // ── Telegram alert (single target only) ───────────────────────
        // Prefer DB settings (configurable in the UI), fall back to .env
        $chatId   = Setting::get('alert_chat_id') ?: config('services.telegram.alert_chat_id');
        $threadId = Setting::get('alert_thread_id') ?: config('services.telegram.alert_thread_id');

        if ($chatId) {
            $this->sendGroupedLowStockAlert(collect([$material]), true);
        }

        // ── Mark alerted ──────────────────────────────────────────────
        $material->newQuery()->where('id', $material->id)->update(['last_alerted_at' => now()]);
    }

    private function sendTelegramAlert(
        Material $material,
        float $stock,
        float $min,
        string $chatId,
        ?int $threadId,
    ): void {
        $token = config('services.telegram.bot_token');
        if (! $token) return;

        $template = Setting::get('stock_alert_template', self::DEFAULT_TEMPLATE);
        $message  = self::renderTemplate($template, $material, $stock, $min);

        \App\Jobs\SendTelegramMessageJob::dispatch($chatId, $message, $threadId);
    }

    /**
     * Send a consolidated low stock report.
     */
    public function sendGroupedLowStockAlert($materials, bool $ignoreCooldown = false): void
    {
        $lowStockItems = [];
        $cooldown = (int) (Setting::get('alert_cooldown_hours') ?: config('services.telegram.alert_cooldown', 24));

        foreach ($materials as $material) {
            $stock = $material->calculated_stock ?? $material->currentStock();
            if ($stock > (float) $material->min_stock) {
                continue;
            }

            if (!$ignoreCooldown && $material->last_alerted_at && $material->last_alerted_at->diffInHours(now()) < $cooldown) {
                continue;
            }

            $material->calculated_stock = $stock; // ensure it's set
            $lowStockItems[] = $material;
        }

        if (empty($lowStockItems)) {
            return;
        }

        $date = now()->format('d/m/Y');
        $message = "*របាយការណ៍ស្តុក ជិតអស់*\n {$date}\n\n";

        $groupedItems = collect($lowStockItems)->groupBy(function ($item) {
            $catLabel = match($item->category) {
                'paper'      => \App\Models\Setting::get('category_label_paper', 'ក្រដាស (Paper)'),
                'film'       => \App\Models\Setting::get('category_label_film', 'Lamination Film (ស្គុត)'),
                'consumable' => \App\Models\Setting::get('category_label_consumable', 'Consumable (សម្ភារៈប្រើប្រាស់)'),
                default      => ucfirst($item->category),
            };
            return "*{$catLabel}*";
        });

        foreach ($groupedItems as $categoryLabel => $items) {
            $message .= "{$categoryLabel}\n";
            foreach ($items as $item) {
                $stock = rtrim(rtrim(number_format($item->calculated_stock, 2), '0'), '.');
                $isOut = $item->calculated_stock <= 0;
                $statusIcon = $isOut ? '🔴' : '⚠️';
                $statusText = $isOut ? '(អស់)' : '(ជិតអស់)';
                $sizeInfo = $item->size ? " ({$item->size})" : "";
                
                $message .= "{$statusIcon} {$item->name}{$sizeInfo}\n";
                $message .= "   └ Stock: {$stock} {$statusText}\n";
            }
            $message .= "\n";
        }

        $chatId = Setting::get('alert_chat_id') ?: config('services.telegram.alert_chat_id');
        $threadId = Setting::get('alert_thread_id') ?: config('services.telegram.alert_thread_id');

        if ($chatId) {
            \App\Jobs\SendTelegramMessageJob::dispatch($chatId, trim($message), $threadId ? (int) $threadId : null);
            
            foreach ($lowStockItems as $item) {
                $item->newQuery()->where('id', $item->id)->update(['last_alerted_at' => now()]);
            }
        }
    }

    /**
     * Format low-stock report message for Leaders with category grouping and status indicators.
     */
    public function formatLeaderLowStockMessage(array $items, string $reportDate, ?string $reporterName = null): string
    {
        $d = \Carbon\Carbon::parse($reportDate);
        $kmMonths = ['','មករា','កុម្ភៈ','មីនា','មេសា','ឧសភា','មិថុនា','កក្កដា','សីហា','កញ្ញា','តុលា','វិច្ឆិកា','ធ្នូ'];
        $dateStr = "{$d->format('d/m/Y')} (ថ្ងៃទី {$d->day} ខែ{$kmMonths[$d->month]} ឆ្នាំ {$d->year})";

        $customTemplate = Setting::get('low_stock_message_template');

        $grouped = collect($items)->groupBy(function ($item) {
            $cat = $item['category'] ?? 'consumable';
            return match($cat) {
                'paper'      => Setting::get('category_label_paper', 'ក្រដាស (Paper)'),
                'film'       => Setting::get('category_label_film', 'Lamination Film (ស្គុត)'),
                'consumable' => Setting::get('category_label_consumable', 'Consumable (សម្ភារៈប្រើប្រាស់)'),
                default      => ucfirst($cat),
            };
        });

        $bodyLines = [];
        foreach ($grouped as $catName => $catItems) {
            $bodyLines[] = "<b>*{$catName}*</b>";
            foreach ($catItems as $it) {
                $stock = (float)($it['current_stock'] ?? 0);
                $min = (float)($it['low_stock_threshold'] ?? $it['min_stock'] ?? 0);
                $critical = isset($it['critical_stock']) ? (float)$it['critical_stock'] : null;
                $unit = $it['unit'] ?? '';
                $name = $it['name'] ?? '';
                $nameKm = $it['name_km'] ?? '';
                $displayName = $nameKm ? "{$name} ({$nameKm})" : $name;
                $size = !empty($it['size']) ? " ({$it['size']})" : "";

                if ($stock <= 0) {
                    $icon = '🔴';
                    $statusText = 'អស់ស្តុក';
                } elseif ($critical !== null && $stock <= $critical) {
                    $icon = '🔴';
                    $statusText = 'Critical ជិតអស់ខ្លាំង';
                } else {
                    $icon = '⚠️';
                    $statusText = 'ជិតអស់';
                }

                $stockFormatted = rtrim(rtrim(number_format($stock, 2), '0'), '.');
                $bodyLines[] = "{$icon} <b>{$displayName}{$size}</b>";
                $bodyLines[] = "   └ Stock: <b>{$stockFormatted} {$unit}</b> ({$statusText})";
            }
            $bodyLines[] = "";
        }

        $itemsText = trim(implode("\n", $bodyLines));
        $byText = $reporterName ? "\n👤 អ្នករាយការណ៍: <b>{$reporterName}</b>" : '';

        if ($customTemplate) {
            return str_replace([
                '{{report_date}}',
                '{{reporter_name}}',
                '{{items_list}}',
            ], [
                $dateStr,
                $reporterName ?? '',
                $itemsText,
            ], $customTemplate);
        }

        $msg = "សូមគោរពរាយការណ៍ជូនបង ពូ 📩\n\n";
        $msg .= "<b>*របាយការណ៍ស្តុក ជិតអស់*</b>\n";
        $msg .= "📅 <b>{$dateStr}</b>\n";
        if ($byText) $msg .= "{$byText}\n";
        $msg .= "\n{$itemsText}\n\n";
        $msg .= "សូមជ្រាបជាព័ត៌មាន និងពិនិត្យស្តុកសម្រាប់ការប្រើប្រាស់បន្ត។";

        return trim($msg);
    }

    /**
     * Send Low Stock Notification to leader group(s) with duplicate prevention and history logging.
     */
    public function sendLeaderLowStockNotification(
        array $items,
        array $groupIds,
        string $reportDate,
        ?string $reporterName = null,
        ?string $category = null,
        bool $forceRetry = false
    ): array {
        if (empty($items)) {
            return ['success' => false, 'message' => 'គ្មានទំនិញស្តុកជិតអស់ទេ (No low stock items)'];
        }

        if (empty($groupIds)) {
            // Check fallback in Settings
            $configuredGroups = json_decode(Setting::get('low_stock_notification_groups', '[]'), true) ?: [];
            if (empty($configuredGroups)) {
                $alertGroup = Setting::get('alert_chat_id');
                if ($alertGroup) {
                    $configuredGroups = [$alertGroup];
                }
            }
            $groupIds = $configuredGroups;
        }

        if (empty($groupIds)) {
            return ['success' => false, 'message' => 'មិនទាន់មានក្រុមទទួលសារត្រូវបានកំណត់ដោយ Admin (No target group configured)'];
        }

        $telegramService = app(TelegramService::class);
        $messageText = $this->formatLeaderLowStockMessage($items, $reportDate, $reporterName);

        $sentCount = 0;
        $failedCount = 0;
        $logs = [];

        foreach ($groupIds as $gId) {
            // Retrieve group record if ID is numerical DB id
            $group = is_numeric($gId) ? \App\Models\TelegramGroup::find($gId) : null;
            $chatId = $group ? $group->chat_id : (string)$gId;
            $threadId = $group ? $group->message_thread_id : null;
            $groupName = $group ? $group->displayLabel() : "Group {$chatId}";

            // ── Duplicate Prevention Check ────────────────────────────
            if (!$forceRetry) {
                $existingSent = \App\Models\LowStockNotification::where('report_date', $reportDate)
                    ->where('destination_group_name', $groupName)
                    ->where('status', 'SENT')
                    ->when($category, fn($q) => $q->where('category', $category))
                    ->exists();

                if ($existingSent) {
                    $logs[] = [
                        'group'  => $groupName,
                        'status' => 'SKIPPED',
                        'reason' => 'បានផ្ញើរួចហើយសម្រាប់ថ្ងៃនេះ (Duplicate prevented)',
                    ];
                    continue;
                }
            }

            // Create notification record (PENDING)
            $record = \App\Models\LowStockNotification::create([
                'report_date'            => $reportDate,
                'category'               => $category,
                'destination_group_id'   => $group?->id,
                'destination_group_name' => $groupName,
                'items_count'            => count($items),
                'items_payload'          => $items,
                'message'                => $messageText,
                'status'                 => 'PENDING',
                'sent_by'                => $reporterName,
            ]);

            // Attempt send via Telegram
            $sent = $telegramService->sendMessage($chatId, $messageText, $threadId ? (int)$threadId : null, 'HTML');

            if ($sent) {
                $record->update([
                    'status'  => 'SENT',
                    'sent_at' => now(),
                ]);
                $sentCount++;
                $logs[] = ['group' => $groupName, 'status' => 'SENT', 'record_id' => $record->id];
            } else {
                $record->update([
                    'status'        => 'FAILED',
                    'error_message' => ' Telegram API response failed or connection timeout',
                ]);
                $failedCount++;
                $logs[] = ['group' => $groupName, 'status' => 'FAILED', 'record_id' => $record->id];
            }
        }

        return [
            'success'      => $sentCount > 0,
            'sent_count'   => $sentCount,
            'failed_count' => $failedCount,
            'logs'         => $logs,
            'message'      => $sentCount > 0
                ? "ផ្ញើសារបានសម្រេចចំនួន {$sentCount} ក្រុម"
                : "ផ្ញើសារមិនបានសម្រេច (Failed to send notification)",
        ];
    }
}

