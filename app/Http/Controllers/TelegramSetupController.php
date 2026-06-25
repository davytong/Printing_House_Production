<?php

namespace App\Http\Controllers;

use App\Models\TelegramGroup;
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
            Log::error("TelegramSetup API GET failed: $endpoint", ['error' => $e->getMessage()]);
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
            Log::error("TelegramSetup API POST failed: $endpoint", ['error' => $e->getMessage()]);
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

        return view('telegram.setup', compact(
            'token', 'botInfo', 'webhookInfo', 'botError',
            'groups', 'groupedChats', 'appUrl', 'alertTemplate'
        ));
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
    public function testGroup(TelegramGroup $group): RedirectResponse
    {
        $params = [
            'chat_id'    => $group->chat_id,
            'text'       => "✅ PrintTracker connected!\n"
                          . ($group->topic_name ? "Topic: {$group->topic_name}\n" : "")
                          . "Time: " . now()->format('d/m/Y H:i:s'),
        ];

        if ($group->message_thread_id) {
            $params['message_thread_id'] = $group->message_thread_id;
        }

        $response = $this->apiPost('/sendMessage', $params);

        if ($response && $response->successful() && $response->json('ok')) {
            return back()->with('success', 'Test message sent to "' . $group->displayLabel() . '"!');
        }

        return back()->with('error',
            'Failed to send to "' . $group->displayLabel() . '": ' .
            ($response?->json('description') ?? 'Connection error'));
    }
}
