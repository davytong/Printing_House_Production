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
            $me = Http::timeout(6)->get($this->apiBase() . '/getMe');
            if ($me->successful() && $me->json('ok')) {
                $botInfo = $me->json('result');
            } else {
                $botError = $me->json('description') ?? 'Could not reach Telegram API';
            }

            $wh = Http::timeout(6)->get($this->apiBase() . '/getWebhookInfo');
            if ($wh->successful() && $wh->json('ok')) {
                $webhookInfo = $wh->json('result');
            }
        }

        $groups  = TelegramGroup::orderBy('name')->get();
        $appUrl  = config('app.url');

        return view('telegram.setup', compact(
            'token', 'botInfo', 'webhookInfo', 'botError', 'groups', 'appUrl'
        ));
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

        $response = Http::timeout(10)->get($this->apiBase() . '/setWebhook', [
            'url'             => $url,
            'drop_pending_updates' => true,
        ]);

        if ($response->successful() && $response->json('ok')) {
            return back()->with('success', 'Webhook set: ' . $url);
        }

        return back()->with('error', 'Failed: ' . ($response->json('description') ?? $response->body()));
    }

    // ─────────────────────────────────────────────
    // Delete webhook (switch to polling mode)
    // ─────────────────────────────────────────────
    public function deleteWebhook(): RedirectResponse
    {
        $response = Http::timeout(10)->get($this->apiBase() . '/deleteWebhook', [
            'drop_pending_updates' => true,
        ]);

        if ($response->successful() && $response->json('ok')) {
            return back()->with('success', 'Webhook removed. You can now use php artisan telegram:poll');
        }

        return back()->with('error', 'Failed: ' . ($response->json('description') ?? $response->body()));
    }

    // ─────────────────────────────────────────────
    // Poll once (runs telegram:poll and returns result)
    // ─────────────────────────────────────────────
    public function pollNow(): RedirectResponse
    {
        // Must have no webhook set to use getUpdates
        $wh = Http::timeout(6)->get($this->apiBase() . '/getWebhookInfo');
        $webhookUrl = $wh->json('result.url') ?? '';

        if ($webhookUrl) {
            return back()->with('error',
                'Cannot poll while a webhook is active. Remove the webhook first, then poll.');
        }

        $offset   = cache('telegram_offset', 0);
        $response = Http::timeout(15)->get($this->apiBase() . '/getUpdates', [
            'offset'  => $offset,
            'timeout' => 5,
            'limit'   => 100,
        ]);

        if (! $response->successful()) {
            return back()->with('error', 'Telegram API error: ' . $response->status());
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
                    ['chat_id' => $chat['id']],
                    [
                        'name' => $chat['title'] ?? 'Unknown Group',
                        'type' => $chat['type'],
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
            'chat_id'    => 'required',
            'group_name' => 'required|string|max:255',
        ]);

        // Verify the chat_id is reachable
        $chatId = $request->chat_id;
        $check  = Http::timeout(8)->get($this->apiBase() . '/getChat', [
            'chat_id' => $chatId,
        ]);

        if (! $check->successful() || ! $check->json('ok')) {
            return back()->with('error',
                'Could not verify chat ID. Make sure the bot is a member of the group. ' .
                'Error: ' . ($check->json('description') ?? 'unknown'));
        }

        $chat = $check->json('result');

        TelegramGroup::updateOrCreate(
            ['chat_id' => $chatId],
            [
                'name' => $chat['title'] ?? $request->group_name,
                'type' => $chat['type'] ?? 'group',
            ]
        );

        return back()->with('success', 'Group "' . ($chat['title'] ?? $request->group_name) . '" added successfully.');
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
    // Send a test message to verify a group works
    // ─────────────────────────────────────────────
    public function testGroup(TelegramGroup $group): RedirectResponse
    {
        $response = Http::timeout(10)->post($this->apiBase() . '/sendMessage', [
            'chat_id'    => $group->chat_id,
            'text'       => "✅ PrintTracker connected!\nBot: @" . (config('telegram.bot_username', 'Bot')) . "\nTime: " . now()->format('d/m/Y H:i:s'),
            'parse_mode' => 'HTML',
        ]);

        if ($response->successful() && $response->json('ok')) {
            return back()->with('success', 'Test message sent to "' . $group->name . '"!');
        }

        return back()->with('error',
            'Failed to send to "' . $group->name . '": ' .
            ($response->json('description') ?? $response->body()));
    }
}
