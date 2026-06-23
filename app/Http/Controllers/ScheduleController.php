<?php

namespace App\Http\Controllers;

use App\Models\ProductionSchedule;
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

        ProductionSchedule::updateOrCreate(
            [
                'year'    => $request->year,
                'month'   => $request->month,
                'process' => $request->process,
                'day'     => $request->day,
            ],
            [
                'task'  => $request->task,
                'note'  => $request->note,
                'color' => $request->color,
            ]
        );

        return redirect()->route('schedule.index', ['year' => $request->year, 'month' => $request->month])
            ->with('success', 'រក្សាទុកបានជោគជ័យ!');
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
