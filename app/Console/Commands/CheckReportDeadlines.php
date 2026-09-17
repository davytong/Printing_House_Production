<?php

namespace App\Console\Commands;

use App\Services\DailyReportTrackerService;
use Illuminate\Console\Command;

class CheckReportDeadlines extends Command
{
    protected $signature = 'reports:check-deadlines';

    protected $description = 'Check daily production report deadlines and send automatic alerts for pending reports';

    public function handle(DailyReportTrackerService $service): int
    {
        $this->info('Checking daily report deadlines...');
        $alerts = $service->checkDeadlines();

        if (empty($alerts)) {
            $this->line('All reports checked. No new alerts required at this time.');
        } else {
            foreach ($alerts as $alert) {
                $this->warn("Alert sent for '{$alert['title']}' to {$alert['destination']} ({$alert['count']} staff pending).");
            }
        }

        return Command::SUCCESS;
    }
}