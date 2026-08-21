<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Storage;

class SendTelegramPhotoJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 60;
    public $tries   = 3;
    public $backoff = [15, 45, 90]; // seconds between retries

    public function __construct(
        public string $chatId,
        public string $imagePath,
        public string $caption = '',
        public ?int $threadId = null,
        public bool $deleteAfterSend = false
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        $telegramService->sendPhoto($this->chatId, $this->imagePath, $this->caption, $this->threadId);

        if ($this->deleteAfterSend) {
            Storage::disk('public')->delete($this->imagePath);
        }
    }
}
