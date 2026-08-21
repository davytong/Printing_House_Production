<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductionTask;
use App\Models\TelegramGroup;
use App\Services\TelegramService;
use Carbon\Carbon;

class SendDailyBriefing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-briefing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a morning production briefing to the production Telegram group';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram)
    {
        // Find the group with 'production_briefing' purpose
        $group = TelegramGroup::where('purpose', 'production_briefing')->first();
        
        if (!$group) {
            $this->warn('No Telegram group configured with purpose "production_briefing".');
            return;
        }

        $today = Carbon::today();

        // Tasks scheduled for today
        $todayTasks = ProductionTask::where('scheduled_start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->where('scheduled_end_date', '>=', $today)
                  ->orWhereNull('scheduled_end_date');
            })
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        // Overdue tasks
        $overdueTasks = ProductionTask::where('due_date', '<', $today)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        // Urgent tasks
        $urgentTasks = ProductionTask::where('priority', 'urgent')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        $message = "🌅 *Morning Production Briefing* - " . $today->format('d/m/Y') . "\n\n";

        if ($todayTasks->isEmpty() && $overdueTasks->isEmpty() && $urgentTasks->isEmpty()) {
            $message .= "No tasks scheduled or overdue for today. Enjoy the day! 🎉";
        } else {
            if ($todayTasks->isNotEmpty()) {
                $message .= "📋 *Tasks Scheduled for Today:* " . $todayTasks->count() . "\n";
                foreach ($todayTasks->take(5) as $task) {
                    $machineName = $task->machine ? $task->machine->name : 'No Machine';
                    $message .= "• {$task->name} ({$machineName})\n";
                }
                if ($todayTasks->count() > 5) {
                    $message .= "• ... and " . ($todayTasks->count() - 5) . " more.\n";
                }
                $message .= "\n";
            }

            if ($urgentTasks->isNotEmpty()) {
                $message .= "🔥 *URGENT Tasks:* " . $urgentTasks->count() . "\n";
                foreach ($urgentTasks->take(5) as $task) {
                    $message .= "• {$task->name}\n";
                }
                if ($urgentTasks->count() > 5) {
                    $message .= "• ... and " . ($urgentTasks->count() - 5) . " more.\n";
                }
                $message .= "\n";
            }

            if ($overdueTasks->isNotEmpty()) {
                $message .= "⚠️ *Overdue Tasks:* " . $overdueTasks->count() . "\n";
                foreach ($overdueTasks->take(5) as $task) {
                    $overdueDays = (int) $today->diffInDays($task->due_date);
                    $message .= "• {$task->name} ({$overdueDays} days overdue)\n";
                }
                if ($overdueTasks->count() > 5) {
                    $message .= "• ... and " . ($overdueTasks->count() - 5) . " more.\n";
                }
                $message .= "\n";
            }
            
            $message .= "Let's get to work! 💪";
        }

        // Send message
        $telegram->sendMessage($group->chat_id, $message, $group->message_thread_id, 'Markdown');

        $this->info('Daily briefing sent to Telegram!');
    }
}
