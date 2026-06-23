<?php

namespace App\Http\Controllers;

use App\Models\ProductionSchedule;
use App\Models\ScheduleDelayLog;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Default processes in order.
     */
    private array $processes = [
        'Design',
        'Press',
        'Folding',
        'Gathering',
        'Staple',
        'Binding',
        'Cutting',
        'Packaging',
        'Delivery',
        'Other',
    ];

    /**
     * Show monthly schedule grid.
     */
    public function index(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $schedules   = ProductionSchedule::forMonth($year, $month);

        // Today's tasks for alert panel
        $todayTasks = ProductionSchedule::where('year', now()->year)
            ->where('month', now()->month)
            ->where('day', now()->day)
            ->get();

        // Tomorrow's tasks for upcoming alert
        $tomorrow = now()->addDay();
        $tomorrowTasks = ProductionSchedule::where('year', $tomorrow->year)
            ->where('month', $tomorrow->month)
            ->where('day', $tomorrow->day)
            ->get();

        // Progress: how many days have tasks vs total days in month
        $totalCells = $daysInMonth * count($this->processes);
        $filledCells = ProductionSchedule::where('year', $year)->where('month', $month)->count();
        $progress = $totalCells > 0 ? round(($filledCells / $totalCells) * 100, 1) : 0;

        return view('schedule.index', [
            'year'          => $year,
            'month'         => $month,
            'daysInMonth'   => $daysInMonth,
            'schedules'     => $schedules,
            'processes'     => $this->processes,
            'todayTasks'    => $todayTasks,
            'tomorrowTasks' => $tomorrowTasks,
            'progress'      => $progress,
            'filledCells'   => $filledCells,
            'totalCells'    => $totalCells,
        ]);
    }

    /**
     * Store or update a single cell.
     */
    public function store(Request $request)
    {
        $request->validate([
            'year'    => 'required|integer',
            'month'   => 'required|integer|min:1|max:12',
            'process' => 'required|string',
            'day'     => 'required|integer|min:1|max:31',
            'task'    => 'nullable|string|max:255',
            'note'    => 'nullable|string|max:255',
            'color'   => 'nullable|string|max:30',
        ]);

        if (empty($request->task) && empty($request->note)) {
            ProductionSchedule::where([
                'year'    => $request->year,
                'month'   => $request->month,
                'process' => $request->process,
                'day'     => $request->day,
            ])->delete();

            return redirect()->route('schedule.index', ['year' => $request->year, 'month' => $request->month])
                ->with('success', 'ជម្រះទិន្នន័យបានជោគជ័យ!');
        }

        // Multi-day span support
        $spanDays = max(1, (int) $request->input('span_days', 1));
        $daysInMonth = \Carbon\Carbon::createFromDate($request->year, $request->month, 1)->daysInMonth;
        $saved = 0;

        for ($i = 0; $i < $spanDays; $i++) {
            $targetDay = $request->day + $i;
            if ($targetDay > $daysInMonth) break;

            ProductionSchedule::updateOrCreate(
                ['year' => $request->year, 'month' => $request->month, 'process' => $request->process, 'day' => $targetDay],
                ['task' => $request->task, 'note' => $request->note, 'color' => $request->color]
            );
            $saved++;
        }

        $msg = $saved > 1 ? "Saved across {$saved} days" : 'Saved!';
        return redirect()->route('schedule.index', ['year' => $request->year, 'month' => $request->month])
            ->with('success', $msg);
    }

    /**
     * Bulk save all cells for a month.
     */
    public function bulkSave(Request $request)
    {
        $request->validate([
            'year'    => 'required|integer',
            'month'   => 'required|integer|min:1|max:12',
            'cells'   => 'required|array',
            'cells.*.process' => 'required|string',
            'cells.*.day'     => 'required|integer|min:1|max:31',
            'cells.*.task'    => 'nullable|string|max:255',
            'cells.*.note'    => 'nullable|string|max:255',
            'cells.*.color'   => 'nullable|string|max:30',
        ]);

        $year  = $request->year;
        $month = $request->month;

        foreach ($request->cells as $cell) {
            if (empty($cell['task']) && empty($cell['note'])) {
                ProductionSchedule::where([
                    'year'    => $year,
                    'month'   => $month,
                    'process' => $cell['process'],
                    'day'     => $cell['day'],
                ])->delete();
            } else {
                ProductionSchedule::updateOrCreate(
                    [
                        'year'    => $year,
                        'month'   => $month,
                        'process' => $cell['process'],
                        'day'     => $cell['day'],
                    ],
                    [
                        'task'  => $cell['task'] ?? null,
                        'note'  => $cell['note'] ?? null,
                        'color' => $cell['color'] ?? null,
                    ]
                );
            }
        }

        return redirect()->route('schedule.index', ['year' => $year, 'month' => $month])
            ->with('success', 'រក្សាទុកកាលវិភាគបានជោគជ័យ!');
    }

    /**
     * Export month schedule as printable calendar view or ICS file.
     */
    public function exportCalendar(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $format = $request->get('format', 'html');

        $entries = ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->whereNotNull('task')
            ->orderBy('day')
            ->orderBy('process')
            ->get();

        return view('schedule.export-calendar', [
            'year'      => $year,
            'month'     => $month,
            'entries'   => $entries,
            'processes' => $this->processes,
        ]);
    }

    /**
     * Send today's schedule as Telegram alert to selected group(s).
     */
    public function sendTelegramAlert(Request $request)
    {
        $request->validate([
            'year'     => 'required|integer',
            'month'    => 'required|integer|min:1|max:12',
            'day'      => 'required|integer|min:1|max:31',
            'group_id' => 'required|string',
        ]);

        $year  = (int) $request->year;
        $month = (int) $request->month;
        $day   = (int) $request->day;

        $tasks = ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->where('day', $day)
            ->whereNotNull('task')
            ->orderBy('process')
            ->get();

        if ($tasks->isEmpty()) {
            return redirect()->route('schedule.index', ['year' => $year, 'month' => $month])
                ->with('error', 'មិនមានកិច្ចការសម្រាប់ថ្ងៃនេះ!');
        }

        $date = Carbon::createFromDate($year, $month, $day)->format('d/m/Y');
        $dayName = Carbon::createFromDate($year, $month, $day)->locale('km')->dayName;

        $message = "📅 កាលវិភាគផលិតកម្ម — {$date} ({$dayName})\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($tasks as $task) {
            $emoji = $this->processEmoji($task->process);
            $message .= "{$emoji} {$task->process}: {$task->task}";
            if ($task->note) {
                $message .= " ({$task->note})";
            }
            $message .= "\n";
        }

        $message .= "\n━━━━━━━━━━━━━━━━━━\n";
        $message .= "✅ សរុប: {$tasks->count()} ដំណើរការ";

        $telegram = new TelegramService();
        $sent = 0;

        if ($request->group_id === 'all') {
            // Send to all groups
            $sent = $telegram->broadcastMessage($message);
        } else {
            // Send to specific group
            $group = \App\Models\TelegramGroup::find($request->group_id);
            if ($group) {
                $success = $telegram->sendMessage($group->chat_id, $message, $group->message_thread_id);
                if ($success) $sent = 1;
            }
        }

        if ($sent > 0) {
            return redirect()->route('schedule.index', ['year' => $year, 'month' => $month])
                ->with('success', "បានផ្ញើការជូនដំណឹង ថ្ងៃ {$date} ទៅ {$sent} group(s)!");
        }

        return redirect()->route('schedule.index', ['year' => $year, 'month' => $month])
            ->with('error', 'ផ្ញើមិនបានជោគជ័យ! សូមពិនិត្យការកំណត់ Telegram.');
    }

    /**
     * Copy current month's schedule to another month.
     */
    public function copyToMonth(Request $request)
    {
        $request->validate([
            'from_year'  => 'required|integer',
            'from_month' => 'required|integer|min:1|max:12',
            'to_year'    => 'required|integer',
            'to_month'   => 'required|integer|min:1|max:12',
        ]);

        $fromEntries = ProductionSchedule::where('year', $request->from_year)
            ->where('month', $request->from_month)
            ->get();

        if ($fromEntries->isEmpty()) {
            return redirect()->route('schedule.index', ['year' => $request->from_year, 'month' => $request->from_month])
                ->with('error', 'ខែនេះមិនមានទិន្នន័យដើម្បី Copy!');
        }

        $toYear = $request->to_year;
        $toMonth = $request->to_month;
        $toDaysInMonth = Carbon::createFromDate($toYear, $toMonth, 1)->daysInMonth;
        $overwrite = $request->has('overwrite');
        $copied = 0;

        foreach ($fromEntries as $entry) {
            // Skip if day exceeds target month's days
            if ($entry->day > $toDaysInMonth) continue;

            if ($overwrite) {
                ProductionSchedule::updateOrCreate(
                    [
                        'year'    => $toYear,
                        'month'   => $toMonth,
                        'process' => $entry->process,
                        'day'     => $entry->day,
                    ],
                    [
                        'task'  => $entry->task,
                        'note'  => $entry->note,
                        'color' => $entry->color,
                    ]
                );
                $copied++;
            } else {
                $exists = ProductionSchedule::where([
                    'year'    => $toYear,
                    'month'   => $toMonth,
                    'process' => $entry->process,
                    'day'     => $entry->day,
                ])->exists();

                if (!$exists) {
                    ProductionSchedule::create([
                        'year'    => $toYear,
                        'month'   => $toMonth,
                        'process' => $entry->process,
                        'day'     => $entry->day,
                        'task'    => $entry->task,
                        'note'    => $entry->note,
                        'color'   => $entry->color,
                    ]);
                    $copied++;
                }
            }
        }

        $targetMonthName = Carbon::createFromDate($toYear, $toMonth, 1)->format('F Y');

        return redirect()->route('schedule.index', ['year' => $toYear, 'month' => $toMonth])
            ->with('success', "បាន Copy {$copied} កិច្ចការទៅ {$targetMonthName}!");
    }

    /**
     * Clear all schedule entries for a month.
     */
    public function clearMonth(Request $request)
    {
        $request->validate([
            'year'  => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $deleted = ProductionSchedule::where('year', $request->year)
            ->where('month', $request->month)
            ->delete();

        return redirect()->route('schedule.index', ['year' => $request->year, 'month' => $request->month])
            ->with('success', "បានជម្រះ {$deleted} កិច្ចការសម្រាប់ខែនេះ!");
    }

    /**
     * Handle Urgent Task — insert into grid and shift displaced tasks forward.
     *
     * Supports two modes:
     *   mode=new    → brand-new urgent work inserted; existing planned tasks on those days pushed forward
     *   mode=existing → an already-planned task on a specific day is made urgent (moved to urgentDay),
     *                   everything between urgentDay and its original day is nudged one day forward
     */
    public function urgentTask(Request $request)
    {
        $request->validate([
            'year'         => 'required|integer',
            'month'        => 'required|integer|min:1|max:12',
            'process'      => 'required|string',
            'urgent_day'   => 'required|integer|min:1|max:31',
            'duration_days'=> 'required|integer|min:1|max:30',
            'task_name'    => 'required|string|max:255',
            'note'         => 'nullable|string|max:255',
            'reason'       => 'nullable|string|max:500',
            'mode'         => 'nullable|in:new,existing',
        ]);

        $year    = (int) $request->year;
        $month   = (int) $request->month;
        $process = $request->process;
        $startDay = (int) $request->urgent_day;
        $duration = (int) $request->duration_days;
        $taskName = $request->task_name;
        $note     = $request->note ?? 'URGENT';
        $reason   = $request->reason ?? 'Urgent task';
        $mode     = $request->input('mode', 'new');
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // Collect days this urgent task will occupy (skip weekends)
        $urgentDays = $this->collectWorkingDays($year, $month, $startDay, $duration);

        if ($mode === 'existing') {
            // The task_name refers to an already-planned task that must move EARLIER to urgentDay.
            // Find where it currently lives:
            $existingEntry = ProductionSchedule::where('year', $year)
                ->where('month', $month)
                ->where('process', $process)
                ->where(function ($q) use ($taskName) {
                    $q->where('task', $taskName)
                      ->orWhere('task', 'like', "%{$taskName}%");
                })
                ->where('day', '>=', $startDay)
                ->orderBy('day')
                ->first();

            if ($existingEntry && $existingEntry->day > $startDay) {
                $origDay = $existingEntry->day;

                // Shift tasks between startDay and origDay-1 forward by 1 working day
                $rangeDays = range($startDay, $origDay - 1);
                foreach (array_reverse($rangeDays) as $d) {
                    $cell = ProductionSchedule::where(['year'=>$year,'month'=>$month,'process'=>$process,'day'=>$d])->first();
                    if ($cell) {
                        $nextDay = $this->nextWorkingDay($year, $month, $d);
                        if ($nextDay <= $daysInMonth) {
                            $displaced = ProductionSchedule::where(['year'=>$year,'month'=>$month,'process'=>$process,'day'=>$nextDay])->first();
                            if ($displaced) {
                                // cascade — merge tasks
                                $displaced->task = trim($displaced->task . ', ' . $cell->task, ', ');
                                $displaced->save();
                            } else {
                                ProductionSchedule::create([
                                    'year'=>$year,'month'=>$month,'process'=>$process,
                                    'day'=>$nextDay,'task'=>$cell->task,
                                    'note'=>$cell->note,'color'=>$cell->color,
                                ]);
                            }
                            // Log the delay
                            ScheduleDelayLog::create([
                                'year'=>$year,'month'=>$month,'process'=>$process,
                                'original_task'=>$cell->task,'original_day'=>$d,
                                'shifted_to_day'=>$nextDay,
                                'reason_type'=>'urgent_task',
                                'reason_detail'=>"Urgent: {$taskName} — {$reason}",
                            ]);
                            $cell->delete();
                        }
                    }
                }

                // Move the existing task to startDay
                $existingEntry->day = $startDay;
                $existingEntry->note = 'URGENT — ' . ($existingEntry->note ?? '');
                $existingEntry->save();

            } else {
                // Already at or before urgentDay — just mark it urgent
                if ($existingEntry) {
                    $existingEntry->note = 'URGENT — ' . ($existingEntry->note ?? '');
                    $existingEntry->save();
                }
            }

            return redirect()->route('schedule.index', ['year'=>$year,'month'=>$month])
                ->with('success', "'{$taskName}' marked urgent on day {$startDay}!");
        }

        // ── MODE: new ─────────────────────────────────────────────────────────
        // 1. Find any tasks currently in the urgent days and shift them forward
        $lastUrgentDay = end($urgentDays);

        // Collect all existing tasks in affected days, shift them beyond urgentDays
        foreach ($urgentDays as $urgentDay) {
            $existing = ProductionSchedule::where('year', $year)
                ->where('month', $month)
                ->where('process', $process)
                ->where('day', $urgentDay)
                ->first();

            if ($existing) {
                // Find next available day after the last urgent day
                $shiftTo = $this->nextWorkingDay($year, $month, $lastUrgentDay);

                // Check if shiftTo day already has a task — if so, merge
                if ($shiftTo <= $daysInMonth) {
                    $targetCell = ProductionSchedule::where(['year'=>$year,'month'=>$month,'process'=>$process,'day'=>$shiftTo])->first();
                    if ($targetCell) {
                        $targetCell->task = trim($targetCell->task . ', ' . $existing->task, ', ');
                        $targetCell->save();
                    } else {
                        ProductionSchedule::create([
                            'year'=>$year,'month'=>$month,'process'=>$process,
                            'day'=>$shiftTo,'task'=>$existing->task,
                            'note'=>$existing->note,'color'=>$existing->color,
                        ]);
                    }

                    ScheduleDelayLog::create([
                        'year'=>$year,'month'=>$month,'process'=>$process,
                        'original_task'=>$existing->task,'original_day'=>$urgentDay,
                        'shifted_to_day'=>$shiftTo,
                        'reason_type'=>'urgent_task',
                        'reason_detail'=>"New urgent task inserted: {$taskName} — {$reason}",
                    ]);
                }

                $existing->delete();
            }
        }

        // 2. Insert the urgent task across its days
        $urgentColor = '#dc2626'; // red for urgent
        foreach ($urgentDays as $idx => $urgentDay) {
            if ($urgentDay > $daysInMonth) break;
            $label = ($duration > 1) ? $taskName . ' (' . ($idx+1) . '/' . $duration . ')' : $taskName;
            ProductionSchedule::updateOrCreate(
                ['year'=>$year,'month'=>$month,'process'=>$process,'day'=>$urgentDay],
                ['task'=>$label,'note'=>$note,'color'=>$urgentColor]
            );
        }

        return redirect()->route('schedule.index', ['year'=>$year,'month'=>$month])
            ->with('success', "Urgent task '{$taskName}' inserted! Displaced tasks shifted forward.");
    }

    /**
     * Handle Machine Downtime — shift ALL tasks on the affected process(es) forward
     * by the number of downtime days, starting from the downtime start day.
     */
    public function machineDowntime(Request $request)
    {
        $request->validate([
            'year'            => 'required|integer',
            'month'           => 'required|integer|min:1|max:12',
            'process'         => 'required|string',
            'downtime_day'    => 'required|integer|min:1|max:31',
            'downtime_days'   => 'required|integer|min:1|max:30',
            'reason'          => 'nullable|string|max:500',
        ]);

        $year         = (int) $request->year;
        $month        = (int) $request->month;
        $process      = $request->process;
        $startDay     = (int) $request->downtime_day;
        $downtimeDays = (int) $request->downtime_days;
        $reason       = $request->reason ?? 'Machine downtime';
        $daysInMonth  = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // Collect the downtime working days (the days that are blocked)
        $blockedDays = $this->collectWorkingDays($year, $month, $startDay, $downtimeDays);
        $lastBlocked = end($blockedDays);

        // Get all scheduled tasks on or after the downtime start day for this process
        // We need to shift them ALL forward by downtimeDays (in working-day terms)
        $affectedCells = ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->where('process', $process)
            ->where('day', '>=', $startDay)
            ->orderBy('day', 'desc') // process in reverse to avoid overwriting
            ->get();

        foreach ($affectedCells as $cell) {
            // Calculate new day: shift forward by downtimeDays working days
            $newDay = $cell->day;
            $shifts = 0;
            while ($shifts < $downtimeDays) {
                $newDay++;
                if ($newDay > $daysInMonth) break;
                $dow = Carbon::createFromDate($year, $month, $newDay)->dayOfWeek;
                if (!in_array($dow, [0, 6])) { // not weekend
                    $shifts++;
                }
            }

            if ($newDay > $daysInMonth) {
                // Log it but can't shift within month — log as "pushed out of month"
                ScheduleDelayLog::create([
                    'year'=>$year,'month'=>$month,'process'=>$process,
                    'original_task'=>$cell->task,'original_day'=>$cell->day,
                    'shifted_to_day'=>$newDay, // will be > daysInMonth, just for record
                    'reason_type'=>'machine_downtime',
                    'reason_detail'=>"Machine downtime {$downtimeDays}d: {$reason}",
                ]);
                // Can't fit in this month — keep at last valid day with a note
                $newDay = $daysInMonth;
            }

            ScheduleDelayLog::create([
                'year'=>$year,'month'=>$month,'process'=>$process,
                'original_task'=>$cell->task,'original_day'=>$cell->day,
                'shifted_to_day'=>$newDay,
                'reason_type'=>'machine_downtime',
                'reason_detail'=>"Machine downtime {$downtimeDays}d: {$reason}",
            ]);

            // Move the cell
            ProductionSchedule::where(['year'=>$year,'month'=>$month,'process'=>$process,'day'=>$cell->day])->delete();
            ProductionSchedule::updateOrCreate(
                ['year'=>$year,'month'=>$month,'process'=>$process,'day'=>$newDay],
                ['task'=>$cell->task,'note'=>$cell->note ? $cell->note . ' (delayed)' : 'delayed by downtime','color'=>$cell->color]
            );
        }

        // Mark the downtime days on the grid
        foreach ($blockedDays as $bd) {
            if ($bd > $daysInMonth) break;
            $existing = ProductionSchedule::where(['year'=>$year,'month'=>$month,'process'=>$process,'day'=>$bd])->first();
            if (!$existing) {
                ProductionSchedule::create([
                    'year'=>$year,'month'=>$month,'process'=>$process,
                    'day'=>$bd,'task'=>'🔧 DOWNTIME','note'=>$reason,'color'=>'#78350f',
                ]);
            }
        }

        return redirect()->route('schedule.index', ['year'=>$year,'month'=>$month])
            ->with('success', "Machine downtime logged! {$affectedCells->count()} tasks shifted forward by {$downtimeDays} working day(s).");
    }

    /**
     * Show delay log for a month (for reporting).
     */
    public function delayReport(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $logs = ScheduleDelayLog::where('year', $year)
            ->where('month', $month)
            ->orderBy('original_day')
            ->get();

        return view('schedule.delay-report', compact('year', 'month', 'logs'));
    }

    /**
     * Collect N consecutive working days (Mon–Fri) starting from startDay.
     */
    private function collectWorkingDays(int $year, int $month, int $startDay, int $count): array
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $days = [];
        $d = $startDay;
        $collected = 0;
        while ($collected < $count && $d <= $daysInMonth) {
            $dow = Carbon::createFromDate($year, $month, $d)->dayOfWeek;
            if (!in_array($dow, [0, 6])) {
                $days[] = $d;
                $collected++;
            }
            $d++;
        }
        return $days;
    }

    /**
     * Get next working day after the given day.
     */
    private function nextWorkingDay(int $year, int $month, int $day): int
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $d = $day + 1;
        while ($d <= $daysInMonth) {
            $dow = Carbon::createFromDate($year, $month, $d)->dayOfWeek;
            if (!in_array($dow, [0, 6])) return $d;
            $d++;
        }
        return $d; // may exceed month — caller must check
    }

    /**
     * Emoji for each process.
     */
    private function processEmoji(string $process): string
    {
        return match ($process) {
            'Design'    => '🎨',
            'Press'     => '🖨️',
            'Folding'   => '📐',
            'Gathering' => '📚',
            'Staple'    => '📎',
            'Binding'   => '📖',
            'Cutting'   => '✂️',
            'Packaging' => '📦',
            'Delivery'  => '🚚',
            default     => '📌',
        };
    }
}
