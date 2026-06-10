<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\TelegramGroup;

class PollTelegramGroups extends Command
{
    protected $signature   = 'telegram:poll';
    protected $description = 'Poll Telegram for updates and persist group records';

    public function handle(): void
    {
        $token  = config('services.telegram.bot_token');
        $offset = cache('telegram_offset', 0);

        $response = Http::get("https://api.telegram.org/bot{$token}/getUpdates", [
            'offset'  => $offset,
            'timeout' => 10,
        ]);

        if (! $response->successful()) {
            $this->error('Failed to reach Telegram API: ' . $response->status());
            return;
        }

        $data = $response->json();

        foreach ($data['result'] ?? [] as $update) {
            $offset = $update['update_id'] + 1;

            $chat = $update['message']['chat'] ?? null;

            if (! $chat) {
                continue;
            }

            if (in_array($chat['type'], ['group', 'supergroup'])) {
                TelegramGroup::updateOrCreate(
                    ['chat_id' => $chat['id']],
                    [
                        'name' => $chat['title'] ?? 'Unknown Group',
                        'type' => $chat['type'],
                    ]
                );
                $this->info("Saved group: " . ($chat['title'] ?? 'Unknown') . " ({$chat['id']})");
            }
        }

        cache(['telegram_offset' => $offset], 3600);
        $this->info('Poll complete. Next offset: ' . $offset);
    }
}
