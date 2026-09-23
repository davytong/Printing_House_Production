<?php

namespace App\Http\Controllers;

use App\Models\TelegramGroup;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TelegramSetupController extends Controller
{
    private function apiGet(string $endpoint, array $params = []): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::timeout(10)
                ->withOptions(['curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.167.220']]])
                ->get($this->apiBase() . $endpoint, $params);
        } catch (\Throwable $e) {
            Log::error("TelegramSetup API GET failed: $endpoint", ['error' => TelegramService::redactApiError($e->getMessage())]);
            return null;
        }
    }

    private function apiPost(string $endpoint, array $params = []): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::timeout(10)
                ->withOptions(['curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.167.220']]])
                ->post($this->apiBase() . $endpoint, $params);
        } catch (\Throwable $e) {
            Log::error("TelegramSetup API POST failed: $endpoint", ['error' => TelegramService::redactApiError($e->getMessage())]);
            return null;
        }
    }

    private function token(): string
    {
        return config('services.telegram.bot_token', '');
    }

    private function apiBase(): string
    {
        return 'https://api.telegram.org/bot' . $this->token();
    }

    // ─────────────────────────────────────────────
    // Setup / status page
    // ─────────────────────────────────────────────
    public function index(): View
    {
        $token  = $this->token();
        $botInfo = null;
        $webhookInfo = null;
        $botError = null;

        if ($token) {
            try {
                $me = Http::timeout(6)->withOptions(['curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.167.220']]])->get($this->apiBase() . '/getMe');
                if ($me->successful() && $me->json('ok')) {
                    $botInfo = $me->json('result');
                } else {
                    $botError = $me->json('description') ?? 'Could not reach Telegram API';
                }

                $wh = Http::timeout(6)->withOptions(['curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.167.220']]])->get($this->apiBase() . '/getWebhookInfo');
                if ($wh->successful() && $wh->json('ok')) {
                    $webhookInfo = $wh->json('result');
                }
            } catch (\Throwable $e) {
                $botError = 'Connection error: ' . $e->getMessage();
            }
        }

        // Group by chat_id so forum topics show under their parent group
        $groups     = TelegramGroup::orderBy('chat_id')->orderBy('message_thread_id')->get();
        $groupedChats = $groups->groupBy('chat_id');

        $appUrl  = config('app.url');

        $alertTemplate = \App\Models\Setting::get('stock_alert_template', \App\Services\AlertService::DEFAULT_TEMPLATE);
        $dailyReportTemplate = \App\Models\Setting::get('daily_report_template', $this->getDefaultDailyReportTemplate());
        $stockOutTemplate = \App\Models\Setting::get('stock_out_template', $this->getDefaultStockOutTemplate());

        // Low-stock alert destination + cooldown (DB-driven, falls back to .env)
        $alertConfig = [
            'chat_id'   => \App\Models\Setting::get('alert_chat_id', config('services.telegram.alert_chat_id')),
            'thread_id' => \App\Models\Setting::get('alert_thread_id', config('services.telegram.alert_thread_id')),
            'cooldown'  => (int) \App\Models\Setting::get('alert_cooldown_hours', config('services.telegram.alert_cooldown', 24)),
        ];
        
        // Daily Stock Usage destination (DB-driven)
        $dailyUsageConfig = [
            'chat_id'   => \App\Models\Setting::get('daily_usage_chat_id', ''),
            'thread_id' => \App\Models\Setting::get('daily_usage_thread_id', ''),
        ];

        // Stock Out Notification destination (DB-driven, falls back to daily_usage)
        $stockOutConfig = [
            'chat_id'   => \App\Models\Setting::get('stock_out_chat_id', \App\Models\Setting::get('daily_usage_chat_id', '')),
            'thread_id' => \App\Models\Setting::get('stock_out_thread_id', \App\Models\Setting::get('daily_usage_thread_id', '')),
        ];
        
        // Category labels
        $categoryLabels = [
            'paper' => \App\Models\Setting::get('category_label_paper', 'ក្រដាស (Paper)'),
            'film' => \App\Models\Setting::get('category_label_film', 'Lamination Film (ស្គុត)'),
            'consumable' => \App\Models\Setting::get('category_label_consumable', 'Consumable (សម្ភារៈប្រើប្រាស់)'),
        ];
        
        // Language format for item names (per category)
        $itemNameFormats = [
            'paper' => \App\Models\Setting::get('telegram_item_name_format_paper', 'both'),
            'film' => \App\Models\Setting::get('telegram_item_name_format_film', 'both'),
            'consumable' => \App\Models\Setting::get('telegram_item_name_format_consumable', 'both'),
        ];

        return view('telegram.setup', compact(
            'token', 'botInfo', 'webhookInfo', 'botError',
            'groups', 'groupedChats', 'appUrl', 'alertTemplate', 'dailyReportTemplate', 'stockOutTemplate', 'categoryLabels', 'itemNameFormats', 'alertConfig', 'dailyUsageConfig', 'stockOutConfig'
        ));
    }

    public function saveStockOutConfig(Request $request): RedirectResponse
    {
        $target = $request->input('stock_out_target');

        if ($target && str_contains($target, '|')) {
            [$chatId, $threadIdStr] = explode('|', $target, 2);
            $threadId = $threadIdStr !== '' ? (int)$threadIdStr : null;
        } else {
            $chatId   = $target;
            $threadId = null;
        }

        \App\Models\Setting::set('stock_out_chat_id', $chatId ?: '');
        \App\Models\Setting::set('stock_out_thread_id', $threadId !== null ? (string)$threadId : '');

        return redirect()->route('telegram.setup')
            ->with('success', 'បានរក្សាទុកក្រុម/Topic គោលដៅដកស្តុក (Stock Out Notification Target)!');
    }

    public function getDefaultStockOutTemplate(): string
    {
        return "<b>[ ប័ណ្ណបញ្ចេញស្តុកទំនិញ / STOCK DISPATCH ]</b>\n" .
               "─────────────────────────────\n" .
               "<b>លេខយោង (Ref):</b> <code>{ref_code}</code>\n" .
               "<b>កាលបរិច្ឆេទ (Date):</b> {date} | {time}\n" .
               "<b>អ្នកទទួល (Recipient):</b> {performed_by}\n" .
               "<b>គោលបំណង (Purpose):</b> {reason}\n" .
               "─────────────────────────────\n" .
               "<b>សម្ភារៈ:</b> <b>{name}</b>\n" .
               "<b>ចំនួនបញ្ចេញ:</b> <b>{quantity} {unit}</b>\n" .
               "<b>ស្តុកនៅសល់:</b> <b>{stock_remaining} {unit}</b> (មុនដក: {stock_before} {unit})\n" .
               "─────────────────────────────\n" .
               "<i>កត់ត្រាដោយស្វ័យប្រវត្តិតាមរយៈប្រព័ន្ធ PrintTracker Pro</i>";
    }

    public function saveStockOutTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stock_out_template' => 'required|string|max:2000',
        ]);

        \App\Models\Setting::set('stock_out_template', $data['stock_out_template']);

        return redirect()->route('telegram.setup')
            ->with('success', 'បានរក្សាទុក Template ការជូនដំណឹងដកស្តុក!');
    }

    public function resetStockOutTemplate(): RedirectResponse
    {
        \App\Models\Setting::set('stock_out_template', $this->getDefaultStockOutTemplate());
        return redirect()->route('telegram.setup')
            ->with('success', 'បានកំណត់ Template ត្រឡប់ទៅលំនាំដើម!');
    }

    // ─────────────────────────────────────────────
    // Save the low-stock alert caption template
    // ─────────────────────────────────────────────
    public function saveAlertTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alert_template' => 'required|string|max:2000',
        ]);

        \App\Models\Setting::set('stock_alert_template', $data['alert_template']);

        return redirect()->route('telegram.setup')
            ->with('success', 'បានរក្សាទុក Template ការជូនដំណឹង Stock!');
    }

    // ─────────────────────────────────────────────
    // Reset alert template to default
    // ─────────────────────────────────────────────
    public function resetAlertTemplate(): RedirectResponse
    {
        \App\Models\Setting::set('stock_alert_template', \App\Services\AlertService::DEFAULT_TEMPLATE);
        return redirect()->route('telegram.setup')
            ->with('success', 'បានកំណត់ Template ត្រឡប់ទៅលំនាំដើម!');
    }

    // ─────────────────────────────────────────────
    // Save Low-Stock Alert destination + cooldown (no .env editing needed)
    // ─────────────────────────────────────────────
    public function saveAlertConfig(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alert_target'   => 'nullable|string|max:100', // "chatId|threadId" composite
            'alert_cooldown' => 'required|integer|min:1|max:168',
        ]);

        $chatId   = '';
        $threadId = '';
        if (!empty($data['alert_target'])) {
            [$chatId, $threadPart] = array_pad(explode('|', $data['alert_target'], 2), 2, '');
            $threadId = ($threadPart !== '' && $threadPart !== null) ? (string) (int) $threadPart : '';
        }

        \App\Models\Setting::set('alert_chat_id', $chatId);
        \App\Models\Setting::set('alert_thread_id', $threadId);
        \App\Models\Setting::set('alert_cooldown_hours', (string) $data['alert_cooldown']);

        return redirect()->route('telegram.setup')
            ->with('success', 'បានរក្សាទុកគោលដៅ Alert!');
    }

    // ─────────────────────────────────────────────
    // Save Daily Stock Usage destination
    // ─────────────────────────────────────────────
    public function saveDailyUsageConfig(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'daily_usage_target' => 'nullable|string|max:100', // "chatId|threadId" composite
        ]);

        $chatId   = '';
        $threadId = '';
        if (!empty($data['daily_usage_target'])) {
            [$chatId, $threadPart] = array_pad(explode('|', $data['daily_usage_target'], 2), 2, '');
            $threadId = $threadPart;
        }

        \App\Models\Setting::set('daily_usage_chat_id', $chatId);
        \App\Models\Setting::set('daily_usage_thread_id', $threadId);

        return redirect()->route('telegram.setup')
            ->with('success', 'បានរក្សាទុកគោលដៅ Daily Stock Usage Report!');
    }

    // ─────────────────────────────────────────────
    // Send a test alert to verify formatting
    // ─────────────────────────────────────────────
    public function sendTestAlert(\App\Services\AlertService $alertService, TelegramService $telegramService): RedirectResponse
    {
        $dummy1 = new \App\Models\Material([
            'name' => 'Premium Glossy Paper 200gsm',
            'category' => 'paper',
            'min_stock' => 500,
        ]);
        $dummy1->calculated_stock = 450.5;

        $dummy2 = new \App\Models\Material([
            'name' => 'Lamination Film Matte',
            'category' => 'film',
            'min_stock' => 10,
        ]);
        $dummy2->calculated_stock = 0;

        // Test traffic is deliberately isolated from staff work groups. The
        // production alert target is never consulted for this action.
        $message = "🧪 [TEST] " . now()->format('d/m/Y H:i') . "\n\n"
            . $alertService->formatLeaderLowStockMessage([
                ['name' => $dummy1->name, 'category' => $dummy1->category, 'current_stock' => 450.5, 'min_stock' => 500, 'unit' => 'sheet'],
                ['name' => $dummy2->name, 'category' => $dummy2->category, 'current_stock' => 0, 'min_stock' => 10, 'unit' => 'roll'],
            ], now()->toDateString());

        if (! $telegramService->sendMessage(TelegramService::TESTING_GROUPS[0], $message, null, 'HTML')) {
            return redirect()->back()->with('error', 'មិនអាចផ្ញើសារសាកល្បងទៅ Testing Group បានទេ។ សូមពិនិត្យការភ្ជាប់ Bot។');
        }

        return redirect()->back()->with('success', 'សារសាកល្បងត្រូវបានបញ្ជូនទៅ Testing Group រួចរាល់។');
    }

    // ─────────────────────────────────────────────
    // Save daily report template
    // ─────────────────────────────────────────────
    public function saveDailyReportTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'daily_report_template' => 'required|string|max:2000',
        ]);

        \App\Models\Setting::set('daily_report_template', $data['daily_report_template']);

        return redirect()->route('telegram.setup')
            ->with('success', 'បានរក្សាទុក Template រាយការណ៍ប្រចាំថ្ងៃ!');
    }

    // ─────────────────────────────────────────────
    // Reset daily report template to default
    // ─────────────────────────────────────────────
    public function resetDailyReportTemplate(): RedirectResponse
    {
        \App\Models\Setting::set('daily_report_template', $this->getDefaultDailyReportTemplate());
        return redirect()->route('telegram.setup')
            ->with('success', 'បានកំណត់ Template ត្រឡប់ទៅលំនាំដើម!');
    }

    // ─────────────────────────────────────────────
    // Get default daily report template
    // ─────────────────────────────────────────────
    private function getDefaultDailyReportTemplate(): string
    {
        return "សូមគោរពរាយការណ៍ជូនបង ពូ 📩\nថ្ងៃទី {date}\n\n{emoji} {category} នៅសល់មានចំនួន:\n{items}\n{person}\n{hashtag}";
    }

    // ─────────────────────────────────────────────
    // Save category labels
    // ─────────────────────────────────────────────
    public function saveCategoryLabels(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label_paper' => 'required|string|max:100',
            'label_film' => 'required|string|max:100',
            'label_consumable' => 'required|string|max:100',
        ]);

        \App\Models\Setting::set('category_label_paper', $data['label_paper']);
        \App\Models\Setting::set('category_label_film', $data['label_film']);
        \App\Models\Setting::set('category_label_consumable', $data['label_consumable']);

        return redirect()->route('telegram.setup')
            ->with('success', 'បានរក្សាទុក Category Labels!');
    }

    // ─────────────────────────────────────────────
    // Reset category labels to default
    // ─────────────────────────────────────────────
    public function resetCategoryLabels(): RedirectResponse
    {
        \App\Models\Setting::set('category_label_paper', 'ក្រដាស (Paper)');
        \App\Models\Setting::set('category_label_film', 'Lamination Film (ស្គុត)');
        \App\Models\Setting::set('category_label_consumable', 'Consumable (សម្ភារៈប្រើប្រាស់)');
        
        return redirect()->route('telegram.setup')
            ->with('success', 'បានកំណត់ Category Labels ត្រឡប់ទៅលំនាំដើម!');
    }

    // ─────────────────────────────────────────────
    // Save item name format (both languages or Khmer only) - per category
    // ─────────────────────────────────────────────
    public function saveItemNameFormat(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'format_paper' => 'required|in:both,khmer,english',
            'format_film' => 'required|in:both,khmer,english',
            'format_consumable' => 'required|in:both,khmer,english',
        ]);

        \App\Models\Setting::set('telegram_item_name_format_paper', $data['format_paper']);
        \App\Models\Setting::set('telegram_item_name_format_film', $data['format_film']);
        \App\Models\Setting::set('telegram_item_name_format_consumable', $data['format_consumable']);

        return redirect()->route('telegram.setup')
            ->with('success', 'បានរក្សាទុកទម្រង់ឈ្មោះទំនិញ!');
    }

    // ─────────────────────────────────────────────
    // Set webhook
    // ─────────────────────────────────────────────
    public function setWebhook(Request $request): RedirectResponse
    {
        $request->validate([
            'webhook_url' => 'required|url',
        ]);

        $url = rtrim($request->webhook_url, '/') . '/api/telegram/webhook';

        $params = [
            'url'                  => $url,
            'drop_pending_updates' => true,
            'allowed_updates'      => json_encode(['message', 'edited_message', 'my_chat_member']),
        ];

        // Attach secret token if configured
        $secret = config('services.telegram.webhook_secret');
        if ($secret) {
            $params['secret_token'] = $secret;
        }

        $response = $this->apiGet('/setWebhook', $params);

        if ($response && $response->successful() && $response->json('ok')) {
            return back()->with('success', 'Webhook set: ' . $url . ($secret ? ' (secret token active ✅)' : ''));
        }

        return back()->with('error', 'Failed: ' . ($response?->json('description') ?? 'Connection error'));
    }

    // ─────────────────────────────────────────────
    // Delete webhook (switch to polling mode)
    // ─────────────────────────────────────────────
    public function deleteWebhook(): RedirectResponse
    {
        $response = $this->apiGet('/deleteWebhook', ['drop_pending_updates' => true]);

        if ($response && $response->successful() && $response->json('ok')) {
            return back()->with('success', 'Webhook removed. You can now use php artisan telegram:poll');
        }

        return back()->with('error', 'Failed: ' . ($response?->json('description') ?? 'Connection error'));
    }

    // ─────────────────────────────────────────────
    // Set Telegram Bot Menu Button (Web App Link)
    // ─────────────────────────────────────────────
    public function setMenuButton(Request $request): RedirectResponse
    {
        $appUrl = $request->input('app_url') ?: config('app.url') . '/telegram/app';

        $response = $this->apiPost('/setChatMenuButton', [
            'menu_button' => [
                'type'    => 'web_app',
                'text'    => '📦 ដកស្តុក (Stock Out)',
                'web_app' => [
                    'url' => $appUrl,
                ],
            ],
        ]);

        if ($response && $response->successful() && $response->json('ok')) {
            return back()->with('success', 'Telegram Bot Menu Button set successfully! Users can now tap 📦 ដកស្តុក directly in Telegram.');
        }

        $err = $response ? ($response->json('description') ?? 'Failed to set Menu Button') : 'Connection error';
        return back()->with('error', 'Failed: ' . $err);
    }

    // ─────────────────────────────────────────────
    // Poll once (runs telegram:poll and returns result)
    // ─────────────────────────────────────────────
    public function pollNow(): RedirectResponse
    {
        // Must have no webhook set to use getUpdates
        $wh = $this->apiGet('/getWebhookInfo');
        $webhookUrl = $wh?->json('result.url') ?? '';

        if ($webhookUrl) {
            return back()->with('error',
                'Cannot poll while a webhook is active. Remove the webhook first, then poll.');
        }

        $offset   = cache('telegram_offset', 0);
        $response = $this->apiGet('/getUpdates', ['offset' => $offset, 'timeout' => 5, 'limit' => 100]);

        if (! $response || ! $response->successful()) {
            return back()->with('error', 'Telegram API error or connection failed');
        }

        $results = $response->json('result') ?? [];
        $saved   = 0;

        foreach ($results as $update) {
            $offset = $update['update_id'] + 1;
            $chat   = $update['message']['chat']
                   ?? $update['my_chat_member']['chat']
                   ?? null;

            if ($chat && in_array($chat['type'] ?? '', ['group', 'supergroup'])) {
                TelegramGroup::updateOrCreate(
                    ['chat_id' => $chat['id'], 'message_thread_id' => null],
                    [
                        'name'     => $chat['title'] ?? 'Unknown Group',
                        'type'     => $chat['type'],
                        'is_forum' => (bool) ($chat['is_forum'] ?? false),
                    ]
                );
                $saved++;
            }
        }

        cache(['telegram_offset' => $offset], 3600);

        $msg = count($results) . ' update(s) processed, ' . $saved . ' group(s) saved.';
        return back()->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    // Manually add a group by chat ID
    // ─────────────────────────────────────────────
    public function addGroup(Request $request): RedirectResponse
    {
        $request->validate([
            'chat_id'           => 'required',
            'group_name'        => 'required|string|max:255',
            'message_thread_id' => 'nullable|integer|min:1',
            'topic_name'        => 'nullable|string|max:255',
            'purpose'           => 'nullable|string|max:50',
        ]);

        $chatId   = $request->chat_id;
        $threadId = $request->integer('message_thread_id') ?: null;

        // Verify the chat_id is reachable
        $check = $this->apiGet('/getChat', ['chat_id' => $chatId]);

        if (! $check || ! $check->successful() || ! $check->json('ok')) {
            return back()->with('error',
                'Could not verify chat ID. Make sure the bot is a member of the group. ' .
                'Error: ' . ($check?->json('description') ?? 'Connection failed'));
        }

        $chat     = $check->json('result');
        $isForum  = (bool) ($chat['is_forum'] ?? false);

        // If topic group: allow multiple entries (same chat_id, different thread_id)
        TelegramGroup::updateOrCreate(
            ['chat_id' => $chatId, 'message_thread_id' => $threadId],
            [
                'name'              => $chat['title'] ?? $request->group_name,
                'type'              => $chat['type'] ?? 'supergroup',
                'is_forum'          => $isForum,
                'topic_name'        => $request->input('topic_name') ?: null,
                'purpose'           => $request->input('purpose') ?: null,
            ]
        );

        $label = ($chat['title'] ?? $request->group_name)
            . ($threadId ? " › {$request->input('topic_name', 'Thread #'.$threadId)}" : '');

        return back()->with('success', "Added: \"{$label}\"" . ($isForum ? ' (Forum group ✅)' : ''));
    }

    // ─────────────────────────────────────────────
    // Remove a group
    // ─────────────────────────────────────────────
    public function removeGroup(TelegramGroup $group): RedirectResponse
    {
        $name = $group->name;
        $group->delete();
        return back()->with('success', "Group \"{$name}\" removed.");
    }

    // ─────────────────────────────────────────────
    // Update a group's purpose (inline from the list)
    // ─────────────────────────────────────────────
    public function updatePurpose(Request $request, TelegramGroup $group): RedirectResponse
    {
        $request->validate([
            'purpose' => 'nullable|string|max:50',
        ]);

        // If setting a purpose, clear it from any other group that had it (one purpose = one destination)
        $newPurpose = $request->input('purpose') ?: null;
        if ($newPurpose) {
            TelegramGroup::where('purpose', $newPurpose)
                ->where('id', '!=', $group->id)
                ->update(['purpose' => null]);
        }

        $group->update(['purpose' => $newPurpose]);

        $label = $newPurpose ? "Set \"{$group->displayLabel()}\" → {$newPurpose}" : "Cleared purpose for \"{$group->displayLabel()}\"";
        return back()->with('success', $label);
    }

    // ─────────────────────────────────────────────
    // Send a test message to verify a group works
    // ─────────────────────────────────────────────
    public function testGroup(TelegramGroup $group, TelegramService $telegramService): RedirectResponse
    {
        if (! in_array((string) $group->chat_id, TelegramService::TESTING_GROUPS, true)) {
            return back()->with('error', 'ការសាកល្បងអាចផ្ញើបានតែទៅ Testing Group ប៉ុណ្ណោះ។');
        }

        $message = "🧪 [TEST] PrintTracker connected!\n"
                          . ($group->topic_name ? "Topic: {$group->topic_name}\n" : "")
                          . "Time: " . now()->format('d/m/Y H:i:s');

        if ($telegramService->sendMessage($group->chat_id, $message, $group->message_thread_id)) {
            return back()->with('success', 'Test message sent to "' . $group->displayLabel() . '"!');
        }

        return back()->with('error', 'Failed to send to "' . $group->displayLabel() . '". Check the bot connection and group access.');
    }
}
