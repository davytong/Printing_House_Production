<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Storage;

class SendTelegramMediaGroupJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 60;
    public $tries   = 3;
    public $backoff = [15, 45, 90]; // seconds between retries

    public function __construct(
        public string $chatId,
        public array $imagePaths,
        public string $caption = '',
        public ?int $threadId = null,
        public bool $deleteAfterSend = false
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        try {
            $telegramService->sendMediaGroup($this->chatId, $this->imagePaths, $this->caption, $this->threadId);
        } finally {
            if ($this->deleteAfterSend) {
                foreach ($this->imagePaths as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
        }
    }
}
