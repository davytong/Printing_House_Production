<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\TelegramService;

class SendTelegramMessageJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 30;
    public $tries   = 3;
    public $backoff = [10, 30, 60]; // seconds between retries

    public function __construct(
        public string $chatId,
        public string $text,
        public ?int $threadId = null
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        $telegramService->sendMessage($this->chatId, $this->text, $this->threadId);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('SendTelegramMessageJob failed permanently', [
            'chat_id' => $this->chatId,
            'error'   => $exception->getMessage(),
        ]);
    }
}
