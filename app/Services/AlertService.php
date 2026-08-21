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
}
