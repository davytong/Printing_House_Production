<?php

namespace App\Http\Controllers;

use App\Models\TelegramGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    private string $apiBase;
    private string $token;

    public function __construct()
    {
        $this->token   = config('services.telegram.bot_token', '');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Build an HTTP client that bypasses DNS — fixes XAMPP/Apache DNS issues.
     * Uses CURLOPT_RESOLVE to pre-inject the Telegram IP (no DNS lookup needed).
     */
    private function http(int $timeout = 30): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($timeout)->withOptions([
            'curl' => [
                // Pre-resolved IP for api.telegram.org:443 — skips DNS entirely
                CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.167.220'],
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // WEBHOOK  — called by Telegram servers on every update
    // ─────────────────────────────────────────────────────────────────
    public function webhook(Request $request): JsonResponse
    {
        // ── Optional: verify secret token header ──────────────────────
        // Set TELEGRAM_WEBHOOK_SECRET in .env, then pass it when registering:
        // /setWebhook?secret_token=YOUR_SECRET
        $secret = config('services.telegram.webhook_secret');
        if ($secret) {
            $incoming = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($incoming !== $secret) {
                Log::warning('Telegram webhook: invalid secret token');
                return response()->json(['ok' => false], 403);
            }
        }

        $update = $request->all();
        Log::debug('TELEGRAM UPDATE', ['update_id' => $update['update_id'] ?? null]);

        // ── Extract message (could be message, channel_post, etc.) ────
        $message = $update['message']
                ?? $update['edited_message']
                ?? $update['channel_post']
                ?? null;

        if (! $message) {
            return response()->json(['ok' => true]); // my_chat_member, callback_query, etc.
        }

        $chat = $message['chat'] ?? null;
        if (! $chat) {
            return response()->json(['ok' => true]);
        }

        $chatType = $chat['type'] ?? '';

        // Only care about groups / supergroups
        if (! in_array($chatType, ['group', 'supergroup'], true)) {
            return response()->json(['ok' => true]);
        }

        $chatId    = (string) $chat['id'];
        $isForum   = (bool) ($chat['is_forum'] ?? false);
        $threadId  = $isForum ? ($message['message_thread_id'] ?? null) : null;

        // Auto-register/update the group (General slot — thread_id = null)
        TelegramGroup::updateOrCreate(
            ['chat_id' => $chatId, 'message_thread_id' => null],
            [
                'name'     => $chat['title'] ?? 'Unknown Group',
                'type'     => $chatType,
                'is_forum' => $isForum,
            ]
        );

        // If forum and message came from a specific topic — register that topic too
        if ($isForum && $threadId) {
            // Try to get topic name from forum_topic_created if available
            $topicName = $message['forum_topic_created']['name']
                      ?? $message['forum_topic_edited']['name']
                      ?? null;

            $existing = TelegramGroup::where('chat_id', $chatId)
                ->where('message_thread_id', $threadId)
                ->first();

            if (! $existing) {
                TelegramGroup::create([
                    'chat_id'           => $chatId,
                    'name'              => $chat['title'] ?? 'Unknown Group',
                    'type'              => $chatType,
                    'is_forum'          => true,
                    'message_thread_id' => $threadId,
                    'topic_name'        => $topicName ?? "Topic #{$threadId}",
                ]);

                Log::info('Telegram: new topic registered', [
                    'chat_id'   => $chatId,
                    'thread_id' => $threadId,
                    'topic'     => $topicName ?? "Topic #{$threadId}",
                ]);
            } elseif ($topicName && ! $existing->topic_name) {
                // Update topic name if we now know it
                $existing->update(['topic_name' => $topicName]);
            }
        }

        Log::info('Telegram webhook processed', [
            'chat_id'  => $chatId,
            'title'    => $chat['title'] ?? '?',
            'is_forum' => $isForum,
            'thread'   => $threadId,
        ]);

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────────
    // SEND IMAGE  — called from the front-end report page
    // Supports message_thread_id for topic groups
    // ─────────────────────────────────────────────────────────────────
    public function sendImage(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id'           => 'required',
            'photo'             => 'required|file|mimes:png,jpg,jpeg|max:10240',
            'caption'           => 'nullable|string',
            'message_thread_id' => 'nullable|integer',
        ]);

        $caption   = mb_substr($request->input('caption', '📄 របាយការណ៍ការបោះពុម្ព'), 0, 1024);
        $rawChatId = $request->input('chat_id');
        // Safety: parse "chatId|threadId" if JS didn't strip it
        if (str_contains($rawChatId, '|')) {
            [$chatId, $threadIdStr] = explode('|', $rawChatId, 2);
            $threadId = $request->integer('message_thread_id') ?: ($threadIdStr !== '' ? (int)$threadIdStr : null);
        } else {
            $chatId   = $rawChatId;
            $threadId = $request->integer('message_thread_id') ?: null;
        }

        $photoPath = $request->file('photo')->getRealPath();

        $params = ['chat_id' => $chatId, 'caption' => $caption];
        if ($threadId) $params['message_thread_id'] = $threadId;

        try {
            $response = $this->http(30)
                ->attach('photo', file_get_contents($photoPath), 'report.png')
                ->post("{$this->apiBase}/sendPhoto", $params);
        } catch (\Throwable $e) {
            Log::error('Telegram sendPhoto: connection failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Cannot connect to Telegram: ' . $e->getMessage()], 502);
        }

        if ($response->successful() && $response->json('ok') === true) {
            return response()->json(['ok' => true, 'message' => 'Image sent']);
        }

        Log::error('Telegram sendPhoto failed', [
            'chat_id'   => $chatId,
            'thread_id' => $threadId,
            'status'    => $response->status(),
            'body'      => $response->body(),
        ]);

        return response()->json(['ok' => false, 'message' => $response->json('description') ?? 'Failed'], 502);
    }

    // ─────────────────────────────────────────────────────────────────
    // SEND REPORT  — plain-text message, supports topics
    // ─────────────────────────────────────────────────────────────────
    public function sendReport(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id'           => 'required',
            'message'           => 'required|string|max:4096',
            'message_thread_id' => 'nullable|integer',
        ]);

        $rawChatId = $request->input('chat_id');
        if (str_contains($rawChatId, '|')) {
            [$chatId, $threadIdStr] = explode('|', $rawChatId, 2);
            $threadId = $request->integer('message_thread_id') ?: ($threadIdStr !== '' ? (int)$threadIdStr : null);
        } else {
            $chatId   = $rawChatId;
            $threadId = $request->integer('message_thread_id') ?: null;
        }

        $params = [
            'chat_id'    => $chatId,
            'text'       => $request->input('message'),
            'parse_mode' => 'HTML',
        ];
        if ($threadId) $params['message_thread_id'] = $threadId;

        try {
            $response = $this->http(15)->post("{$this->apiBase}/sendMessage", $params);
        } catch (\Throwable $e) {
            Log::error('Telegram sendMessage: connection failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Cannot connect to Telegram: ' . $e->getMessage()], 502);
        }

        if ($response->successful() && $response->json('ok') === true) {
            return response()->json(['ok' => true, 'message' => 'Report sent']);
        }

        Log::error('Telegram sendMessage failed', [
            'chat_id'   => $chatId,
            'thread_id' => $threadId,
            'status'    => $response->status(),
            'body'      => $response->body(),
        ]);

        return response()->json(['ok' => false, 'message' => $response->json('description') ?? 'Failed'], 502);
    }
}
