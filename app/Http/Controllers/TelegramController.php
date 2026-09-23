<?php

namespace App\Http\Controllers;

use App\Models\TelegramGroup;
use App\Services\TelegramService;
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
        $telegramIp = env('TELEGRAM_API_IP', '149.154.167.220');
        
        return Http::timeout($timeout)->withOptions([
            'curl' => [
                // Pre-resolved IP for api.telegram.org:443 — skips DNS entirely
                CURLOPT_RESOLVE => ["api.telegram.org:443:{$telegramIp}"],
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

        // ── Process Bot Commands & Callback Queries via TelegramBotService ──
        try {
            app(\App\Services\TelegramBotService::class)->handleUpdate($update);
        } catch (\Throwable $e) {
            Log::error('TelegramBotService handleUpdate failed: ' . $e->getMessage());
        }

        // ── Extract message (could be message, channel_post, etc.) ────
        $message = $update['message']
                ?? $update['edited_message']
                ?? $update['channel_post']
                ?? null;

        if (! $message) {
            return response()->json(['ok' => true]);
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
            'send_full_text'    => 'nullable|boolean',
            'full_text'         => 'nullable|string',
        ]);

        $rawChatId = $request->input('chat_id');
        // Safety: parse "chatId|threadId" if JS didn't strip it
        if (str_contains($rawChatId, '|')) {
            [$chatId, $threadIdStr] = explode('|', $rawChatId, 2);
            $threadId = $request->integer('message_thread_id') ?: ($threadIdStr !== '' ? (int)$threadIdStr : null);
        } else {
            $chatId   = $rawChatId;
            $threadId = $request->integer('message_thread_id') ?: null;
        }

        $caption   = $request->input('caption', '🖨️ របាយការណ៍ការបោះពុម្ព');
        // A report image can include one or more follow-up text messages; scan
        // all user-provided text before sending the image so a test report can
        // never partially reach a staff group.
        $testContent = $caption . "\n" . (string) $request->input('full_text', '') . "\n"
            . json_encode($request->input('full_texts', ''), JSON_UNESCAPED_UNICODE);
        if (app(TelegramService::class)->isTestMessageBlocked((string) $chatId, $testContent)) {
            return response()->json(['ok' => false, 'message' => 'Test messages may only be sent to a designated testing group.'], 422);
        }
        // Prevent Telegram 1024-character caption cutoff on sendPhoto
        $photoCaption = mb_substr($caption, 0, 1000);
        $photoPath = $request->file('photo')->getRealPath();

        $params = ['chat_id' => $chatId, 'caption' => $photoCaption];
        if ($threadId) $params['message_thread_id'] = $threadId;

        try {
            $response = $this->http(30)
                ->attach('photo', file_get_contents($photoPath), 'report.png')
                ->post("{$this->apiBase}/sendPhoto", $params);

            if ($response->successful() && $response->json('ok') === true) {
                // Send full text report(s) as separate message(s) if enabled (one-touch tap-to-copy or normal text)
                if ($request->boolean('send_full_text')) {
                    $isMonospace = $request->boolean('is_monospace', true);
                    $fullTexts = [];
                    if ($request->filled('full_texts')) {
                        $raw = $request->input('full_texts');
                        $fullTexts = is_array($raw) ? $raw : (json_decode($raw, true) ?: [$raw]);
                    } elseif ($request->filled('full_text')) {
                        $fullTexts = [$request->input('full_text')];
                    }

                    foreach ($fullTexts as $txt) {
                        if (!is_string($txt) || empty(trim($txt))) continue;

                        $chunks = mb_strlen($txt) > 4000 ? str_split($txt, 3900) : [$txt];
                        foreach ($chunks as $chunk) {
                            $textParams = ['chat_id' => $chatId];
                            if ($threadId) $textParams['message_thread_id'] = $threadId;

                            if ($isMonospace) {
                                $escaped = htmlspecialchars($chunk, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                $textParams['text'] = "<pre>{$escaped}</pre>";
                                $textParams['parse_mode'] = 'HTML';
                            } else {
                                $textParams['text'] = $chunk;
                            }

                            $this->http(15)->post("{$this->apiBase}/sendMessage", $textParams);
                        }
                    }
                }

                \App\Models\ActivityLog::record(
                    'Broadcast Telegram Image',
                    "Sent image snapshot to chat {$chatId}" . ($threadId ? " (topic {$threadId})" : ""),
                    'telegram'
                );

                return response()->json(['ok' => true, 'message' => 'Image sent']);
            }
        } catch (\Throwable $e) {
            Log::error('Telegram sendPhoto: connection failed', ['error' => TelegramService::redactApiError($e->getMessage())]);
            return response()->json(['ok' => false, 'message' => 'Cannot connect to Telegram.'], 502);
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
    // SEND REPORT  — plain-text message(s) with tap-to-copy HTML or plain text
    // ─────────────────────────────────────────────────────────────────
    public function sendReport(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id'           => 'required',
            'message'           => 'nullable|string',
            'messages'          => 'nullable',
            'is_monospace'      => 'nullable|boolean',
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

        $isMonospace = $request->boolean('is_monospace', true);

        $rawMessages = [];
        if ($request->filled('messages')) {
            $raw = $request->input('messages');
            $rawMessages = is_array($raw) ? $raw : (json_decode($raw, true) ?: [$raw]);
        } elseif ($request->filled('message')) {
            $rawMessages = [$request->input('message')];
        }

        if (empty($rawMessages)) {
            return response()->json(['ok' => false, 'message' => 'No message provided'], 422);
        }

        if (app(TelegramService::class)->isTestMessageBlocked((string) $chatId, implode("\n", $rawMessages))) {
            return response()->json(['ok' => false, 'message' => 'Test messages may only be sent to a designated testing group.'], 422);
        }

        $allOk = true;
        foreach ($rawMessages as $rawText) {
            if (!is_string($rawText) || empty(trim($rawText))) continue;

            $chunks = mb_strlen($rawText) > 4000 ? str_split($rawText, 3900) : [$rawText];
            foreach ($chunks as $chunk) {
                $params = ['chat_id' => $chatId];
                if ($threadId) $params['message_thread_id'] = $threadId;

                if ($isMonospace) {
                    $escaped = htmlspecialchars($chunk, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $params['text'] = "<pre>{$escaped}</pre>";
                    $params['parse_mode'] = 'HTML';
                } else {
                    $params['text'] = $chunk;
                }

                try {
                    $response = $this->http(15)->post("{$this->apiBase}/sendMessage", $params);
                    if (!$response->successful() || $response->json('ok') !== true) {
                        $allOk = false;
                    }
                } catch (\Throwable $e) {
                    Log::error('Telegram sendMessage: connection failed', ['error' => TelegramService::redactApiError($e->getMessage())]);
                    return response()->json(['ok' => false, 'message' => 'Cannot connect to Telegram.'], 502);
                }
            }
        }

        if ($allOk) {
            \App\Models\ActivityLog::record(
                'Broadcast Telegram Report',
                "Sent " . count($rawMessages) . " message(s) to chat {$chatId}" . ($threadId ? " (topic {$threadId})" : ""),
                'telegram'
            );

            return response()->json(['ok' => true, 'message' => count($rawMessages) > 1 ? 'Both messages sent' : 'Report sent']);
        }

        return response()->json(['ok' => false, 'message' => 'Failed to send one or more messages'], 502);
    }
}
