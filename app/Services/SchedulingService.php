<?php

namespace App\Services;

use App\Models\MachineDowntime;
use App\Models\ProductionTask;
use App\Models\ScheduleShiftLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SchedulingService
{
    /**
     * Schedule a new task. If urgent, preempt existing tasks.
     */
    public function scheduleTask(ProductionTask $task): ProductionTask
    {
        // Calculate end date (multi-day blockout, skip weekends)
        $task->scheduled_end_date = $task->calculateEndDate();
        $task->save();

        // If urgent → trigger preemption
        if ($task->isUrgent() || $task->isCritical()) {
            $this->preemptForUrgent($task);
        }

        return $task;
    }

    /**
     * URGENT PREEMPTION: Pause/shift all conflicting standard tasks.
     *
     * Algorithm:
     * 1. Find all non-urgent, active tasks on the same machine/date range.
     * 2. Shift them forward by the urgent task's duration.
     * 3. Log the shift.
     */
    public function preemptForUrgent(ProductionTask $urgentTask): int
    {
        $conflicting = ProductionTask::query()
            ->where('id', '!=', $urgentTask->id)
            ->where('priority', '!=', 'urgent')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('scheduled_start_date', '<=', $urgentTask->scheduled_end_date)
            ->where(function ($q) use ($urgentTask) {
                $q->where('scheduled_end_date', '>=', $urgentTask->scheduled_start_date)
                  ->orWhereNull('scheduled_end_date');
            });

        // If machine-specific, only shift tasks on same machine
        if ($urgentTask->assigned_machine_id) {
            $conflicting->where('assigned_machine_id', $urgentTask->assigned_machine_id);
        }

        $tasks = $conflicting->orderBy('scheduled_start_date')->get();
        $shifted = 0;

        foreach ($tasks as $task) {
            $originalStart = $task->scheduled_start_date->copy();
            $originalEnd   = $task->scheduled_end_date?->copy();

            // Shift forward by urgent task duration
            $newStart = $this->nextAvailableDate(
                $urgentTask->scheduled_end_date->copy()->addDay(),
                $task->assigned_machine_id
            );

            $task->scheduled_start_date = $newStart;
            $task->scheduled_end_date   = $task->calculateEndDate();
            $task->status = 'paused';
            $task->preempted_by = $urgentTask->id;
            $task->save();

            // Log the shift
            ScheduleShiftLog::create([
                'task_id'        => $task->id,
                'trigger'        => 'urgent_preemption',
                'original_start' => $originalStart,
                'new_start'      => $task->scheduled_start_date,
                'original_end'   => $originalEnd,
                'new_end'        => $task->scheduled_end_date,
                'reason'         => "Preempted by urgent task: {$urgentTask->name} (#{$urgentTask->id})",
            ]);

            $shifted++;
        }

        return $shifted;
    }

    /**
     * MACHINE DOWNTIME: Cascade-shift all affected tasks forward.
     *
     * Algorithm:
     * 1. Find tasks assigned to the downed machine during the downtime window.
     * 2. Shift each task forward by the downtime duration (in working days).
     * 3. Cascade: if shifted task now conflicts with another, shift that too.
     */
    public function handleDowntime(MachineDowntime $downtime): int
    {
        $machineId    = $downtime->machine_id;
        $downtimeStart = $downtime->start_time->toDateString();
        $downtimeEnd   = $downtime->endTime()->toDateString();
        $shiftDays     = $downtime->durationInDays();

        // Find affected tasks
        $affected = ProductionTask::query()
            ->where('assigned_machine_id', $machineId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('scheduled_start_date', '<=', $downtimeEnd)
            ->where(function ($q) use ($downtimeStart) {
                $q->where('scheduled_end_date', '>=', $downtimeStart)
                  ->orWhereNull('scheduled_end_date');
            })
            ->orderBy('scheduled_start_date')
            ->get();

        $shifted = 0;

        foreach ($affected as $task) {
            $originalStart = $task->scheduled_start_date->copy();
            $originalEnd   = $task->scheduled_end_date?->copy();

            // Shift forward by downtime days
            $newStart = $this->addWorkingDays($task->scheduled_start_date, $shiftDays);
            $task->scheduled_start_date = $newStart;
            $task->scheduled_end_date   = $task->calculateEndDate();
            $task->save();

            ScheduleShiftLog::create([
                'task_id'        => $task->id,
                'trigger'        => 'machine_downtime',
                'original_start' => $originalStart,
                'new_start'      => $newStart,
                'original_end'   => $originalEnd,
                'new_end'        => $task->scheduled_end_date,
                'reason'         => "Machine downtime: {$downtime->reason} ({$downtime->duration_hours}h)",
            ]);

            $shifted++;
        }

        // Cascade: check if shifted tasks now conflict with each other
        $this->resolveConflicts($machineId);

        return $shifted;
    }

    /**
     * CONFLICT RESOLUTION: Ensure no two tasks on the same machine overlap.
     */
    public function resolveConflicts(?int $machineId = null): int
    {
        $query = ProductionTask::whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('priority', 'desc')  // urgent first
            ->orderBy('sort_order')
            ->orderBy('scheduled_start_date');

        if ($machineId) {
            $query->where('assigned_machine_id', $machineId);
        }

        $tasks = $query->get();
        $resolved = 0;

        // Group by machine
        $byMachine = $tasks->groupBy('assigned_machine_id');

        foreach ($byMachine as $mId => $machineTasks) {
            $lastEnd = null;

            foreach ($machineTasks as $task) {
                if ($lastEnd && $task->scheduled_start_date <= $lastEnd) {
                    // Conflict! Shift this task after the previous one
                    $originalStart = $task->scheduled_start_date->copy();
                    $task->scheduled_start_date = $this->nextWorkingDay($lastEnd->copy()->addDay());
                    $task->scheduled_end_date = $task->calculateEndDate();
                    $task->save();

                    ScheduleShiftLog::create([
                        'task_id'        => $task->id,
                        'trigger'        => 'conflict_resolution',
                        'original_start' => $originalStart,
                        'new_start'      => $task->scheduled_start_date,
                        'reason'         => 'Auto-resolved scheduling conflict',
                    ]);

                    $resolved++;
                }

                $lastEnd = $task->scheduled_end_date ?? $task->scheduled_start_date;
            }
        }

        return $resolved;
    }

    /**
     * RESUME paused tasks after urgent task completes.
     */
    public function resumeAfterUrgent(ProductionTask $completedUrgent): int
    {
        $paused = ProductionTask::where('preempted_by', $completedUrgent->id)
            ->where('status', 'paused')
            ->get();

        $resumed = 0;
        foreach ($paused as $task) {
            $task->status = 'pending';
            $task->preempted_by = null;
            // Optionally reschedule to earliest available
            $task->scheduled_start_date = $this->nextAvailableDate(
                now()->toDate(),
                $task->assigned_machine_id
            );
            $task->scheduled_end_date = $task->calculateEndDate();
            $task->save();
            $resumed++;
        }

        return $resumed;
    }

    /**
     * REPORTING: Get completed tasks within a timeframe.
     */
    public function getCompletedTasks(Carbon $from, Carbon $to): Collection
    {
        return ProductionTask::where('status', 'completed')
            ->where('actual_end_date', '>=', $from)
            ->where('actual_end_date', '<=', $to)
            ->orderBy('actual_end_date', 'desc')
            ->get();
    }

    /**
     * REPORTING: Performance metrics for a period.
     */
    public function getMetrics(Carbon $from, Carbon $to): array
    {
        $completed = $this->getCompletedTasks($from, $to);

        return [
            'period_start'        => $from->toDateString(),
            'period_end'          => $to->toDateString(),
            'total_completed'     => $completed->count(),
            'total_pending'       => ProductionTask::where('status', 'pending')->count(),
            'total_in_progress'   => ProductionTask::where('status', 'in_progress')->count(),
            'total_paused'        => ProductionTask::where('status', 'paused')->count(),
            'urgent_completed'    => $completed->where('priority', 'urgent')->count(),
            'avg_duration_days'   => round($completed->avg('duration_days'), 1),
            'by_process'          => $completed->groupBy('process')->map->count(),
            'by_machine'          => $completed->groupBy('assigned_machine_id')->map->count(),
            'downtime_events'     => MachineDowntime::whereBetween('start_time', [$from, $to])->count(),
            'shifts_logged'       => ScheduleShiftLog::whereBetween('created_at', [$from, $to])->count(),
            'completed_tasks'     => $completed->map(fn($t) => [
                'id'          => $t->id,
                'name'        => $t->name,
                'process'     => $t->process,
                'duration'    => $t->duration_days . 'd',
                'started'     => $t->actual_start_date?->format('Y-m-d'),
                'completed'   => $t->actual_end_date?->format('Y-m-d'),
                'machine_id'  => $t->assigned_machine_id,
            ])->values(),
        ];
    }

    // ── Helpers ──────────────────────────────────────────

    /**
     * PRODUCTION TRACKING: Get today's active work for each machine.
     */
    public function getTodaysWorkQueue(): array
    {
        $today = now()->toDateString();

        $tasks = ProductionTask::with('machine')
            ->where('scheduled_start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->where('scheduled_end_date', '>=', $today)
                  ->orWhereNull('scheduled_end_date');
            })
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByRaw("FIELD(priority, 'urgent', 'standard')")
            ->orderBy('sort_order')
            ->get();

        // Group by machine
        $queue = [];
        foreach ($tasks as $task) {
            $machineKey = $task->assigned_machine_id ?? 'unassigned';
            $queue[$machineKey][] = [
                'id'       => $task->id,
                'name'     => $task->name,
                'process'  => $task->process,
                'status'   => $task->status,
                'priority' => $task->priority,
                'days_remaining' => $task->scheduled_end_date
                    ? max(0, now()->diffInDays($task->scheduled_end_date, false))
                    : 0,
            ];
        }

        return $queue;
    }

    /**
     * PRODUCTION TRACKING: Track a job through the production pipeline.
     * Returns the current stage and progress of a job (e.g. book title).
     */
    public function trackJob(string $jobName): array
    {
        $stages = ['Design', 'Press', 'Folding', 'Gathering', 'Staple', 'Binding', 'Cutting', 'Packaging', 'Delivery'];

        $tasks = ProductionTask::where('name', 'like', "%{$jobName}%")
            ->orderBy('scheduled_start_date')
            ->get();

        $pipeline = [];
        foreach ($stages as $stage) {
            $stageTask = $tasks->firstWhere('process', $stage);
            $pipeline[] = [
                'stage'  => $stage,
                'status' => $stageTask?->status ?? 'not_scheduled',
                'start'  => $stageTask?->scheduled_start_date?->format('d/m'),
                'end'    => $stageTask?->scheduled_end_date?->format('d/m'),
                'machine'=> $stageTask?->machine?->name,
            ];
        }

        $completedStages = collect($pipeline)->where('status', 'completed')->count();
        $totalStages = count($stages);
        $progress = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;

        return [
            'job_name'    => $jobName,
            'pipeline'    => $pipeline,
            'progress'    => $progress,
            'current_stage' => collect($pipeline)->firstWhere('status', 'in_progress')['stage']
                           ?? collect($pipeline)->firstWhere('status', 'pending')['stage']
                           ?? 'Done',
        ];
    }

    /**
     * MACHINE UTILIZATION: Calculate how busy each machine is this month.
     */
    public function getMachineUtilization(Carbon $from, Carbon $to): Collection
    {
        $machines = \App\Models\Machine::where('status', '!=', 'retired')->get();
        $totalWorkingDays = $this->countWorkingDays($from, $to);

        return $machines->map(function ($machine) use ($from, $to, $totalWorkingDays) {
            $bookedDays = ProductionTask::where('assigned_machine_id', $machine->id)
                ->whereNotIn('status', ['cancelled'])
                ->where('scheduled_start_date', '<=', $to)
                ->where(function ($q) use ($from) {
                    $q->where('scheduled_end_date', '>=', $from)
                      ->orWhereNull('scheduled_end_date');
                })
                ->sum('duration_days');

            $utilization = $totalWorkingDays > 0 ? min(100, round(($bookedDays / $totalWorkingDays) * 100)) : 0;

            $downtimeHours = MachineDowntime::where('machine_id', $machine->id)
                ->whereBetween('start_time', [$from, $to])
                ->sum('duration_hours');

            return [
                'id'             => $machine->id,
                'name'           => $machine->name,
                'code'           => $machine->code,
                'type'           => $machine->type,
                'status'         => $machine->status,
                'booked_days'    => (int) $bookedDays,
                'total_days'     => $totalWorkingDays,
                'utilization_pct'=> $utilization,
                'downtime_hours' => (int) $downtimeHours,
            ];
        });
    }

    /**
     * UPCOMING: Get tasks starting in the next N days.
     */
    public function getUpcoming(int $days = 7): Collection
    {
        return ProductionTask::with('machine')
            ->where('scheduled_start_date', '>=', now())
            ->where('scheduled_start_date', '<=', now()->addDays($days))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('scheduled_start_date')
            ->orderByRaw("FIELD(priority, 'urgent', 'standard')")
            ->get();
    }

    /**
     * Count working days between two dates.
     */
    private function countWorkingDays(Carbon $from, Carbon $to): int
    {
        $count = 0;
        $current = $from->copy();
        while ($current <= $to) {
            if (! $current->isWeekend()) $count++;
            $current->addDay();
        }
        return $count;
    }

    /**
     * Find the next available date for a machine (no existing task).
     */
    private function nextAvailableDate(Carbon $from, ?int $machineId): Carbon
    {
        $date = $this->nextWorkingDay($from);

        if (! $machineId) return $date;

        // Check up to 60 days ahead
        for ($i = 0; $i < 60; $i++) {
            $conflict = ProductionTask::where('assigned_machine_id', $machineId)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->where('scheduled_start_date', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->where('scheduled_end_date', '>=', $date)
                      ->orWhereNull('scheduled_end_date');
                })
                ->exists();

            if (! $conflict) return $date;

            $date = $this->nextWorkingDay($date->addDay());
        }

        return $date; // fallback
    }

    /**
     * Add N working days to a date (skip weekends).
     */
    private function addWorkingDays(Carbon $date, int $days): Carbon
    {
        $result = $date->copy();
        $added = 0;
        while ($added < $days) {
            $result->addDay();
            if (! $result->isWeekend()) $added++;
        }
        return $result;
    }

    /**
     * Get next working day (skip weekends).
     */
    private function nextWorkingDay(Carbon $date): Carbon
    {
        while ($date->isWeekend()) {
            $date->addDay();
        }
        return $date;
    }
}
