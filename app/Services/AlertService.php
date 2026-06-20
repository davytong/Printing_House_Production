<?php

namespace App\Services;

use App\Models\Material;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Log;

class AlertService
{
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
        $cooldown = (int) config('services.telegram.alert_cooldown', 24);

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
        $chatId   = config('services.telegram.alert_chat_id');
        $threadId = config('services.telegram.alert_thread_id');

        if ($chatId) {
            $this->sendTelegramAlert($material, $stock, $min, $chatId, $threadId);
        }

        // ── Mark alerted ──────────────────────────────────────────────
        $material->updateQuietly(['last_alerted_at' => now()]);
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

        $isOut = $stock <= 0;

        $message = ($isOut ? "🔴 Stock អស់!" : "⚠️ Stock ទាប") . "\n\n"
            . "📦 {$material->name}"
            . ($material->name_km ? " ({$material->name_km})" : '') . "\n"
            . "📂 {$material->categoryLabelShort()}"
            . ($material->sub_type ? " · {$material->sub_type}" : '') . "\n"
            . "━━━━━━━━━━━━━━━━\n"
            . "📊 Stock: {$stock} {$material->unit}\n"
            . ($isOut ? "❌ អស់ Stock!\n" : "🔴 Min: {$min} {$material->unit}\n")
            . "━━━━━━━━━━━━━━━━\n"
            . "🕐 " . now()->format('d/m/Y H:i');

        $params = ['chat_id' => $chatId, 'text' => $message];
        if ($threadId) $params['message_thread_id'] = $threadId;

        try {
            \Illuminate\Support\Facades\Http::timeout(10)->withOptions([
                'curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.167.220']],
            ])->post("https://api.telegram.org/bot{$token}/sendMessage", $params);
        } catch (\Throwable $e) {
            Log::warning("AlertService: Telegram send failed — " . $e->getMessage());
        }
    }
}
