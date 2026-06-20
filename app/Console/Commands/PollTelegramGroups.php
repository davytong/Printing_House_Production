<?php

namespace App\Console\Commands;

use App\Models\TelegramGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PollTelegramGroups extends Command
{
    protected $signature = 'telegram:poll
                            {--watch : Run continuously, polling every 3 seconds}
                            {--interval=3 : Seconds between polls (default 3)}';

    protected $description = 'Poll Telegram for updates — registers groups and topics automatically';

    private string $apiBase;

    public function handle(): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token) {
            $this->error('TELEGRAM_BOT_TOKEN not set in .env');
            return;
        }

        $this->apiBase = "https://api.telegram.org/bot{$token}";

        if ($this->option('watch')) {
            $interval = (int) $this->option('interval');
            $this->info("🔄 Telegram polling started (every {$interval}s) — Press Ctrl+C to stop");
            $this->line('   Groups and topics will register automatically when the bot receives messages.');
            $this->newLine();

            while (true) {
                $this->pollOnce();
                sleep($interval);
            }
        } else {
            $this->pollOnce();
        }
    }

    private function pollOnce(): void
    {
        $token  = config('services.telegram.bot_token');
        $offset = cache('telegram_offset', 0);

        try {
            $response = Http::timeout(15)->withOptions([
                'curl' => [CURLOPT_RESOLVE => ['api.telegram.org:443:149.154.167.220']],
            ])->get("{$this->apiBase}/getUpdates", [
                'offset'          => $offset,
                'timeout'         => 10,
                'limit'           => 100,
                'allowed_updates' => json_encode(['message', 'my_chat_member']),
            ]);
        } catch (\Throwable $e) {
            $this->warn('Connection failed: ' . $e->getMessage());
            return;
        }

        if (! $response->successful()) {
            $this->warn('API error: ' . $response->status() . ' — ' . $response->body());
            return;
        }

        $results = $response->json('result') ?? [];

        foreach ($results as $update) {
            $offset  = $update['update_id'] + 1;
            $message = $update['message'] ?? $update['edited_message'] ?? null;

            if (! $message) continue;

            $chat     = $message['chat'] ?? null;
            $chatType = $chat['type'] ?? '';

            if (! in_array($chatType, ['group', 'supergroup'], true)) continue;

            $chatId   = (string) $chat['id'];
            $isForum  = (bool) ($chat['is_forum'] ?? false);
            $threadId = $isForum ? ($message['message_thread_id'] ?? null) : null;

            // Register / update the base group
            $group = TelegramGroup::updateOrCreate(
                ['chat_id' => $chatId, 'message_thread_id' => null],
                [
                    'name'     => $chat['title'] ?? 'Unknown Group',
                    'type'     => $chatType,
                    'is_forum' => $isForum,
                ]
            );

            if ($group->wasRecentlyCreated) {
                $this->info("  ✅ New group: {$group->name} (ID: {$chatId})");
                Log::info("Telegram poll: new group registered", ['chat_id' => $chatId, 'name' => $group->name]);
            }

            // Register topic if forum and message came from a specific thread
            if ($isForum && $threadId) {
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

                    $label = $topicName ?? "Topic #{$threadId}";
                    $this->info("  🗂️  New topic: {$label} (Thread #{$threadId}) in {$group->name}");
                    Log::info("Telegram poll: new topic registered", [
                        'chat_id'   => $chatId,
                        'thread_id' => $threadId,
                        'topic'     => $label,
                    ]);
                } elseif ($topicName && ! $existing->topic_name) {
                    $existing->update(['topic_name' => $topicName]);
                    $this->line("  📝 Updated topic name: {$topicName}");
                }
            }
        }

        cache(['telegram_offset' => $offset], now()->addDays(7));

        if (! $this->option('watch')) {
            $count = count($results);
            $this->info("Poll complete — {$count} update(s) processed. Offset: {$offset}");
        }
    }
}
