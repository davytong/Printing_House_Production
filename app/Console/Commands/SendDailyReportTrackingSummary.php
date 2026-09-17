<?php

namespace App\Console\Commands;

use App\Services\DailyReportTrackerService;
use Illuminate\Console\Command;

class SendDailyReportTrackingSummary extends Command
{
    protected $signature = 'reports:daily-summary
                            {--date= : The date to summarize (YYYY-MM-DD), defaults to today}
                            {--chat= : Custom Telegram Chat ID}
                            {--thread= : Custom Telegram Thread ID}';

    protected $description = 'Send daily production report tracking summary to Telegram';

    public function handle(DailyReportTrackerService $service): int
    {
        $date     = $this->option('date');
        $chatId   = $this->option('chat');
        $threadId = $this->option('thread') ? (int) $this->option('thread') : null;

        $this->info("Generating daily tracking summary" . ($date ? " for {$date}" : "") . "...");

        $success = $service->sendDailySummary($date, $chatId, $threadId);

        if ($success) {
            $this->info('Daily tracking summary dispatched successfully.');
            return Command::SUCCESS;
        }

        $this->error('Failed to send daily summary or no requirements due.');
        return Command::FAILURE;
    }
}