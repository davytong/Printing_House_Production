<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Machine;
use App\Models\PrintRequest;
use App\Models\ProductionActual;
use App\Models\ProductionJob;
use App\Models\ProductionProcess;
use App\Models\ProductionSchedule;
use App\Models\ProductionTask;
use App\Models\ProductionTemplate;
use App\Models\ScheduleAudit;
use App\Models\ScheduleShiftLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductionPlanningService
{
    /**
     * Generate a complete production plan preview with capacity calculation,
     * working-day awareness, dependency chaining, and conflict detection.
     */
    public function generatePlan(array $params): array
    {
        $jobName       = trim($params['name'] ?? 'Untitled Job');
        $quantity      = max(1, (int)($params['quantity'] ?? 1000));
        $startDate     = Carbon::parse($params['start_date'] ?? now());
        $dueDate       = !empty($params['due_date']) ? Carbon::parse($params['due_date']) : null;
        $priority      = in_array($params['priority'] ?? '', ['low', 'normal', 'high', 'urgent']) ? $params['priority'] : 'normal';
        $templateId    = $params['template_id'] ?? null;
        $includeSundays = (bool)($params['include_sundays'] ?? false);
        $bookId        = $params['book_id'] ?? null;
        $printRequestId= $params['print_request_id'] ?? null;

        // 1. Resolve Stages from Template or Processes
        $stagesConfig = [];
        if ($templateId) {
            $template = ProductionTemplate::with('processes')->find($templateId);
            if ($template && $template->processes->isNotEmpty()) {
                foreach ($template->processes as $tp) {
                    $stagesConfig[] = [
                        'process_name' => $tp->process_name,
                        'capacity'     => $tp->capacity,
                        'machine_id'   => $params['stage_machines'][$tp->process_name] ?? null,
                    ];
                }
            }
        }

        // Fallback default stages if no template
        if (empty($stagesConfig)) {
            $defaultProcesses = ['Press', 'Folding', 'Gathering', 'Binding', 'Cutting', 'Packaging'];
            foreach ($defaultProcesses as $proc) {
                $stagesConfig[] = [
                    'process_name' => $proc,
                    'capacity'     => null,
                    'machine_id'   => $params['stage_machines'][$proc] ?? null,
                ];
            }
        }

        // 2. Fetch processes, machines, and holidays
        $allProcesses = ProductionProcess::where('is_active', true)->get()->keyBy('name');
        $allMachines  = Machine::where('status', '!=', 'retired')->get();
        $holidays     = $this->getConfiguredHolidays();

        // 3. Sequentially calculate dates and allocations per stage
        $planStages = [];
        $currentCursorDate = $startDate->copy();
        $conflicts = [];
        $warnings = [];

        // Ensure start date is on a working day
        $currentCursorDate = $this->ensureWorkingDay($currentCursorDate, $includeSundays, $holidays);

        foreach ($stagesConfig as $index => $stage) {
            $procName = $stage['process_name'];
            $procModel = $allProcesses->get($procName);

            // Determine Assigned Machine
            $assignedMachine = null;
            if (!empty($stage['machine_id'])) {
                $assignedMachine = $allMachines->firstWhere('id', (int)$stage['machine_id']);
            } else {
                // Auto-assign first available operational machine matching process
                $assignedMachine = $allMachines->first(function ($m) use ($procName) {
                    return ($m->process_name === $procName || $m->type === strtolower($procName))
                        && $m->status === 'operational';
                });
            }

            // Determine Daily Capacity
            $dailyCapacity = $stage['capacity']
                ?: ($assignedMachine?->daily_capacity
                    ?: ($procModel?->default_capacity ?: 8000));

            // Calculate Required Days and Day-by-Day Quantity Split
            $stageAllocation = $this->calculateDailySplit($quantity, $dailyCapacity);
            $stageDaysCount  = count($stageAllocation);

            $stageDates = [];
            $stageStartDate = $currentCursorDate->copy();

            foreach ($stageAllocation as $dayIndex => $allocatedQty) {
                // Advance to valid working day
                $targetDate = $currentCursorDate->copy();

                // Conflict check for this date + machine/process
                $conflictInfo = $this->checkDateConflict(
                    $targetDate,
                    $procName,
                    $assignedMachine?->id,
                    $allocatedQty,
                    $assignedMachine?->daily_capacity ?: $dailyCapacity
                );

                if ($conflictInfo['has_conflict']) {
                    $conflicts[] = [
                        'date'         => $targetDate->toDateString(),
                        'process'      => $procName,
                        'machine'      => $assignedMachine?->name ?? 'Default',
                        'message'      => $conflictInfo['message'],
                        'severity'     => $conflictInfo['severity'],
                    ];
                }

                $stageDates[] = [
                    'date'          => $targetDate->toDateString(),
                    'formatted'     => $targetDate->format('d/m/Y'),
                    'day_name'      => $targetDate->format('D'),
                    'year'          => $targetDate->year,
                    'month'         => $targetDate->month,
                    'day'           => $targetDate->day,
                    'allocated_qty' => $allocatedQty,
                    'is_sunday'     => $targetDate->dayOfWeek === 0,
                    'conflict'      => $conflictInfo['has_conflict'] ? $conflictInfo['message'] : null,
                ];

                // Advance cursor to next working day for subsequent day of same stage
                if ($dayIndex < $stageDaysCount - 1) {
                    $currentCursorDate->addDay();
                    $currentCursorDate = $this->ensureWorkingDay($currentCursorDate, $includeSundays, $holidays);
                }
            }

            $stageEndDate = $currentCursorDate->copy();

            $planStages[] = [
                'process_name'   => $procName,
                'process_color'  => $procModel?->color ?? '#475569',
                'machine_id'     => $assignedMachine?->id,
                'machine_name'   => $assignedMachine?->name ?? 'Standard Workflow',
                'machine_code'   => $assignedMachine?->code,
                'daily_capacity' => $dailyCapacity,
                'duration_days'  => $stageDaysCount,
                'quantity'       => $quantity,
                'start_date'     => $stageStartDate->toDateString(),
                'end_date'       => $stageEndDate->toDateString(),
                'formatted_start_date' => $stageStartDate->format('d/m/Y'),
                'formatted_end_date'   => $stageEndDate->format('d/m/Y'),
                'dates'          => $stageDates,
            ];

            // Next sequential stage starts on next working day
            $currentCursorDate->addDay();
            $currentCursorDate = $this->ensureWorkingDay($currentCursorDate, $includeSundays, $holidays);
        }

        // Summary Calculations
        $finalEndDate = !empty($planStages)
            ? Carbon::parse(end($planStages)['end_date'])
            : $startDate;

        $totalProductionDays = $this->countWorkingDays($startDate, $finalEndDate, $includeSundays, $holidays);

        $isOnTime = true;
        $overdueDays = 0;
        if ($dueDate) {
            if ($finalEndDate->gt($dueDate)) {
                $isOnTime = false;
                $overdueDays = $dueDate->diffInDays($finalEndDate, false);
                $warnings[] = "Due date {$dueDate->format('d/m/Y')} will be exceeded by {$overdueDays} days. Completion expected on {$finalEndDate->format('d/m/Y')}.";
            }
        }

        if (!empty($conflicts)) {
            $warnings[] = count($conflicts) . ' potential machine/capacity conflicts detected.';
        }

        return [
            'job' => [
                'name'             => $jobName,
                'quantity'         => $quantity,
                'priority'         => $priority,
                'start_date'       => $startDate->toDateString(),
                'due_date'         => $dueDate?->toDateString(),
                'template_id'      => $templateId,
                'book_id'          => $bookId,
                'print_request_id' => $printRequestId,
                'include_sundays'  => $includeSundays,
            ],
            'stages' => $planStages,
            'summary' => [
                'start_date'          => $startDate->toDateString(),
                'end_date'            => $finalEndDate->toDateString(),
                'total_calendar_days' => $startDate->diffInDays($finalEndDate) + 1,
                'total_working_days'  => $totalProductionDays,
                'due_date'            => $dueDate?->toDateString(),
                'is_on_time'          => $isOnTime,
                'overdue_days'        => $overdueDays,
                'conflicts_count'     => count($conflicts),
                'conflicts'           => $conflicts,
                'warnings'            => $warnings,
                'status'              => empty($conflicts) && $isOnTime ? 'optimal' : 'warning',
            ],
        ];
    }

    /**
     * Confirm and commit a generated plan into the database.
     * Creates ProductionJob, ProductionSchedule grid cells, and ProductionTask entries.
     */
    public function confirmPlan(array $planData, ?string $userName = null): ProductionJob
    {
        return DB::transaction(function () use ($planData, $userName) {
            $jobInfo = $planData['job'];
            $stages  = $planData['stages'];

            // 1. Create ProductionJob
            $job = ProductionJob::create([
                'name'             => $jobInfo['name'],
                'quantity'         => $jobInfo['quantity'],
                'priority'         => $jobInfo['priority'] ?? 'normal',
                'start_date'       => $jobInfo['start_date'],
                'due_date'         => $jobInfo['due_date'] ?? null,
                'template_id'      => $jobInfo['template_id'] ?? null,
                'book_id'          => $jobInfo['book_id'] ?? null,
                'print_request_id' => $jobInfo['print_request_id'] ?? null,
                'status'           => 'confirmed',
                'created_by'       => $userName ?? session('user_name') ?? 'System',
            ]);

            // Update PrintRequest status if linked
            if (!empty($jobInfo['print_request_id'])) {
                $req = PrintRequest::find($jobInfo['print_request_id']);
                if ($req && in_array($req->status, ['pending', 'approved'])) {
                    $req->update(['status' => 'in_production']);
                }
            }

            // 2. Populate ProductionSchedules (Calendar Grid Cells)
            foreach ($stages as $stage) {
                $procName   = $stage['process_name'];
                $procColor  = $stage['process_color'] ?? '#3b82f6';
                $machineId  = $stage['machine_id'] ?? null;
                $datesCount = count($stage['dates']);

                foreach ($stage['dates'] as $dateIndex => $d) {
                    $year  = (int)$d['year'];
                    $month = (int)$d['month'];
                    $day   = (int)$d['day'];
                    $qty   = (int)$d['allocated_qty'];

                    $taskLabel = $datesCount > 1
                        ? "{$job->name} (" . ($dateIndex + 1) . "/{$datesCount})"
                        : $job->name;

                    // Check if cell already exists for this process & date
                    $existingCell = ProductionSchedule::where([
                        'year'    => $year,
                        'month'   => $month,
                        'process' => $procName,
                        'day'     => $day,
                    ])->first();

                    if ($existingCell) {
                        // Append task to cell
                        $currentTasks = array_filter(array_map('trim', explode(',', (string)$existingCell->task)));
                        if (!in_array($taskLabel, $currentTasks)) {
                            $currentTasks[] = $taskLabel;
                        }

                        $existingCell->update([
                            'task'              => implode(', ', $currentTasks),
                            'planned_qty'       => ($existingCell->planned_qty ?? 0) + $qty,
                            'production_job_id' => $job->id,
                            'machine_id'        => $machineId ?: $existingCell->machine_id,
                            'schedule_date'     => $d['date'],
                        ]);
                    } else {
                        ProductionSchedule::create([
                            'year'              => $year,
                            'month'             => $month,
                            'process'           => $procName,
                            'day'               => $day,
                            'schedule_date'     => $d['date'],
                            'production_job_id' => $job->id,
                            'machine_id'        => $machineId,
                            'task'              => $taskLabel,
                            'planned_qty'       => $qty,
                            'actual_qty'        => 0,
                            'color'             => $procColor,
                            'status'            => 'planned',
                            'is_locked'         => false,
                        ]);
                    }
                }

                // 3. Create ProductionTask for Shop Floor / Kanban
                ProductionTask::create([
                    'name'                 => "{$job->name} — {$procName}",
                    'description'          => "Quantity: {$job->quantity} | Planned Daily: {$stage['daily_capacity']}",
                    'process'              => $procName,
                    'duration_days'        => $stage['duration_days'],
                    'status'               => 'pending',
                    'priority'             => $job->priority === 'urgent' ? 'urgent' : 'standard',
                    'assigned_machine_id'  => $machineId,
                    'scheduled_start_date' => $stage['start_date'],
                    'scheduled_end_date'   => $stage['end_date'],
                    'due_date'             => $job->due_date,
                    'notes'                => "Auto-planned for Job #{$job->job_number}",
                ]);
            }

            // 4. Log Audit
            ScheduleAudit::log('plan_confirmed', $job->id, null, null, [
                'job_number' => $job->job_number,
                'stages'     => count($stages),
                'quantity'   => $job->quantity,
            ], "Plan confirmed and populated to calendar");

            return $job;
        });
    }

    /**
     * Record daily actual production output with variance computation.
     */
    public function recordActual(int $scheduleId, int $actualQty, ?string $notes = null, ?string $recordedBy = null): ProductionActual
    {
        return DB::transaction(function () use ($scheduleId, $actualQty, $notes, $recordedBy) {
            $schedule = ProductionSchedule::with('job')->findOrFail($scheduleId);

            $plannedQty = $schedule->planned_qty ?? 0;
            $oldActual  = $schedule->actual_qty ?? 0;

            // Update schedule actual
            $schedule->actual_qty = $actualQty;

            // Auto-update status
            if ($actualQty >= $plannedQty && $plannedQty > 0) {
                $schedule->status = 'done';
            } elseif ($actualQty > 0) {
                $schedule->status = 'in_progress';
            }
            $schedule->save();

            // Create actual log
            $actualLog = ProductionActual::create([
                'production_schedule_id' => $schedule->id,
                'production_job_id'      => $schedule->production_job_id,
                'production_date'        => $schedule->schedule_date ?: Carbon::createFromDate($schedule->year, $schedule->month, $schedule->day)->toDateString(),
                'planned_qty'            => $plannedQty,
                'actual_qty'             => $actualQty,
                'variance'               => $actualQty - $plannedQty,
                'notes'                  => $notes,
                'recorded_by'            => $recordedBy ?? session('user_name') ?? 'Operator',
            ]);

            ScheduleAudit::log(
                'actual_recorded',
                $schedule->production_job_id,
                $schedule->id,
                ['actual_qty' => $oldActual],
                ['actual_qty' => $actualQty, 'variance' => $actualQty - $plannedQty],
                $notes
            );

            return $actualLog;
        });
    }

    /**
     * Generate an intelligent reschedule suggestion for delayed tasks.
     * Shifts downstream processes forward across working days, keeping locked schedules safe.
     */
    public function generateRescheduleSuggestion(int $scheduleId, int $delayWorkingDays): array
    {
        $schedule = ProductionSchedule::with(['job', 'machine'])->findOrFail($scheduleId);
        $jobId    = $schedule->production_job_id;

        $targetDate = $schedule->schedule_date
            ?: Carbon::createFromDate($schedule->year, $schedule->month, $schedule->day);

        // Find all subsequent schedules for the same job (or same process) starting on or after targetDate
        $query = ProductionSchedule::query();
        if ($jobId) {
            $query->where('production_job_id', $jobId);
        } else {
            $query->where('process', $schedule->process)->where('year', $schedule->year)->where('month', $schedule->month);
        }

        $allAffected = $query->where('day', '>=', $schedule->day)
            ->orderBy('day')
            ->get();

        $shifts = [];
        $holidays = $this->getConfiguredHolidays();

        foreach ($allAffected as $item) {
            if ($item->isLocked()) {
                $shifts[] = [
                    'id'           => $item->id,
                    'process'      => $item->process,
                    'original_day' => $item->day,
                    'new_day'      => $item->day,
                    'is_locked'    => true,
                    'note'         => 'LOCKED (Cannot be moved)',
                ];
                continue;
            }

            $origDate = Carbon::createFromDate($item->year, $item->month, $item->day);
            $newDate  = $this->addWorkingDays($origDate, $delayWorkingDays, false, $holidays);

            $shifts[] = [
                'id'            => $item->id,
                'process'       => $item->process,
                'task'          => $item->task,
                'original_date' => $origDate->format('d/m/Y'),
                'original_day'  => $item->day,
                'new_date'      => $newDate->format('d/m/Y'),
                'new_day'       => $newDate->day,
                'new_month'     => $newDate->month,
                'new_year'      => $newDate->year,
                'is_locked'     => false,
            ];
        }

        return [
            'schedule_id'    => $scheduleId,
            'job_id'         => $jobId,
            'job_name'       => $schedule->job?->name ?? $schedule->task,
            'delay_days'     => $delayWorkingDays,
            'affected_count' => count($shifts),
            'shifts'         => $shifts,
        ];
    }

    /**
     * Apply an approved reschedule proposal to the calendar.
     */
    public function applyReschedule(array $rescheduleData, ?string $reason = null): int
    {
        return DB::transaction(function () use ($rescheduleData, $reason) {
            $shifts = $rescheduleData['shifts'] ?? [];
            $appliedCount = 0;

            foreach ($shifts as $shift) {
                if (!empty($shift['is_locked'])) continue;

                $schedule = ProductionSchedule::find($shift['id']);
                if (!$schedule) continue;

                $oldDay   = $schedule->day;
                $oldMonth = $schedule->month;
                $oldYear  = $schedule->year;

                $newDay   = (int)$shift['new_day'];
                $newMonth = (int)($shift['new_month'] ?? $oldMonth);
                $newYear  = (int)($shift['new_year'] ?? $oldYear);

                // Check if target cell exists
                $targetCell = ProductionSchedule::where([
                    'year'    => $newYear,
                    'month'   => $newMonth,
                    'process' => $schedule->process,
                    'day'     => $newDay,
                ])->where('id', '!=', $schedule->id)->first();

                if ($targetCell) {
                    // Merge
                    $curTasks = array_filter(array_map('trim', explode(',', (string)$targetCell->task)));
                    $newTasks = array_filter(array_map('trim', explode(',', (string)$schedule->task)));
                    foreach ($newTasks as $nt) {
                        if (!in_array($nt, $curTasks)) $curTasks[] = $nt;
                    }
                    $targetCell->update([
                        'task'        => implode(', ', $curTasks),
                        'planned_qty' => ($targetCell->planned_qty ?? 0) + ($schedule->planned_qty ?? 0),
                    ]);
                    $schedule->delete();
                } else {
                    $schedule->update([
                        'year'          => $newYear,
                        'month'         => $newMonth,
                        'day'           => $newDay,
                        'schedule_date' => Carbon::createFromDate($newYear, $newMonth, $newDay)->toDateString(),
                    ]);
                }

                ScheduleAudit::log('rescheduled', $schedule->production_job_id, $schedule->id, [
                    'date' => "{$oldDay}/{$oldMonth}/{$oldYear}",
                ], [
                    'date' => "{$newDay}/{$newMonth}/{$newYear}",
                ], $reason ?? 'Auto-reschedule applied');

                $appliedCount++;
            }

            return $appliedCount;
        });
    }

    /**
     * Run What-If simulation without writing to DB.
     */
    public function simulatePlan(array $params): array
    {
        return $this->generatePlan($params);
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────────────────────

    /**
     * Calculate day-by-day split of quantity according to machine daily capacity.
     * Example: 20,000 qty @ 8,000/day => [8000, 8000, 4000]
     */
    public function calculateDailySplit(int $totalQty, int $dailyCapacity): array
    {
        if ($dailyCapacity <= 0) $dailyCapacity = 8000;
        if ($totalQty <= 0) return [0];

        $splits = [];
        $remaining = $totalQty;

        while ($remaining > 0) {
            $allocated = min($remaining, $dailyCapacity);
            $splits[] = $allocated;
            $remaining -= $allocated;
        }

        return $splits;
    }

    /**
     * Ensure a date falls on a working day (Mon-Sat, skips Sunday and configured holidays).
     */
    public function ensureWorkingDay(Carbon $date, bool $includeSundays = false, array $holidays = []): Carbon
    {
        $current = $date->copy();
        while (true) {
            $isSunday  = $current->dayOfWeek === 0;
            $isHoliday = in_array($current->toDateString(), $holidays);

            if ((!$includeSundays && $isSunday) || $isHoliday) {
                $current->addDay();
            } else {
                break;
            }
        }
        return $current;
    }

    /**
     * Add N working days to a date.
     */
    public function addWorkingDays(Carbon $date, int $days, bool $includeSundays = false, array $holidays = []): Carbon
    {
        $result = $date->copy();
        $added = 0;
        while ($added < $days) {
            $result->addDay();
            $isSunday  = $result->dayOfWeek === 0;
            $isHoliday = in_array($result->toDateString(), $holidays);

            if (($includeSundays || !$isSunday) && !$isHoliday) {
                $added++;
            }
        }
        return $result;
    }

    /**
     * Count working days between two dates.
     */
    public function countWorkingDays(Carbon $from, Carbon $to, bool $includeSundays = false, array $holidays = []): int
    {
        $count = 0;
        $curr = $from->copy();
        while ($curr->lte($to)) {
            $isSunday  = $curr->dayOfWeek === 0;
            $isHoliday = in_array($curr->toDateString(), $holidays);

            if (($includeSundays || !$isSunday) && !$isHoliday) {
                $count++;
            }
            $curr->addDay();
        }
        return $count;
    }

    /**
     * Check if a machine or process has a conflict on a specific date.
     */
    public function checkDateConflict(Carbon $date, string $process, ?int $machineId, int $newQty, int $maxCapacity): array
    {
        $query = ProductionSchedule::where('year', $date->year)
            ->where('month', $date->month)
            ->where('day', $date->day)
            ->where('process', $process);

        if ($machineId) {
            $query->where('machine_id', $machineId);
        }

        $existingSchedules = $query->get();
        $bookedQty = (int)$existingSchedules->sum('planned_qty');

        if ($bookedQty > 0 && ($bookedQty + $newQty) > $maxCapacity) {
            return [
                'has_conflict' => true,
                'severity'     => 'danger',
                'message'      => "Capacity overload on {$date->format('d/m/Y')}: Already booked {$bookedQty} + new {$newQty} > Capacity {$maxCapacity}.",
            ];
        }

        // Check Machine Maintenance
        if ($machineId) {
            $machine = Machine::find($machineId);
            if ($machine && $machine->status === 'maintenance') {
                return [
                    'has_conflict' => true,
                    'severity'     => 'warning',
                    'message'      => "Machine {$machine->name} is marked as Under Maintenance.",
                ];
            }
        }

        return ['has_conflict' => false, 'severity' => 'none', 'message' => ''];
    }

    /**
     * Retrieve configured holidays array from Settings or default.
     */
    public function getConfiguredHolidays(): array
    {
        $stored = Setting::get('production_holidays', '[]');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;
        return is_array($decoded) ? $decoded : [];
    }
}
