<?php

namespace App\Services;

use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function __construct(
        private TelegramService $telegramService,
        private StockService $stockService,
        private AlertService $alertService,
        private ?DailyReportTrackerService $reportTracker = null
    ) {
        $this->reportTracker = $reportTracker ?? app(DailyReportTrackerService::class);
    }

    /**
     * Process an incoming Telegram update (Message or Callback Query).
     */
    public function handleUpdate(array $update): void
    {
        // Auto-capture Telegram sender
        try {
            $msgPayload = $update['message'] ?? $update['callback_query']['message'] ?? null;
            $from = $update['message']['from'] ?? $update['callback_query']['from'] ?? null;
            $chatTitle = $msgPayload['chat']['title'] ?? null;
            if ($from) {
                \App\Models\TelegramUser::capture($from, $chatTitle, 'chat_interaction');
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }

        // 1. Handle Callback Queries (Inline Button Taps)
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
            return;
        }

        // 2. Handle Direct Messages (Text / Commands)
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }
    }

    /**
     * Handle Inline Keyboard Button Callback Queries
     */
    private function handleCallbackQuery(array $callback): void
    {
        $chatId = (string) ($callback['message']['chat']['id'] ?? '');
        $data   = $callback['data'] ?? '';
        $user   = $callback['from'] ?? [];
        $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'User');

        if (!$chatId || !$data) return;

        // Route by callback data prefix
        if ($data === 'menu:main') {
            $this->sendMainMenu($chatId, "👋 **ជំរាបសួរ {$userName}!**\nសូមជ្រើសរើសម៉ឺនុយខាងក្រោម (Select an option):");
        } elseif ($data === 'menu:stock_out' || $data === 'out_cat:list') {
            $this->sendStockOutCategorySelection($chatId);
        } elseif ($data === 'menu:stock_balance') {
            $this->sendStockBalanceSummary($chatId);
        } elseif ($data === 'menu:my_transactions') {
            $this->sendUserTransactions($chatId, $userName);
        } elseif (str_starts_with($data, 'out_cat:')) {
            $category = substr($data, 8);
            $this->sendStockOutItemSelection($chatId, $category);
        } elseif (str_starts_with($data, 'out_item:')) {
            $materialId = (int) substr($data, 9);
            $this->sendStockOutQuantityPrompt($chatId, $materialId);
        } elseif (str_starts_with($data, 'out_qty:')) {
            [$prefix, $matId, $qty] = explode(':', $data);
            $this->sendStockOutReasonPrompt($chatId, (int)$matId, (float)$qty);
        } elseif (str_starts_with($data, 'out_reason:')) {
            $parts = explode(':', $data, 4);
            $matId  = (int) $parts[1];
            $qty    = (float) $parts[2];
            $reason = $parts[3] ?? 'Production';

            if ($reason === 'other') {
                Cache::put("tg_state_{$chatId}", [
                    'step'        => 'awaiting_custom_reason',
                    'material_id' => $matId,
                    'qty'         => $qty,
                    'user_name'   => $userName,
                ], now()->addMinutes(10));

                $this->telegramService->sendMessage(
                    $chatId,
                    "📝 **សូមវាយបញ្ជាក់មូលហេតុ (Type note for Stock Out):**\n\nឧទាហរណ៍: ប្រើសម្រាប់ម៉ាស៊ីនលេខ២ ឬ ស្គុតដាច់..."
                );
            } else {
                $this->sendStockOutConfirmationCard($chatId, $matId, $qty, $reason, '', $userName);
            }
        } elseif (str_starts_with($data, 'out_confirm:')) {
            $parts = explode(':', $data, 5);
            $matId  = (int) $parts[1];
            $qty    = (float) $parts[2];
            $reason = urldecode($parts[3] ?? 'Production');
            $note   = isset($parts[4]) ? urldecode($parts[4]) : '';

            $this->executeStockOut($chatId, $matId, $qty, $reason, $note, $userName);
        }
    }

    /**
     * Handle Text Messages & Bot Commands
     */
    private function handleMessage(array $message): void
    {
        $chatId = (string) ($message['chat']['id'] ?? '');
        $text   = trim($message['text'] ?? $message['caption'] ?? '');
        $user   = $message['from'] ?? [];
        $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'User');

        if (!$chatId || !$text) return;

        // ── Daily Production Report Tracking ──
        try {
            if ($this->reportTracker && $this->reportTracker->processIncomingMessage($message)) {
                return;
            }
        } catch (\Throwable $e) {
            Log::error('TelegramBotService DailyReportTracker error: ' . $e->getMessage());
        }

        // Check active state session
        $state = Cache::get("tg_state_{$chatId}");
        if ($state && ($state['step'] ?? '') === 'awaiting_custom_reason') {
            Cache::forget("tg_state_{$chatId}");
            $this->sendStockOutConfirmationCard(
                $chatId,
                $state['material_id'],
                $state['qty'],
                'Other',
                $text,
                $state['user_name'] ?? $userName
            );
            return;
        }

        // Standard commands & text triggers
        $textLower = strtolower($text);

        if ($textLower === '/start' || $textLower === '/menu' || $textLower === 'menu') {
            $this->sendMainMenu($chatId, "📦 **Printing Tracker Stock System**\n\n👋 ជំរាបសួរ **{$userName}**! សូមជ្រើសរើសមុខងារខាងក្រោម:");
        } elseif ($textLower === '/stockout' || str_contains($textLower, 'stock out') || str_contains($textLower, 'យកចេញ')) {
            $this->sendStockOutCategorySelection($chatId);
        } elseif ($textLower === '/balance' || str_contains($textLower, 'balance') || str_contains($textLower, 'ស្តុកសល់')) {
            $this->sendStockBalanceSummary($chatId);
        }
    }

    /**
     * Render Main Menu with Big Visual Inline Buttons
     */
    public function sendMainMenu(string $chatId, string $greetingText): void
    {
        $miniAppUrl = config('app.url') . '/telegram/app';

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📤 យកទំនិញចេញ (Stock Out)', 'callback_data' => 'menu:stock_out'],
                ],
                [
                    ['text' => '📊 ស្តុកនៅសល់ (Stock Balance)', 'callback_data' => 'menu:stock_balance'],
                    ['text' => '📜 ប្រវត្តិ (Transactions)', 'callback_data' => 'menu:my_transactions'],
                ],
                [
                    ['text' => '📱 បើក Mini App (Full Visual Menu)', 'web_app' => ['url' => $miniAppUrl]],
                ],
            ],
        ];

        $this->telegramService->sendMessage($chatId, $greetingText, null, 'Markdown', $keyboard);
    }

    /**
     * Step 1: Category Selection
     */
    private function sendStockOutCategorySelection(string $chatId): void
    {
        $text = "📤 **ជ្រើសរើសប្រភេទសម្ភារៈ (Select Category for Stock Out):**\n\nចុចលើប្រភេទខាងក្រោមដើម្បីមើលទំនិញ:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🧴 Consumables (សម្ភារៈប្រើប្រាស់)', 'callback_data' => 'out_cat:consumable'],
                ],
                [
                    ['text' => '📄 ក្រដាស (Paper)', 'callback_data' => 'out_cat:paper'],
                    ['text' => '🎞️ Film / ស្គុត', 'callback_data' => 'out_cat:film'],
                ],
                [
                    ['text' => '🖨️ Offset Supplies', 'callback_data' => 'out_cat:offset'],
                ],
                [
                    ['text' => '🔙 ត្រឡប់ក្រោយ (Main Menu)', 'callback_data' => 'menu:main'],
                ],
            ],
        ];

        $this->telegramService->sendMessage($chatId, $text, null, 'Markdown', $keyboard);
    }

    /**
     * Step 2: Item Selection under selected Category
     */
    private function sendStockOutItemSelection(string $chatId, string $category): void
    {
        $materials = Material::where('status', 'active')
            ->where('category', $category)
            ->get();

        if ($materials->isEmpty()) {
            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '🔙 ត្រឡប់ក្រោយ', 'callback_data' => 'menu:stock_out']],
                ],
            ];
            $this->telegramService->sendMessage($chatId, "⚠️ មិនទាន់មានទំនិញក្នុងប្រភេទនេះទេ។", null, 'Markdown', $keyboard);
            return;
        }

        $buttons = [];
        foreach ($materials as $m) {
            $stock = $m->currentStock();
            $label = ($m->icon ? $m->icon . ' ' : '') . ($m->name_km ?: $m->name) . " (សល់: {$stock} {$m->unit})";
            $buttons[] = [
                ['text' => $label, 'callback_data' => "out_item:{$m->id}"],
            ];
        }

        $buttons[] = [['text' => '🔙 ជ្រើសរើសប្រភេទផ្សេង', 'callback_data' => 'menu:stock_out']];

        $catName = match($category) {
            'consumable' => '🧴 Consumable Items',
            'paper'      => '📄 Paper Items',
            'film'       => '🎞️ Film & Lamination',
            'offset'     => '🖨️ Offset Supplies',
            default      => ucfirst($category),
        };

        $text = "📦 **{$catName}**\n\nជ្រើសរើសមុខទំនិញដែលអ្នកចង់យកចេញ:";
        $this->telegramService->sendMessage($chatId, $text, null, 'Markdown', ['inline_keyboard' => $buttons]);
    }

    /**
     * Step 3: Quantity Selection for chosen item
     */
    private function sendStockOutQuantityPrompt(string $chatId, int $materialId): void
    {
        $material = Material::find($materialId);
        if (!$material) return;

        $stock = $material->currentStock();
        $name  = $material->name_km ? "{$material->name} ({$material->name_km})" : $material->name;

        $text = "📦 **" . htmlspecialchars($name) . "**\n";
        $text .= "• **ស្តុកបច្ចុប្បន្ន (Current Stock):** `{$stock} {$material->unit}`\n";
        $text .= "• **ឯកតា (Unit):** `{$material->unit}`\n\n";
        $text .= "❓ **តើអ្នកចង់យកចេញចំនួនប៉ុន្មាន? (Select Quantity Out):**";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '1 ' . $material->unit, 'callback_data' => "out_qty:{$material->id}:1"],
                    ['text' => '2 ' . $material->unit, 'callback_data' => "out_qty:{$material->id}:2"],
                    ['text' => '3 ' . $material->unit, 'callback_data' => "out_qty:{$material->id}:3"],
                ],
                [
                    ['text' => '5 ' . $material->unit, 'callback_data' => "out_qty:{$material->id}:5"],
                    ['text' => '10 ' . $material->unit, 'callback_data' => "out_qty:{$material->id}:10"],
                ],
                [
                    ['text' => '🔙 ត្រឡប់ក្រោយ', 'callback_data' => "out_cat:{$material->category}"],
                ],
            ],
        ];

        $this->telegramService->sendMessage($chatId, $text, null, 'Markdown', $keyboard);
    }

    /**
     * Step 4: Purpose / Reason Selection
     */
    private function sendStockOutReasonPrompt(string $chatId, int $materialId, float $qty): void
    {
        $material = Material::find($materialId);
        if (!$material) return;

        $name = $material->name_km ?: $material->name;

        $text = "🎯 **ជ្រើសរើសគោលបំណង/មូលហេតុ (Select Purpose/Reason):**\n\n";
        $text .= "• ទំនិញ: **{$name}**\n";
        $text .= "• ចំនួនយកចេញ: **{$qty} {$material->unit}**\n\n";
        $text .= "ចុចលើមូលហេតុខាងក្រោម:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🏭 ការផលិត (Production)', 'callback_data' => "out_reason:{$materialId}:{$qty}:Production"],
                ],
                [
                    ['text' => '🔧 ថែទាំម៉ាស៊ីន (Machine maintenance)', 'callback_data' => "out_reason:{$materialId}:{$qty}:Machine maintenance"],
                ],
                [
                    ['text' => '🧹 សម្អាត (Cleaning)', 'callback_data' => "out_reason:{$materialId}:{$qty}:Cleaning"],
                ],
                [
                    ['text' => '⚠️ ខូចខាត (Damaged)', 'callback_data' => "out_reason:{$materialId}:{$qty}:Damaged"],
                ],
                [
                    ['text' => '📝 ផ្សេងៗ (Other Note)', 'callback_data' => "out_reason:{$materialId}:{$qty}:other"],
                ],
                [
                    ['text' => '🔙 ត្រឡប់ក្រោយ', 'callback_data' => "out_item:{$materialId}"],
                ],
            ],
        ];

        $this->telegramService->sendMessage($chatId, $text, null, 'Markdown', $keyboard);
    }

    /**
     * Step 5: Confirmation Summary Card
     */
    private function sendStockOutConfirmationCard(
        string $chatId,
        int $materialId,
        float $qty,
        string $reason,
        string $note,
        string $userName
    ): void {
        $material = Material::find($materialId);
        if (!$material) return;

        $currentStock   = $material->currentStock();
        $remainingStock = max(0, $currentStock - $qty);
        $name           = $material->name_km ? "{$material->name} — {$material->name_km}" : $material->name;
        $dateStr        = now()->format('d/m/Y');
        $timeStr        = now()->format('H:i');

        $reasonDisplay = match($reason) {
            'Production'          => '🏭 ការផលិត (Production)',
            'Machine maintenance' => '🔧 ថែទាំម៉ាស៊ីន (Machine maintenance)',
            'Cleaning'            => '🧹 សម្អាត (Cleaning)',
            'Damaged'             => '⚠️ ខូចខាត (Damaged)',
            default               => '📝 ' . ($note ?: $reason),
        };

        $text = "📋 **ផ្ទៀងផ្ទាត់ការដកស្តុកចេញ (Confirm Stock Out)**\n\n";
        $text .= "• **ទំនិញ (Item):** {$name}\n";
        $text .= "• **ចំនួនដក (Qty Out):** `{$qty} {$material->unit}`\n";
        $text .= "• **អ្នកដក (Taken by):** 👤 `{$userName}`\n";
        $text .= "• **គោលបំណង (Reason):** {$reasonDisplay}\n";
        $text .= "• **កាលបរិច្ឆេទ (Date/Time):** `{$dateStr} {$timeStr}`\n";
        $text .= "• **ស្តុកបច្ចុប្បន្ន (Current):** `{$currentStock} {$material->unit}`\n";
        $text .= "• **ស្តុកនៅសល់ (Remaining):** `{$remainingStock} {$material->unit}`\n";

        if ($qty > $currentStock) {
            $text .= "\n⚠️ **កម្រិតប្រុងប្រយ័ត្ន:** ចំនួនដកលើសពីស្តុកដែលមាន!";
        }

        $encReason = urlencode($reason);
        $encNote   = urlencode($note);

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ បញ្ជាក់ដកស្តុក (Confirm Stock Out)', 'callback_data' => "out_confirm:{$materialId}:{$qty}:{$encReason}:{$encNote}"],
                ],
                [
                    ['text' => '❌ បោះបង់ (Cancel)', 'callback_data' => 'menu:main'],
                ],
            ],
        ];

        $this->telegramService->sendMessage($chatId, $text, null, 'Markdown', $keyboard);
    }

    /**
     * Step 6: Commit Stock Out Transaction
     */
    public function executeStockOut(
        string $chatId,
        int $materialId,
        float $qty,
        string $reason,
        string $note,
        string $performedBy,
        ?string $recorderName = null
    ): bool {
        $material = Material::find($materialId);
        if (!$material) {
            $this->telegramService->sendMessage($chatId, "❌ រកមិនឃើញទំនិញក្នុងប្រព័ន្ធទេ។");
            return false;
        }

        $current = $material->currentStock();
        if ($qty > $current) {
            $this->telegramService->sendMessage(
                $chatId,
                "⚠️ **បរាជ័យ:** ស្តុកមិនគ្រប់គ្រាន់! ({$material->name} មានសល់ត្រឹម {$current} {$material->unit})"
            );
            return false;
        }

        // 1. Record stock movement
        $fullNote = $reason === 'Other' || !empty($note) ? trim("{$reason} - {$note}") : $reason;

        $movement = $this->stockService->recordMovement(
            $material,
            'out',
            $qty,
            'Telegram Stock Out',
            $performedBy,
            $fullNote,
            now()->toDateString(),
            $reason
        );

        $remaining = $material->currentStock();

        // 2. Low Stock Alert Check
        $this->alertService->checkAndAlert($material);

        // Helper to format Khmer date
        $monthsKh = [
            1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា', 5 => 'ឧសភា', 6 => 'មិថុនា',
            7 => 'កក្កដា', 8 => 'សីហា', 9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ'
        ];
        $dateKhmer = now()->format('j') . ' ' . ($monthsKh[(int)now()->format('n')] ?? '') . ' ' . now()->format('Y');
        $timeStr   = now()->format('h:i A');

        $reasonMap = [
            'Production'          => 'ប្រើប្រាស់ក្នុងការបោះពុម្ព',
            'Machine maintenance' => 'ថែទាំម៉ាស៊ីន',
            'Cleaning'            => 'សម្អាត',
            'Damaged'             => 'ខូចខាត',
            'Other'               => 'ផ្សេងៗ',
        ];
        $reasonKh = $reasonMap[$reason] ?? $reason;
        if ($note && $reason === 'Other') {
            $reasonKh .= " ({$note})";
        } elseif ($note && $reason !== 'Other') {
            $reasonKh .= " - {$note}";
        }

        $refCode  = 'SO-' . now()->format('Ymd') . '-' . str_pad($movement->id ?? 1, 4, '0', STR_PAD_LEFT);
        $itemName = $material->name_km ?: $material->name;
        $unitStr  = $material->unit ?: 'pcs';
        $warn     = ($remaining <= 0 || (!empty($material->min_stock) && $remaining <= $material->min_stock)) ? ' ⚠️' : '';

        // 3. Post live notification to configured Telegram Usage Channel
        $targetChatId = \App\Models\Setting::get('stock_out_chat_id') ?: \App\Models\Setting::get('daily_usage_chat_id', $chatId);
        $targetThread = \App\Models\Setting::get('stock_out_thread_id') ?: \App\Models\Setting::get('daily_usage_thread_id');

        $takenByHtml = htmlspecialchars($performedBy);
        $actorLine = "<b>អ្នកដក:</b> {$takenByHtml}";
        if (!empty($recorderName)) {
            $actorLine .= " | <b>អ្នកកត់ត្រា:</b> " . htmlspecialchars($recorderName);
        }

        $divider = "━━━━━━━━━━━━━━━";
        $msg = "<b>របាយការណ៍ដកស្តុកប្រើប្រាស់</b>\n" .
               "{$divider}\n" .
               "<b>លេខយោង:</b> <code>{$refCode}</code>\n" .
               "<b>កាលបរិច្ឆេទ:</b> {$dateKhmer} | {$timeStr}\n" .
               "{$actorLine}\n" .
               "<b>គោលបំណង:</b> " . htmlspecialchars($reasonKh) . "\n" .
               "{$divider}\n\n" .
               "<b>សម្ភារៈដែលបានយកប្រើប្រាស់ (1 មុខ)</b>\n\n" .
               "<b>01. " . htmlspecialchars($itemName) . "</b>\n" .
               "• យកប្រើប្រាស់: <b>{$qty} " . htmlspecialchars($unitStr) . "</b>\n" .
               "• ស្តុកនៅសល់: <b>{$remaining} " . htmlspecialchars($unitStr) . "</b>{$warn}\n\n" .
               "{$divider}\n" .
               "<b>កំណត់ត្រាត្រូវបានបង្កើតដោយស្វ័យប្រវត្តិ</b>";

        if ($targetChatId !== $chatId) {
            $this->telegramService->sendMessage($targetChatId, $msg, $targetThread ? (int)$targetThread : null, 'HTML');
        }

        // 4. Send Confirmation Receipt to user
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📤 ដកស្តុកបន្ថែម (Stock Out More)', 'callback_data' => 'menu:stock_out'],
                    ['text' => '📊 មើលស្តុក (Stock Balance)', 'callback_data' => 'menu:stock_balance'],
                ],
                [
                    ['text' => '🏠 ម៉ឺនុយដើម (Main Menu)', 'callback_data' => 'menu:main'],
                ],
            ],
        ];

        $this->telegramService->sendMessage($chatId, $msg, null, 'HTML', $keyboard);
        return true;
    }

    /**
     * Helper: Send Stock Balance Summary
     */
    private function sendStockBalanceSummary(string $chatId): void
    {
        $levels = $this->stockService->getAllStockLevels();

        $text = "📊 **របាយការណ៍ស្តុកបច្ចុប្បន្ន (Current Stock Balance)**\n\n";
        foreach ($levels as $item) {
            $statusEmoji = match($item['stock_status']) {
                'OUT_OF_STOCK' => '⚫',
                'CRITICAL'     => '🔴',
                'LOW_STOCK'    => '🟡',
                default        => '🟢',
            };
            $name = $item['name_km'] ? "{$item['name']} ({$item['name_km']})" : $item['name'];
            $text .= "{$statusEmoji} **{$name}**: `{$item['current_stock']} {$item['unit']}`\n";
        }

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📤 យកទំនិញចេញ (Stock Out)', 'callback_data' => 'menu:stock_out']],
                [['text' => '🔙 ម៉ឺនុយដើម', 'callback_data' => 'menu:main']],
            ],
        ];

        $this->telegramService->sendMessage($chatId, $text, null, 'Markdown', $keyboard);
    }

    /**
     * Helper: Send User Recent Transactions
     */
    private function sendUserTransactions(string $chatId, string $userName): void
    {
        $recent = StockMovement::with('material')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $text = "📜 **ប្រវត្តិដក/បញ្ចូលស្តុកចុងក្រោយ (Recent Transactions)**\n\n";
        if ($recent->isEmpty()) {
            $text .= "មិនទាន់មានប្រតិបត្តិការនៅឡើយទេ។";
        } else {
            foreach ($recent as $m) {
                $icon = match($m->type) {
                    'in'     => '📥',
                    'out'    => '📤',
                    'adjust' => '🔧',
                };
                $matName = $m->material ? ($m->material->name_km ?: $m->material->name) : 'Item';
                $by = $m->performed_by ? " (👤 {$m->performed_by})" : '';
                $text .= "{$icon} **{$matName}**: `{$m->quantity}` — {$m->reason}{$by}\n";
                $text .= "   🕒 " . $m->created_at->format('d/m/Y H:i') . "\n";
            }
        }

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🔙 ម៉ឺនុយដើម', 'callback_data' => 'menu:main']],
            ],
        ];

        $this->telegramService->sendMessage($chatId, $text, null, 'Markdown', $keyboard);
    }
}
