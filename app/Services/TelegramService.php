<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramService
{
    private string $apiBase;

    public function __construct()
    {
        $token = config('services.telegram.bot_token', '');
        $this->apiBase = "https://api.telegram.org/bot{$token}";
    }

    /**
     * HTTP client with forced DNS — fixes XAMPP/Apache DNS resolution issues.
     */
    private function http(int $timeout = 30): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($timeout)->withOptions([
            'curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.167.220']],
        ]);
    }

    /**
     * Send multiple photos as a media group (album) with caption on first photo.
     * $paths = array of storage-disk-relative paths.
     */
    public function sendMediaGroup(string $chatId, array $paths, string $caption = '', ?int $threadId = null): bool
    {
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
            Log::error('TelegramService sendPhoto: connection failed', ['error' => $e->getMessage()]);
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
     * Send a text message (optionally into a topic thread).
     */
    public function sendMessage(string $chatId, string $text, ?int $threadId = null): bool
    {
        $params = ['chat_id' => $chatId, 'text' => mb_substr($text, 0, 4096)];
        if ($threadId) $params['message_thread_id'] = $threadId;

        try {
            $response = $this->http(15)->post("{$this->apiBase}/sendMessage", $params);
        } catch (\Throwable $e) {
            Log::error('TelegramService sendMessage: connection failed', ['error' => $e->getMessage()]);
            return false;
        }

        return $response->successful() && $response->json('ok');
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
    public function broadcastMessage(string $text): int
    {
        $groups = \App\Models\TelegramGroup::all();
        $sent   = 0;
        foreach ($groups as $g) {
            if ($this->sendMessage($g->chat_id, $text, $g->message_thread_id)) $sent++;
        }
        return $sent;
    }
}
