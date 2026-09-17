<?php

namespace App\Console\Commands;

use App\Services\DailyReportTrackerService;
use Illuminate\Console\Command;

class InitDailyReportRecords extends Command
{
    protected $signature = 'reports:init-today {--date= : The date to initialize (YYYY-MM-DD), defaults to today}';

    protected $description = 'Initialize pending tracking records for active requirements due today';

    public function handle(DailyReportTrackerService $service): int
    {
        $date = $this->option('date');
        $created = $service->initializeDayRecords($date);

        $this->info("Initialized {$created} new pending report records.");
        return Command::SUCCESS;
    }
}