<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\DailyReportService;

class SendDailyReportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;
    public $tries   = 3;
    public $backoff = [20, 60, 120]; // seconds between retries

    public function __construct(
        public string $date,
        public ?int $batchId = null,
        public ?int $groupId = null,
        public string $format = 'compact'
    ) {}

    public function handle(DailyReportService $reportService): void
    {
        try {
            // Generate report
            if ($this->format === 'compact') {
                $report = $reportService->generateCompactReport($this->date, $this->batchId);
            } else {
                $report = $reportService->generateDailyReport($this->date, $this->batchId);
            }
            
            // Send to Telegram (background job handles network delays)
            $reportService->sendToTelegram($report, $this->groupId);
            
            \Log::info("Daily report sent successfully for date {$this->date}");
        } catch (\Exception $e) {
            \Log::error("Failed to send daily report for {$this->date}: " . $e->getMessage());
            throw $e; // Rethrow so queue can retry
        }
    }
}
