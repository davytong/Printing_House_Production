<?php

namespace App\Console\Commands;

use App\Services\DailyReportService;
use Illuminate\Console\Command;

class SendDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'report:send-daily
                            {--date= : Report date (Y-m-d format, default: today)}
                            {--batch= : Batch ID (default: current batch)}
                            {--grade= : Filter by specific grade/level (optional)}
                            {--format=compact : Report format (full|compact)}
                            {--group= : Telegram group ID (default: all active groups)}';

    /**
     * The console command description.
     */
    protected $description = 'Send daily production report to Telegram';

    /**
     * Execute the console command.
     */
    public function handle(DailyReportService $reportService): int
    {
        $this->info('📊 Generating daily report...');
        
        $date = $this->option('date') ?: today()->toDateString();
        $batchId = $this->option('batch');
        $grade = $this->option('grade');
        $format = $this->option('format');
        $groupId = $this->option('group');
        
        if ($grade) {
            $this->info("🎯 Filtering by grade: {$grade}");
        }
        
        try {
            // Generate report
            if ($format === 'compact') {
                $report = $reportService->generateCompactReport($date, $batchId, $grade);
            } else {
                $report = $reportService->generateDailyReport($date, $batchId, $grade);
            }
            
            $this->line('');
            $this->line($report);
            $this->line('');
            
            // Send to Telegram
            $this->info('📱 Sending to Telegram...');
            $success = $reportService->sendToTelegram($report, $groupId);
            
            if ($success) {
                $this->info('✅ Report sent successfully!');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Failed to send report to Telegram');
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
