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
                            {--audience=group : Audience type (group|individual|both)}
                            {--monospace=true : Send with Monospace tap-to-copy (true|false)}
                            {--group= : Telegram group ID (default: all active groups)}
                            {--dry-run : Only display report without sending to Telegram}';

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
        $audience = $this->option('audience') ?: 'group';
        $isMonospace = filter_var($this->option('monospace') ?? true, FILTER_VALIDATE_BOOLEAN);
        $groupId = $this->option('group');
        $dryRun = (bool) $this->option('dry-run');
        
        if ($grade) {
            $this->info("🎯 Filtering by grade: {$grade}");
        }
        
        try {
            if ($audience === 'both') {
                $report1 = ($format === 'compact')
                    ? $reportService->generateCompactReport($date, $batchId, $grade, 'group')
                    : $reportService->generateDailyReport($date, $batchId, $grade, 'group');
                $report2 = ($format === 'compact')
                    ? $reportService->generateCompactReport($date, $batchId, $grade, 'individual')
                    : $reportService->generateDailyReport($date, $batchId, $grade, 'individual');

                $this->line('');
                $this->line('=== 📱 MESSAGE 1 (IN GROUP) ===');
                $this->line($report1);
                $this->line('');
                $this->line('=== 📱 MESSAGE 2 (INDIVIDUAL TO HE) ===');
                $this->line($report2);
                $this->line('');

                if ($dryRun) {
                    $this->info('🔍 [Dry-Run] 2 messages generated successfully without sending.');
                    return Command::SUCCESS;
                }

                $this->info('📱 Sending 2 separate messages to Telegram at the same time...');
                $success = $reportService->sendToTelegram([$report1, $report2], $groupId, $isMonospace);
            } else {
                // Generate report
                if ($format === 'compact') {
                    $report = $reportService->generateCompactReport($date, $batchId, $grade, $audience);
                } else {
                    $report = $reportService->generateDailyReport($date, $batchId, $grade, $audience);
                }
                
                $this->line('');
                $this->line($report);
                $this->line('');
                
                if ($dryRun) {
                    $this->info('🔍 [Dry-Run] Report generated successfully without sending.');
                    return Command::SUCCESS;
                }

                // Send to Telegram
                $this->info('📱 Sending to Telegram...');
                $success = $reportService->sendToTelegram($report, $groupId, $isMonospace);
            }
            
            if ($success) {
                $this->info('✅ Report(s) sent successfully!');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Failed to send report(s) to Telegram');
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
