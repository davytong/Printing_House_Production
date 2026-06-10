<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\TelegramGroup;

class TelegramController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        $token = config('services.telegram.bot_token');
        $this->apiBase = "https://api.telegram.org/bot{$token}";
    }

    // ─────────────────────────────────────────────
    // Webhook: auto-register groups that message the bot
    // ─────────────────────────────────────────────
    public function webhook(Request $request): JsonResponse
    {
        Log::info('TELEGRAM UPDATE', $request->all());

        $chat = $request->input('message.chat');

        if (! $chat || ! in_array($chat['type'] ?? '', ['group', 'supergroup'])) {
            return response()->json(['ok' => true]);
        }

        TelegramGroup::updateOrCreate(
            ['chat_id' => $chat['id']],
            [
                'name' => $chat['title'] ?? 'Unknown Group',
                'type' => $chat['type'],
            ]
        );

        Log::info('GROUP SAVED', ['chat_id' => $chat['id'], 'name' => $chat['title'] ?? '?']);

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────
    // Send an image (PNG captured from front-end)
    // ─────────────────────────────────────────────
    public function sendImage(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'required',
            'photo'   => 'required|file|mimes:png,jpg,jpeg|max:10240',
        ]);

        $caption  = $request->input('caption', '📄 របាយការណ៍ការបោះពុម្ព');
        $chatId   = $request->input('chat_id');
        $photoPath = $request->file('photo')->getRealPath();

        $response = Http::attach('photo', file_get_contents($photoPath), 'report.png')
            ->post("{$this->apiBase}/sendPhoto", [
                'chat_id' => $chatId,
                'caption' => $caption,
            ]);

        if ($response->successful() && ($response->json('ok') === true)) {
            return response()->json(['ok' => true, 'message' => 'Image sent']);
        }

        Log::error('Telegram sendPhoto failed', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        return response()->json(['ok' => false, 'message' => 'Failed to send image'], 502);
    }

    // ─────────────────────────────────────────────
    // Send a plain-text report
    // ─────────────────────────────────────────────
    public function sendReport(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'required',
            'message' => 'required|string|max:4096',
        ]);

        $response = Http::post("{$this->apiBase}/sendMessage", [
            'chat_id'    => $request->input('chat_id'),
            'text'       => $request->input('message'),
            'parse_mode' => 'HTML',
        ]);

        if ($response->successful() && ($response->json('ok') === true)) {
            return response()->json(['ok' => true, 'message' => 'Report sent']);
        }

        Log::error('Telegram sendMessage failed', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        return response()->json(['ok' => false, 'message' => 'Failed to send report'], 502);
    }
}
