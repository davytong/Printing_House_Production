<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramService
{
    /**
     * Production / Live Work Group Chat IDs.
     * Under NO circumstances should testing messages be sent to these groups.
     */
    public const WORK_GROUPS = [
        '-4646583053',      // BELTEI Printing Press (Live Staff Group)
        '-1003150870760',   // Press Processing Works (Production Supergroup / Forum Topics)
    ];

    /**
     * Dedicated Testing Group Chat IDs.
     */
    public const TESTING_GROUPS = [
        '-1003744799209',   // Testing Supergroup
        '-5150858234',      // Testing Group
    ];

    private string $apiBase;

    /**
     * Never write a bot token to application logs. HTTP client exceptions include
     * the full request URL, which contains the token for Telegram Bot API calls.
     */
    public static function redactApiError(string $message): string
    {
        return preg_replace('#bot[^/\s]+/#', 'bot[redacted]/', $message) ?? 'Telegram request failed';
    }

    public function __construct()
    {
        $token = config('services.telegram.bot_token', '');
        $this->apiBase = "https://api.telegram.org/bot{$token}";
    }

    /**
     * HTTP client with forced DNS — fixes XAMPP/Apache DNS resolution issues.
     */
    private function http(int $timeout = 8): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($timeout)
            ->connectTimeout(4);
    }

    /**
     * Send multiple photos as a media group (album) with caption on first photo.
     * $paths = array of storage-disk-relative paths.
     */
    public function sendMediaGroup(string $chatId, array $paths, string $caption = '', ?int $threadId = null): bool
    {
        if ($this->isTestMessageBlocked($chatId, $caption)) {
            return false;
        }

        if (empty($paths)) return false;

        $caption = mb_substr($caption, 0, 1024);

        $http  = $this->http(60);
        $media = [];

        foreach ($paths as $i => $path) {
            $fullPath = Storage::disk('public')->path($path);
            if (! file_exists($fullPath)) continue;

            $key  = "photo_{$i}";
            $http = $http->attach($key, file_get_contents($fullPath), "photo_{$i}.jpg");

            $mediaItem = ['type' => 'photo', 'media' => "attach://{$key}"];
            if ($i === 0 && $caption) $mediaItem['caption'] = $caption;
            $media[] = $mediaItem;
        }

        if (empty($media)) return false;

        $params = ['chat_id' => $chatId, 'media' => json_encode($media)];
        if ($threadId) $params['message_thread_id'] = $threadId;

        $response = $http->post("{$this->apiBase}/sendMediaGroup", $params);

        if ($response->successful() && $response->json('ok')) return true;

        Log::error('TelegramService sendMediaGroup failed', [
            'chat_id'   => $chatId,
            'thread_id' => $threadId,
            'status'    => $response->status(),
            'body'      => $response->body(),
        ]);

        return false;
    }

    /**
     * Send a photo with caption to a chat (optionally into a topic thread).
     */
    public function sendPhoto(string $chatId, string $imagePath, string $caption = '', ?int $threadId = null): bool
    {
        if ($this->isTestMessageBlocked($chatId, $caption)) {
            return false;
        }

        $caption  = mb_substr($caption, 0, 1024);
        $fullPath = Storage::disk('public')->path($imagePath);

        if (! file_exists($fullPath)) {
            Log::error("TelegramService: Image not found at {$fullPath}");
            return false;
        }

        $params = ['chat_id' => $chatId, 'caption' => $caption];
        if ($threadId) $params['message_thread_id'] = $threadId;

        try {
            $response = $this->http(30)
                ->attach('photo', file_get_contents($fullPath), 'report.jpg')
                ->post("{$this->apiBase}/sendPhoto", $params);
        } catch (\Throwable $e) {
            Log::error('TelegramService sendPhoto: connection failed', ['error' => self::redactApiError($e->getMessage())]);
            return false;
        }

        if ($response->successful() && $response->json('ok')) return true;

        Log::error('TelegramService sendPhoto failed', [
            'chat_id'   => $chatId,
            'thread_id' => $threadId,
            'status'    => $response->status(),
            'body'      => $response->body(),
        ]);

        return false;
    }

    /**
     * Safety guard: Strictly prevent test messages from being sent to production work groups.
     */
    public function isTestMessageBlocked(string $chatId, string $text = ''): bool
    {
        $chatId = (string) $chatId;
        $isWorkGroup = in_array($chatId, self::WORK_GROUPS, true);
        $isTestMessage = $text !== '' && preg_match('/\[(test|testing|debug|trial)\]|test\s+mode|🧪|សាកល្បង/iu', $text);

        // Test and diagnostic traffic belongs only in the two designated test
        // chats, including when someone has registered another non-production
        // group in the setup page.
        if ($isTestMessage && ! in_array($chatId, self::TESTING_GROUPS, true)) {
            Log::warning("TelegramService: Prevented test message outside a designated testing group ({$chatId}).");
            return true;
        }

        // Block if running under automated test environment
        if ($isWorkGroup && app()->environment('testing')) {
            Log::warning("TelegramService: Prevented message dispatch to work group {$chatId} during automated testing.");
            return true;
        }

        return false;
    }

    /**
     * Send a text message (optionally into a topic thread).
     */
    public function sendMessage(string $chatId, string $text, ?int $threadId = null, ?string $parseMode = null, ?array $replyMarkup = null): bool
    {
        if ($this->isTestMessageBlocked($chatId, $text)) {
            return false;
        }

        $params = ['chat_id' => $chatId, 'text' => mb_substr($text, 0, 4096)];
        if ($parseMode) $params['parse_mode'] = $parseMode;
        if ($threadId) $params['message_thread_id'] = $threadId;
        if ($replyMarkup) $params['reply_markup'] = json_encode($replyMarkup);

        try {
            $response = $this->http(15)->post("{$this->apiBase}/sendMessage", $params);
        } catch (\Throwable $e) {
            Log::error('TelegramService sendMessage: connection failed', ['error' => self::redactApiError($e->getMessage())]);
            return false;
        }

        if (!$response->successful() || !$response->json('ok')) {
            Log::error('TelegramService sendMessage failed', [
                'chat_id' => $chatId,
                'status'  => $response->status(),
                'body'    => $response->body()
            ]);
            return false;
        }

        return true;
    }

    /**
     * Send photo to ALL registered groups (respecting each group's thread_id).
     */
    public function broadcastPhoto(string $imagePath, string $caption = ''): int
    {
        $groups = \App\Models\TelegramGroup::all();
        $sent   = 0;
        foreach ($groups as $g) {
            if ($this->sendPhoto($g->chat_id, $imagePath, $caption, $g->message_thread_id)) $sent++;
        }
        return $sent;
    }

    /**
     * Send message to ALL registered groups (respecting each group's thread_id).
     */
    /**
     * Fetch administrators/members of a chat from Telegram API.
     */
    public function getChatAdministrators(string $chatId): array
    {
        try {
            $response = $this->http(15)->get("{$this->apiBase}/getChatAdministrators", [
                'chat_id' => $chatId,
            ]);

            if ($response->successful() && $response->json('ok')) {
                return $response->json('result') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("TelegramService getChatAdministrators failed for {$chatId}: " . self::redactApiError($e->getMessage()));
        }

        return [];
    }

    public function broadcastMessage(string $text, ?string $parseMode = null): int
    {
        $groups = \App\Models\TelegramGroup::all();
        $sent   = 0;
        foreach ($groups as $g) {
            if ($this->sendMessage($g->chat_id, $text, $g->message_thread_id, $parseMode)) $sent++;
        }
        return $sent;
    }
}
