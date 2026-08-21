<?php

namespace App\Observers;

use App\Models\ProductionTask;
use App\Models\ProductionSchedule;
use Carbon\Carbon;

class ProductionTaskObserver
{
    /**
     * Handle the ProductionTask "created" event.
     */
    public function created(ProductionTask $task): void
    {
        $this->syncToSchedule($task);
    }

    /**
     * Handle the ProductionTask "updated" event.
     */
    public function updated(ProductionTask $task): void
    {
        // If dates or process changed, we need to completely resync
        if ($task->isDirty('scheduled_start_date') || $task->isDirty('duration_days') || $task->isDirty('process') || $task->isDirty('name')) {
            $this->removeFromSchedule($task); // Remove old dates
            $this->syncToSchedule($task); // Add new dates
        }
        
        // If status changed to completed, cascade to the next process
        if ($task->isDirty('status') && $task->status === 'completed') {
            $this->cascadeToNextProcess($task);
        }
    }

    /**
     * Handle the ProductionTask "deleted" event.
     */
    public function deleted(ProductionTask $task): void
    {
        $this->removeFromSchedule($task);
    }

    /**
     * Sync task to ProductionSchedule grid
     */
    private function syncToSchedule(ProductionTask $task): void
    {
        if (!$task->scheduled_start_date || !$task->process) {
            return;
        }

        $date = $task->scheduled_start_date->copy();
        $days = $task->duration_days > 0 ? $task->duration_days : 1;

        for ($i = 0; $i < $days; $i++) {
            // Skip weekends to match task calculation logic
            if ($date->isWeekend()) {
                $date->addDay();
                continue;
            }

            $year = $date->year;
            $month = $date->month;
            $day = $date->day;

            $existing = ProductionSchedule::where([
                'year' => $year,
                'month' => $month,
                'process' => $task->process,
                'day' => $day,
            ])->first();

            if ($existing) {
                // Append if not exists
                $tasksArray = array_map('trim', explode(',', $existing->task));
                if (!in_array($task->name, $tasksArray)) {
                    $tasksArray[] = $task->name;
                    $existing->update(['task' => implode(', ', array_filter($tasksArray))]);
                }
            } else {
                ProductionSchedule::create([
                    'year' => $year,
                    'month' => $month,
                    'process' => $task->process,
                    'day' => $day,
                    'task' => $task->name,
                    'status' => 'planned',
                ]);
            }

            $date->addDay();
        }
    }

    /**
     * Remove task from ProductionSchedule grid
     */
    private function removeFromSchedule(ProductionTask $task): void
    {
        // Because the original dates might have changed, we can't reliably know which days to remove.
        // But we can use the original attributes if this is an update.
        $originalStart = $task->getOriginal('scheduled_start_date') ? Carbon::parse($task->getOriginal('scheduled_start_date')) : $task->scheduled_start_date;
        $originalProcess = $task->getOriginal('process') ?? $task->process;
        $originalDays = $task->getOriginal('duration_days') ?? $task->duration_days;
        $originalDays = $originalDays > 0 ? $originalDays : 1;
        $originalName = $task->getOriginal('name') ?? $task->name;

        if (!$originalStart || !$originalProcess) return;

        $date = $originalStart->copy();

        for ($i = 0; $i < $originalDays; $i++) {
            if ($date->isWeekend()) {
                $date->addDay();
                continue;
            }

            $existing = ProductionSchedule::where([
                'year' => $date->year,
                'month' => $date->month,
                'process' => $originalProcess,
                'day' => $date->day,
            ])->first();

            if ($existing) {
                $tasksArray = array_map('trim', explode(',', $existing->task));
                $tasksArray = array_filter($tasksArray, function($t) use ($originalName) {
                    return $t !== $originalName;
                });
                
                if (empty($tasksArray)) {
                    $existing->delete(); // Delete cell if empty
                } else {
                    $existing->update(['task' => implode(', ', $tasksArray)]);
                }
            }

            $date->addDay();
        }
    }

    /**
     * Automated Workflow Cascading
     * When a task is completed, stage the next logical process in pending status.
     */
    private function cascadeToNextProcess(ProductionTask $task): void
    {
        $processes = [
            'Design', 'Press', 'Digital', 'Folding', 'Gathering', 
            'Staple', 'Binding', 'Cutting', 'Packaging', 'Delivery'
        ];

        $currentIndex = array_search($task->process, $processes);

        // If process is found and it's not the last one (Delivery)
        if ($currentIndex !== false && $currentIndex < count($processes) - 1) {
            $nextProcess = $processes[$currentIndex + 1];

            // Check if the next process already exists for this exact task name
            // (in case it was manually created earlier or we've already cascaded)
            $exists = ProductionTask::where('name', $task->name)
                ->where('process', $nextProcess)
                ->exists();

            if (!$exists) {
                ProductionTask::create([
                    'name' => $task->name,
                    'description' => 'Auto-staged after ' . $task->process . ' completion.',
                    'process' => $nextProcess,
                    'status' => 'pending',
                    'duration_days' => 1,
                    // Note: No dates or machine assigned yet, it goes to the backlog
                ]);
            }
        }
    }
}
