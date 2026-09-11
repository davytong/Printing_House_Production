<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Machine;
use App\Models\PrintRequest;
use App\Models\ProductionActual;
use App\Models\ProductionJob;
use App\Models\ProductionProcess;
use App\Models\ProductionSchedule;
use App\Models\ProductionTask;
use App\Models\ProductionTaskProgress;
use App\Models\ProductionTemplate;
use App\Models\ProductionTemplateProcess;
use App\Models\ScheduleAudit;
use App\Models\ScheduleDelayLog;
use App\Services\ProductionPlanningService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ScheduleController extends Controller
{
    public function __construct(
        protected ProductionPlanningService $planningService,
        protected ?TelegramService $telegramService = null
    ) {}

    /**
     * Default processes in order.
     */
    private array $processes = [
        'Design',
        'Press',
        'Digital',
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
            ->get()
            ->sortBy(function($task) {
                return array_search($task->process, $this->processes);
            });

        // Tomorrow's tasks for upcoming alert
        $tomorrow = now()->addDay();
        $tomorrowTasks = ProductionSchedule::where('year', $tomorrow->year)
            ->where('month', $tomorrow->month)
            ->where('day', $tomorrow->day)
            ->get()
            ->sortBy(function($task) {
                return array_search($task->process, $this->processes);
            });

        // Progress: how many days have tasks vs total days in month
        $totalCells = $daysInMonth * count($this->processes);
        $filledCells = ProductionSchedule::where('year', $year)->where('month', $month)->count();
        $progress = $totalCells > 0 ? round(($filledCells / $totalCells) * 100, 1) : 0;

        // Smart Planning Resources
        $templates = ProductionTemplate::with('processes')->where('is_active', true)->get();
        $machines = Machine::where('status', '!=', 'retired')->get();
        $batches = \App\Models\ProductionBatch::with(['books' => function($q) { $q->ordered(); }])->orderBy('id', 'desc')->get();
        $currentBatch = \App\Models\ProductionBatch::current();
        $books = Book::with('batch')->ordered()->get();
        $approvedRequests = PrintRequest::where('status', 'approved')->with('items.book')->latest()->get();
        $activeJobs = ProductionJob::with(['schedules', 'template'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get();

        $batchesData = $batches->map(function($b) {
            return [
                'id'          => $b->id,
                'name'        => $b->name,
                'status'      => $b->status,
                'books_count' => $b->books->count(),
                'books'       => $b->books->map(function($bk) {
                    return [
                        'id'              => $bk->id,
                        'batch_id'        => $bk->batch_id,
                        'title'           => $bk->title,
                        'grade'           => $bk->grade,
                        'category'        => $bk->category,
                        'printing_method' => $bk->printing_method,
                        'target_qty'      => (int)$bk->target_qty,
                        'total_printed'   => (int)$bk->total_printed,
                        'remaining'       => max(0, (int)$bk->target_qty - (int)$bk->total_printed),
                        'is_completed'    => (int)$bk->target_qty > 0 && (int)$bk->total_printed >= (int)$bk->target_qty,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
        $batchesJson = json_encode($batchesData);

        // Delay Detection Alerts
        $delayAlerts = collect();
        if (Schema::hasColumn('production_schedules', 'planned_qty')) {
            $delayAlerts = ProductionSchedule::with(['job', 'machine'])
                ->where('year', $year)
                ->where('month', $month)
                ->whereNotNull('planned_qty')
                ->where('planned_qty', '>', 0)
                ->where('day', '<', now()->day)
                ->whereColumn('actual_qty', '<', 'planned_qty')
                ->get();
        }

        $processesList = ProductionProcess::where('is_active', true)->orderBy('sequence')->get();

        return view('schedule.index', [
            'year'             => $year,
            'month'            => $month,
            'daysInMonth'      => $daysInMonth,
            'schedules'        => $schedules,
            'processes'        => $this->processes,
            'processesList'    => $processesList,
            'todayTasks'       => $todayTasks,
            'tomorrowTasks'    => $tomorrowTasks,
            'progress'         => $progress,
            'filledCells'      => $filledCells,
            'totalCells'       => $totalCells,
            'templates'        => $templates,
            'machines'         => $machines,
            'batches'          => $batches,
            'batchesJson'      => $batchesJson,
            'currentBatch'     => $currentBatch,
            'books'            => $books,
            'approvedRequests' => $approvedRequests,
            'activeJobs'       => $activeJobs,
            'delayAlerts'      => $delayAlerts,
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
            'task'    => 'nullable|string|max:500',
            'note'    => 'nullable|string|max:255',
            'color'   => 'nullable|string|max:30',
            'status'  => 'nullable|in:planned,in_progress,done',
            'copy_processes'   => 'nullable|array',
            'copy_processes.*' => 'string',
            'include_sundays'  => 'nullable|boolean',
        ]);

        $includeSundays = $request->boolean('include_sundays');

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

        $parser = app(\App\Services\ScheduleParserService::class);
        $parsedData = $parser->parseTasks(
            $request->task ?? '',
            (int)$request->year,
            (int)$request->month,
            (int)$request->day,
            $includeSundays
        );
        
        $cellsToCreate = $parsedData['cells'];
        $startDay = $parsedData['startDay'];
        $tasks = $parsedData['tasks'];
        $saved = 0;

        // Clear any existing cells for this process starting from the input day
        // This ensures we replace old data when editing
        $affectedDays = array_keys($cellsToCreate);
        if (!empty($affectedDays)) {
            ProductionSchedule::where([
                'year'    => $request->year,
                'month'   => $request->month,
                'process' => $request->process,
            ])
            ->whereIn('day', $affectedDays)
            ->delete();
        }

        // Save all cells
        $insertData = [];
        $now = now();
        foreach ($cellsToCreate as $targetDay => $taskLabels) {
            $insertData[] = [
                'year'       => $request->year,
                'month'      => $request->month,
                'process'    => $request->process,
                'day'        => $targetDay,
                'task'       => implode(', ', $taskLabels),
                'note'       => $request->note,
                'color'      => $request->color,
                'status'     => $request->input('status', 'planned'),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $saved++;
        }

        if (!empty($insertData)) {
            ProductionSchedule::insert($insertData);
        }

        // Copy/Append to other processes safely
        if (!empty($request->copy_processes)) {
            $copy1Day = $request->has('copy_1_day');
            
            foreach ($request->copy_processes as $copyProcess) {
                if ($copy1Day) {
                    // Extract only the base task names (strip duration/children metadata)
                    $baseNames = [];
                    foreach ($tasks as $taskStr) {
                        if (empty(trim($taskStr))) continue;
                        $taskName = trim(preg_replace('/\[\d+d\]|\{[^}]+\}/', '', $taskStr));
                        if (!empty($taskName)) {
                            $baseNames[] = $taskName;
                        }
                    }
                    
                    if (!empty($baseNames)) {
                        $existing = ProductionSchedule::where([
                            'year'    => $request->year,
                            'month'   => $request->month,
                            'process' => $copyProcess,
                            'day'     => $startDay, // Only copy to the very first day!
                        ])->first();
                        
                        $newTasksStr = implode(', ', $baseNames);
                        
                        if ($existing) {
                            $currentTasks = array_filter(array_map('trim', explode(',', $existing->task)));
                            foreach ($baseNames as $bn) {
                                if (!in_array($bn, $currentTasks)) {
                                    $currentTasks[] = $bn;
                                }
                            }
                            $existing->update(['task' => implode(', ', $currentTasks)]);
                        } else {
                            ProductionSchedule::create([
                                'year'    => $request->year,
                                'month'   => $request->month,
                                'process' => $copyProcess,
                                'day'     => $startDay, // Only 1 day!
                                'task'    => $newTasksStr,
                                'note'    => null,
                                'color'   => $request->color,
                                'status'  => 'planned',
                            ]);
                        }
                    }
                } else {
                    // Exact Clone (Spans multiple days, keeps [2d] string)
                    foreach ($cellsToCreate as $targetDay => $taskLabels) {
                        $existing = ProductionSchedule::where([
                            'year'    => $request->year,
                            'month'   => $request->month,
                            'process' => $copyProcess,
                            'day'     => $targetDay,
                        ])->first();

                        $newTasksStr = implode(', ', $taskLabels);

                        if ($existing) {
                            // Append to existing tasks safely
                            $currentTasks = array_filter(array_map('trim', explode(',', $existing->task)));
                            // Don't append if it perfectly matches (prevents double copying by accident)
                            if (!in_array($newTasksStr, $currentTasks)) {
                                $currentTasks[] = $newTasksStr;
                                $existing->update(['task' => implode(', ', $currentTasks)]);
                            }
                        } else {
                            // Create new entry
                            ProductionSchedule::create([
                                'year'    => $request->year,
                                'month'   => $request->month,
                                'process' => $copyProcess,
                                'day'     => $targetDay,
                                'task'    => $newTasksStr,
                                'note'    => null, // Don't copy specific notes
                                'color'   => $request->color, // Do copy the color tag
                                'status'  => 'planned',
                            ]);
                        }
                    }
                }
            }
        }

        $msg = $saved > 1 ? "បានរក្សាទុក {$saved} ថ្ងៃ (ឈប់សម្រាកថ្ងៃអាទិត្យ)" : 'រក្សាទុកបានជោគជ័យ!';
        return redirect()->route('schedule.index', ['year' => $request->year, 'month' => $request->month])
            ->with('success', $msg);
    }

    /**
     * Move an entire cell's tasks to another day/process without giving a reason.
     */
    public function moveCell(Request $request)
    {
        $request->validate([
            'year'         => 'required|integer',
            'month'        => 'required|integer',
            'from_process' => 'required|string',
            'from_day'     => 'required|integer',
            'to_process'   => 'required|string',
            'to_day'       => 'required|integer',
        ]);

        $original = ProductionSchedule::where([
            'year'    => $request->year,
            'month'   => $request->month,
            'process' => $request->from_process,
            'day'     => $request->from_day,
        ])->first();

        if (!$original) {
            return back()->with('error', 'រកមិនឃើញទិន្នន័យដើម (Original data not found)');
        }

        $existing = ProductionSchedule::where([
            'year'    => $request->year,
            'month'   => $request->month,
            'process' => $request->to_process,
            'day'     => $request->to_day,
        ])->first();

        if ($existing) {
            // Merge tasks if destination already has tasks
            $currentTasks = array_filter(array_map('trim', explode(',', $existing->task)));
            $newTasks = array_filter(array_map('trim', explode(',', $original->task)));
            foreach ($newTasks as $nt) {
                if (!in_array($nt, $currentTasks)) {
                    $currentTasks[] = $nt;
                }
            }
            $existing->update([
                'task' => implode(', ', $currentTasks),
            ]);
            $original->delete();
        } else {
            // Just update the original to the new cell
            $original->update([
                'process' => $request->to_process,
                'day'     => $request->to_day,
            ]);
        }

        return back()->with('success', 'បានផ្លាស់ប្តូរដោយជោគជ័យ! (Moved successfully)');
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

        $upsertData = [];
        $deleteConditions = [];
        $now = now();

        foreach ($request->cells as $cell) {
            if (empty($cell['task']) && empty($cell['note'])) {
                $deleteConditions[] = [
                    'process' => $cell['process'],
                    'day'     => $cell['day'],
                ];
            } else {
                $upsertData[] = [
                    'year'       => $year,
                    'month'      => $month,
                    'process'    => $cell['process'],
                    'day'        => $cell['day'],
                    'task'       => $cell['task'] ?? null,
                    'note'       => $cell['note'] ?? null,
                    'color'      => $cell['color'] ?? null,
                    'status'     => 'planned',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Process deletions in bulk
        if (!empty($deleteConditions)) {
            $query = ProductionSchedule::where('year', $year)->where('month', $month);
            $query->where(function ($q) use ($deleteConditions) {
                foreach ($deleteConditions as $cond) {
                    $q->orWhere(function ($sub) use ($cond) {
                        $sub->where('process', $cond['process'])
                            ->where('day', $cond['day']);
                    });
                }
            });
            $query->delete();
        }

        // Process upserts in bulk
        if (!empty($upsertData)) {
            ProductionSchedule::upsert(
                $upsertData,
                ['year', 'month', 'process', 'day'],
                ['task', 'note', 'color', 'updated_at']
            );
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
            ->get()
            ->sortBy(fn($task) => [$task->day, array_search($task->process, $this->processes)])
            ->values();

        return view('schedule.export-calendar', [
            'year'      => $year,
            'month'     => $month,
            'entries'   => $entries,
            'processes' => $this->processes,
        ]);
    }

    /**
     * Send calendar image export to Telegram.
     */
    public function sendExportTelegram(Request $request, TelegramService $telegramService)
    {
        $request->validate([
            'image' => 'required|string',
            'monthName' => 'required|string',
            'group_id' => 'nullable|string'
        ]);

        $base64Image = $request->input('image');
        $monthName = $request->input('monthName');
        $groupId = $request->input('group_id', 'all');

        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]);
            
            if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                return response()->json(['error' => 'Invalid image type'], 400);
            }
            
            $base64Image = str_replace(' ', '+', $base64Image);
            $imageData = base64_decode($base64Image);
        } else {
            return response()->json(['error' => 'Invalid image format'], 400);
        }

        $filename = 'monthly_calendar_' . uniqid() . '.' . $type;
        $path = 'temp/' . $filename;
        
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);

        $caption = "📅 Production Schedule — {$monthName}";
        
        if ($groupId === 'all') {
            $sent = $telegramService->broadcastPhoto($path, $caption);
        } else {
            $group = \App\Models\TelegramGroup::find($groupId);
            if ($group) {
                $sent = $telegramService->sendPhoto($group->chat_id, $path, $caption, $group->message_thread_id) ? 1 : 0;
            } else {
                $sent = 0;
            }
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);

        if ($sent > 0) {
            return response()->json(['success' => true, 'message' => "Sent to {$sent} groups."]);
        }

        return response()->json(['success' => false, 'error' => 'Failed to send to Telegram'], 500);
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
            ->get()
            ->sortBy(fn($task) => array_search($task->process, $this->processes))
            ->values();

        if ($tasks->isEmpty()) {
            return redirect()->route('schedule.index', ['year' => $year, 'month' => $month])
                ->with('error', 'មិនមានកិច្ចការសម្រាប់ថ្ងៃនេះ!');
        }

        $dayName = Carbon::createFromDate($year, $month, $day)->locale('km')->dayName;
        $monthNameKm = Carbon::createFromDate($year, $month, 1)->locale('km')->translatedFormat('F');
        
        $targetDate = Carbon::createFromDate($year, $month, $day)->startOfDay();
        $today = now()->startOfDay();
        $relativeDay = '';
        if ($targetDate->equalTo($today)) {
            $relativeDay = ' (ថ្ងៃនេះ)';
        } elseif ($targetDate->equalTo($today->copy()->addDay())) {
            $relativeDay = ' (ថ្ងៃស្អែក)';
        }

        $message = "<b>📅 កាលវិភាគផលិតកម្ម</b>\n";
        $message .= " ថ្ងៃ {$dayName} ទី {$day} ខែ {$monthNameKm} ឆ្នាំ {$year}{$relativeDay}\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($tasks as $task) {
            $emoji = $this->processEmoji($task->process);
            $cleanTask = preg_replace('/\s*\(\d+\/\d+\)\s*(URGENT)?/', '', $task->task);
            $cleanTask = str_replace(' (URGENT)', '', $cleanTask);
            $cleanTask = str_replace(' URGENT', '', $cleanTask);
            $isUrgent = str_contains(strtoupper($task->task), 'URGENT');
            $isDowntime = str_starts_with($task->task, '🔧');
            
            $taskLabel = $cleanTask;
            if ($isUrgent) {
                $taskLabel = "🔴 <u>" . $taskLabel . " [បន្ទាន់]</u>";
            }
            if ($isDowntime) {
                $taskLabel = "<b>" . $taskLabel . "</b>";
            }

            $message .= "{$emoji} <b>{$task->process}:</b> {$taskLabel}";
            if ($task->note) {
                $message .= " <i>({$task->note})</i>";
            }
            $message .= "\n";
        }

        $message .= "\n━━━━━━━━━━━━━━━━━━\n";
        $message .= "📊 <b>សរុប:</b> {$tasks->count()} ដំណើរការ";

        $telegram = new TelegramService();
        $sent = 0;

        if ($request->group_id === 'all') {
            // Send to all groups
            $sent = $telegram->broadcastMessage($message, 'HTML');
        } else {
            // Send to specific group
            $group = \App\Models\TelegramGroup::find($request->group_id);
            if ($group) {
                $success = $telegram->sendMessage($group->chat_id, $message, $group->message_thread_id, 'HTML');
                if ($success) $sent = 1;
            }
        }

        if ($sent > 0) {
            return redirect()->route('schedule.index', ['year' => $year, 'month' => $month])
                ->with('success', "បានផ្ញើការជូនដំណឹង ថ្ងៃ {$targetDate->format('d/m/Y')} ទៅ {$sent} group(s)!");
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
            // ── Find ALL cells containing this task (it may span multiple days) ──
            // e.g. "Listening Textbook" might appear on day 28 AND day 29 if span=2
            $sourceCells = ProductionSchedule::where('year', $year)
                ->where('month', $month)
                ->where('process', $process)
                ->where('task', 'like', "%{$taskName}%")
                ->where('day', '>=', $startDay)
                ->orderBy('day')
                ->get();

            if ($sourceCells->isEmpty()) {
                return redirect()->route('schedule.index', ['year'=>$year,'month'=>$month])
                    ->with('error', "Task '{$taskName}' not found on or after day {$startDay} for {$process}.");
            }

            $firstSourceDay = $sourceCells->first()->day;

            // Auto-detect span: how many consecutive cells contain this task
            $detectedSpan = $sourceCells->count();
            // Use the user-supplied duration if given, otherwise use detected span
            $effectiveDuration = max($duration, $detectedSpan);

            // Target days where the urgent task will be placed
            $targetDays = $this->collectWorkingDays($year, $month, $startDay, $effectiveDuration);

            if ($firstSourceDay <= $startDay) {
                // Task is already at or before the target day — just mark it urgent in place
                foreach ($sourceCells as $cell) {
                    $tasks = array_map('trim', explode(',', $cell->task));
                    $tasks = array_map(fn($t) =>
                        (strcasecmp(trim($t), $taskName) === 0 ||
                         strcasecmp(trim($t), $taskName . ' (URGENT)') === 0)
                            ? $taskName . ' (URGENT)' : $t,
                        $tasks
                    );
                    $cell->task = implode(', ', $tasks);
                    $cell->color = '#dc2626';
                    $cell->save();
                }
                return redirect()->route('schedule.index', ['year'=>$year,'month'=>$month])
                    ->with('success', "'{$taskName}' marked as urgent in place.");
            }

            // ── STEP 1: Remove this task from ALL its source cells ─────────────
            foreach ($sourceCells as $cell) {
                $remaining = array_values(array_filter(
                    array_map('trim', explode(',', $cell->task)),
                    fn($t) => strcasecmp(trim($t), $taskName) !== 0
                        && strcasecmp(trim($t), $taskName . ' (URGENT)') !== 0
                ));
                if (empty($remaining)) {
                    $cell->delete();
                } else {
                    $cell->task = implode(', ', $remaining);
                    $cell->save();
                }
                // Log each moved day
                ScheduleDelayLog::create([
                    'year'         => $year,
                    'month'        => $month,
                    'process'      => $process,
                    'original_task'=> $taskName,
                    'original_day' => $cell->day,
                    'shifted_to_day' => $startDay,
                    'reason_type'  => 'urgent_task',
                    'reason_detail'=> "Made urgent — moved from day {$cell->day} to day {$startDay}: {$reason}",
                ]);
            }

            // ── STEP 2: Place the urgent task on target days (one cell per day) ─
            foreach ($targetDays as $idx => $targetDay) {
                if ($targetDay > $daysInMonth) break;
                $label = ($effectiveDuration > 1)
                    ? $taskName . ' (' . ($idx + 1) . '/' . $effectiveDuration . ') URGENT'
                    : $taskName . ' (URGENT)';

                $targetCell = ProductionSchedule::where([
                    'year'=>$year,'month'=>$month,'process'=>$process,'day'=>$targetDay
                ])->first();

                if ($targetCell) {
                    // Prepend urgent task to whatever is already there
                    $existing = array_map('trim', explode(',', $targetCell->task));
                    array_unshift($existing, $label);
                    $targetCell->task  = implode(', ', $existing);
                    $targetCell->color = '#dc2626';
                    $targetCell->save();
                } else {
                    ProductionSchedule::create([
                        'year'=>$year,'month'=>$month,'process'=>$process,
                        'day'=>$targetDay,'task'=>$label,
                        'note'=>'URGENT','color'=>'#dc2626',
                    ]);
                }
            }

            $daysWord = $effectiveDuration > 1 ? "{$effectiveDuration} days" : "day {$startDay}";
            return redirect()->route('schedule.index', ['year'=>$year,'month'=>$month])
                ->with('success', "'{$taskName}' moved to {$daysWord} as urgent. Other tasks on original day(s) unchanged.");
        }

        // ── MODE: new ─────────────────────────────────────────────────────────
        // A brand-new urgent job is inserted. Any tasks on the blocked days are
        // pushed to the next working day AFTER the urgent block ends.
        // Key rule: only the tasks that occupy the SAME slot get shifted;
        // if a cell has multiple tasks, ALL of them shift together because
        // the whole production day for that process is blocked.
        $lastUrgentDay = end($urgentDays);

        // Pre-fetch existing cells for the blocked days
        $existingCells = ProductionSchedule::where([
            'year'=>$year,'month'=>$month,'process'=>$process
        ])->whereIn('day', $urgentDays)->get()->keyBy('day');

        foreach ($urgentDays as $urgentDay) {
            $existing = $existingCells->get($urgentDay);

            if ($existing) {
                $shiftTo = $this->nextWorkingDay($year, $month, $lastUrgentDay);

                if ($shiftTo <= $daysInMonth) {
                    $targetCell = ProductionSchedule::where([
                        'year'=>$year,'month'=>$month,'process'=>$process,'day'=>$shiftTo
                    ])->first();

                    if ($targetCell) {
                        // Append displaced tasks — avoid duplicating tasks already there
                        $existingTasks = array_map('trim', explode(',', $existing->task));
                        $targetTasks   = array_map('trim', explode(',', $targetCell->task));
                        $merged = array_unique(array_merge($targetTasks, $existingTasks));
                        $targetCell->task = implode(', ', $merged);
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
                        'reason_detail'=>"Displaced by new urgent task: {$taskName} — {$reason}",
                    ]);
                }

                $existing->delete();
            }
        }

        // Insert the urgent task across its days (red)
        $urgentColor = '#dc2626';
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
    public function machineDowntime(Request $request, TelegramService $telegramService)
    {
        $request->validate([
            'year'            => 'required|integer',
            'month'           => 'required|integer|min:1|max:12',
            'process'         => 'required|string',
            'downtime_day'    => 'required|integer|min:1|max:31',
            'downtime_days'   => 'required|numeric|min:0.5|max:30',
            'reason'          => 'nullable|string|max:500',
            'strategy'        => 'nullable|in:shift_workflow,shift_single',
            'machine_id'      => 'nullable|integer',
        ]);

        $year         = (int) $request->year;
        $month        = (int) $request->month;
        $process      = $request->process;
        $startDay     = (int) $request->downtime_day;
        $downtimeDays = (float) $request->downtime_days;
        $reason       = $request->reason ?? 'Machine downtime';
        $strategy     = $request->input('strategy', 'shift_workflow');
        $machineId    = $request->input('machine_id');
        $daysInMonth  = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // Collect the downtime working days (the days that are blocked)
        $blockedDays = $this->collectWorkingDays($year, $month, $startDay, $downtimeDays);
        $lastBlocked = end($blockedDays);

        if (empty($blockedDays)) {
            return redirect()->route('schedule.index', ['year'=>$year,'month'=>$month])
                ->with('error', 'No working days affected by this downtime.');
        }

        // Record machine downtime if machine is specified
        if ($machineId) {
            $startDate = Carbon::createFromDate($year, $month, $startDay)->startOfDay();
            \App\Models\MachineDowntime::create([
                'machine_id'     => $machineId,
                'start_time'     => $startDate,
                'duration_hours' => $downtimeDays * 8, // assuming 8 hours per working day
                'reason'         => $reason,
                'resolved'       => false,
            ]);
            // Update machine status
            $machine = \App\Models\Machine::find($machineId);
            if ($machine) {
                $machine->update(['status' => 'breakdown']);
            }
        }

        $shiftedTasksSet = []; // Keep track of base task names that were shifted
        $totalAffectedTasks = 0;

        // Shift primary process
        $affectedCells = ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->where('process', $process)
            ->where('day', '>=', $startDay)
            ->orderBy('day', 'desc')
            ->get();

        foreach ($affectedCells as $cell) {
            $totalAffectedTasks++;
            
            // Collect task names for workflow shifting
            $cellTasks = array_filter(array_map('trim', explode(',', $cell->task)));
            foreach ($cellTasks as $t) {
                if ($t && !str_starts_with($t, '🔧')) {
                    $clean = preg_replace('/\[\d+d\]|\{[^}]+\}|\(\d+\/\d+\)/', '', $t);
                    $clean = trim(str_replace(['(URGENT)', 'URGENT'], '', $clean));
                    if ($clean) {
                        $shiftedTasksSet[] = $clean;
                    }
                }
            }

            // Shift forward
            $newDay = $this->shiftDayForward($year, $month, $cell->day, $downtimeDays, $daysInMonth);
            $this->moveScheduleCell($cell, $newDay, $year, $month, $process, $daysInMonth, $downtimeDays, $reason, 'machine_downtime');
        }

        // Mark the downtime days on the grid for the primary process
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

        // Shift downstream processes if strategy is shift_workflow
        if ($strategy === 'shift_workflow' && !empty($shiftedTasksSet)) {
            $shiftedTasksSet = array_unique($shiftedTasksSet);
            $processIndex = array_search($process, $this->processes);
            
            if ($processIndex !== false) {
                $downstreamProcesses = array_slice($this->processes, $processIndex + 1);
                
                foreach ($downstreamProcesses as $dsProcess) {
                    // Find cells in downstream processes that contain the shifted tasks
                    $dsCells = ProductionSchedule::where('year', $year)
                        ->where('month', $month)
                        ->where('process', $dsProcess)
                        ->where('day', '>=', $startDay)
                        ->orderBy('day', 'desc')
                        ->get();
                        
                    foreach ($dsCells as $dsCell) {
                        $cellTasks = array_filter(array_map('trim', explode(',', $dsCell->task)));
                        $hasMatch = false;
                        foreach ($cellTasks as $t) {
                            $clean = preg_replace('/\[\d+d\]|\{[^}]+\}|\(\d+\/\d+\)/', '', $t);
                            $clean = trim(str_replace(['(URGENT)', 'URGENT'], '', $clean));
                            if (in_array($clean, $shiftedTasksSet)) {
                                $hasMatch = true;
                                break;
                            }
                        }
                        
                        if ($hasMatch) {
                            $totalAffectedTasks++;
                            $newDay = $this->shiftDayForward($year, $month, $dsCell->day, $downtimeDays, $daysInMonth);
                            $this->moveScheduleCell($dsCell, $newDay, $year, $month, $dsProcess, $daysInMonth, $downtimeDays, 'Cascade from ' . $process . ' delay', 'machine_downtime');
                        }
                    }
                }
            }
        }

        // Send Telegram Notification
        $machineName = $machineId ? (\App\Models\Machine::find($machineId)?->name ?? 'Unknown Machine') : $process;
        $downtimeDate = Carbon::createFromDate($year, $month, $startDay)->format('d/m/Y');
        $telegramMessage = "🚨 <b>MACHINE DOWNTIME ALERT</b> 🚨\n\n" .
                           "<b>Machine/Process:</b> {$machineName} ({$process})\n" .
                           "<b>Date:</b> {$downtimeDate}\n" .
                           "<b>Duration:</b> {$downtimeDays} working day(s)\n" .
                           "<b>Reason:</b> {$reason}\n\n" .
                           "<b>Impact:</b> {$totalAffectedTasks} schedule(s) affected.\n" .
                           ($strategy === 'shift_workflow' ? "🔄 <i>Workflow Cascade Rescheduling Applied</i>" : "⚠️ <i>Only {$process} shifted</i>");
        
        $mainGroup = \App\Models\TelegramGroup::whereNull('topic_name')->first();
        if ($mainGroup) {
            $telegramService->sendMessage($mainGroup->chat_id, $telegramMessage, $mainGroup->message_thread_id, 'HTML');
        }

        // Conflict Detection
        $conflictCount = 0;
        $monthCells = ProductionSchedule::where('year', $year)->where('month', $month)->get();
        foreach ($monthCells as $c) {
            $tasksInCell = array_filter(array_map('trim', explode(',', $c->task)));
            // Avoid counting downtime cells as conflicts
            $isDowntimeOnly = count($tasksInCell) === 1 && str_starts_with(reset($tasksInCell), '🔧');
            if (count($tasksInCell) > 3 && !$isDowntimeOnly) {
                $conflictCount++;
            }
        }

        $successMsg = "Machine downtime logged! {$totalAffectedTasks} tasks shifted forward by {$downtimeDays} working day(s).";
        if ($conflictCount > 0) {
            $successMsg .= " ⚠️ Warning: Possible machine overload detected ({$conflictCount} days have > 3 tasks).";
        }

        return redirect()->route('schedule.index', ['year'=>$year,'month'=>$month])
            ->with('success', $successMsg);
    }

    private function shiftDayForward($year, $month, $currentDay, float $daysToShift, $daysInMonth)
    {
        $newDay = $currentDay;
        $shifts = 0;
        while ($shifts < $daysToShift) {
            $newDay++;
            if ($newDay > $daysInMonth) break;
            $dow = Carbon::createFromDate($year, $month, $newDay)->dayOfWeek;
            if ($dow !== 0) { // Skip Sunday only
                $shifts++;
            }
        }
        return $newDay;
    }

    private function moveScheduleCell($cell, $newDay, $year, $month, $process, $daysInMonth, $downtimeDays, $reason, $reasonType)
    {
        $loggedDay = min($newDay, $daysInMonth);
        $overflow  = $newDay > $daysInMonth;

        ScheduleDelayLog::create([
            'year'         => $year,
            'month'        => $month,
            'process'      => $process,
            'original_task'=> $cell->task,
            'original_day' => $cell->day,
            'shifted_to_day'=> $loggedDay,
            'reason_type'  => $reasonType,
            'reason_detail'=> "Shifted {$downtimeDays}d: {$reason}" . ($overflow ? " [overflows to next month]" : ""),
        ]);

        ProductionSchedule::where([
            'year'=>$year,'month'=>$month,'process'=>$process,'day'=>$cell->day
        ])->delete();

        if ($newDay <= $daysInMonth) {
            $targetCell = ProductionSchedule::where([
                'year'=>$year,'month'=>$month,'process'=>$process,'day'=>$newDay
            ])->first();

            if ($targetCell) {
                $existingTasks = array_map('trim', explode(',', $targetCell->task));
                $incomingTasks = array_map('trim', explode(',', $cell->task));
                $merged = array_unique(array_merge($existingTasks, $incomingTasks));
                $targetCell->task = implode(', ', $merged);
                $targetCell->save();
            } else {
                ProductionSchedule::create([
                    'year'    => $year,
                    'month'   => $month,
                    'process' => $process,
                    'day'     => $newDay,
                    'task'    => $cell->task,
                    'note'    => ($cell->note ? $cell->note . ' — ' : '') . 'delayed: ' . $reason,
                    'color'   => $cell->color,
                ]);
            }
        }
    }

    /**
     * Quick-update the status of a single cell (AJAX).
     * POST /schedule/status  { year, month, process, day, status }
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'year'    => 'required|integer',
            'month'   => 'required|integer|min:1|max:12',
            'process' => 'required|string',
            'day'     => 'required|integer|min:1|max:31',
            'status'  => 'required|in:planned,in_progress,done',
        ]);

        $cell = ProductionSchedule::where([
            'year'    => $request->year,
            'month'   => $request->month,
            'process' => $request->process,
            'day'     => $request->day,
        ])->first();

        if (!$cell) {
            return response()->json(['ok' => false, 'message' => 'Cell not found'], 404);
        }

        $cell->status = $request->status;
        $cell->save();

        return response()->json(['ok' => true, 'status' => $cell->status]);
    }

    /**
     * Return delay log data as JSON for inline modal.
     */
    public function delayReportJson(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $logs = ScheduleDelayLog::where('year', $year)
            ->where('month', $month)
            ->orderBy('original_day')
            ->get();

        $allCells = ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->whereNotNull('task')
            ->get()
            ->sort(function($a, $b) {
                if ($a->day !== $b->day) {
                    return $a->day <=> $b->day;
                }
                return array_search($a->process, $this->processes) <=> array_search($b->process, $this->processes);
            })->values();

        // Process summary
        $processSummary = [];
        foreach ($allCells as $cell) {
            $proc = $cell->process;
            if (!isset($processSummary[$proc])) {
                $processSummary[$proc] = ['days' => 0, 'tasks' => []];
            }
            $processSummary[$proc]['days']++;
            foreach (array_map('trim', explode(',', $cell->task)) as $t) {
                if ($t && !str_starts_with($t, '🔧')) {
                    $clean = preg_replace('/\s*\(\d+\/\d+\)\s*(URGENT)?/', '', $t);
                    $clean = str_replace([' (URGENT)', ' URGENT'], '', $clean);
                    $processSummary[$proc]['tasks'][$clean] = ($processSummary[$proc]['tasks'][$clean] ?? 0) + 1;
                }
            }
        }

        // Reorder processSummary according to standard process list
        $sortedProcessSummary = [];
        foreach ($this->processes as $proc) {
            if (isset($processSummary[$proc])) {
                $sortedProcessSummary[$proc] = $processSummary[$proc];
            }
        }
        $processSummary = $sortedProcessSummary;

        return response()->json([
            'ok'             => true,
            'year'           => $year,
            'month'          => $month,
            'logs'           => $logs,
            'allCells'       => $allCells,
            'processSummary' => $processSummary,
            'stats' => [
                'total'     => $allCells->count(),
                'urgent'    => $logs->where('reason_type', 'urgent_task')->count(),
                'downtime'  => $logs->where('reason_type', 'machine_downtime')->count(),
                'delayDays' => $logs->sum(fn($l) => max(0, $l->shifted_to_day - $l->original_day)),
            ],
        ]);
    }

    /**
     * Show delay log + monthly work summary for reporting.
     */
    public function delayReport(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $logs = ScheduleDelayLog::where('year', $year)
            ->where('month', $month)
            ->orderBy('original_day')
            ->get();

        // Monthly work summary from production_schedules
        $allCells = ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->whereNotNull('task')
            ->get()
            ->sort(function($a, $b) {
                if ($a->day !== $b->day) {
                    return $a->day <=> $b->day;
                }
                return array_search($a->process, $this->processes) <=> array_search($b->process, $this->processes);
            })->values();

        // Build per-process summary
        $processSummary = [];
        foreach ($allCells as $cell) {
            $proc = $cell->process;
            if (!isset($processSummary[$proc])) {
                $processSummary[$proc] = ['tasks' => [], 'days' => 0];
            }
            $processSummary[$proc]['days']++;
            foreach (array_map('trim', explode(',', $cell->task)) as $t) {
                if ($t && !str_starts_with($t, '🔧')) {
                    $clean = preg_replace('/\s*\(\d+\/\d+\)\s*(URGENT)?/', '', $t);
                    $clean = str_replace(' (URGENT)', '', $clean);
                    $processSummary[$proc]['tasks'][$clean] = ($processSummary[$proc]['tasks'][$clean] ?? 0) + 1;
                }
            }
        }

        // Reorder processSummary according to standard process list
        $sortedProcessSummary = [];
        foreach ($this->processes as $proc) {
            if (isset($processSummary[$proc])) {
                $sortedProcessSummary[$proc] = $processSummary[$proc];
            }
        }
        $processSummary = $sortedProcessSummary;

        // Stats
        $today = now()->day;
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $completedCells = ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->where('day', '<', ($year == now()->year && $month == now()->month) ? $today : $daysInMonth + 1)
            ->whereNotNull('task')
            ->count();

        $delayedTasks   = $logs->where('reason_type', 'urgent_task')->count();
        $downtimeEvents = $logs->where('reason_type', 'machine_downtime')->groupBy('reason_detail')->count();
        $totalDelayDays = $logs->sum(fn($l) => max(0, $l->shifted_to_day - $l->original_day));

        return view('schedule.delay-report', compact(
            'year', 'month', 'logs',
            'processSummary', 'completedCells', 'allCells',
            'delayedTasks', 'downtimeEvents', 'totalDelayDays', 'daysInMonth'
        ));
    }

    /**
     * Delete a delay log entry.
     */
    public function destroyDelayLog($id)
    {
        $log = ScheduleDelayLog::findOrFail($id);
        $log->delete();

        return back()->with('success', 'Delay log entry removed successfully.');
    }

    /**
     * Collect N consecutive working days (Mon–Sat) starting from startDay.
     */
    private function collectWorkingDays(int $year, int $month, int $startDay, float $count): array
    {
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $days = [];
        $d = $startDay;
        $collected = 0;
        $target = max(1, ceil($count)); // Ensure we collect at least 1 day for the visual grid
        
        while ($collected < $target && $d <= $daysInMonth) {
            $dow = Carbon::createFromDate($year, $month, $d)->dayOfWeek;
            // Skip Sunday (0) only - Work Mon-Sat (1-6)
            if ($dow !== 0) {
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
            // Skip Sunday (0) only
            if ($dow !== 0) return $d;
            $d++;
        }
        return $d; // may exceed month — caller must check
    }

    private function processEmoji(string $process): string
    {
        return match ($process) {
            'Design'    => '🖥️', // Digital design / CTP
            'Press'     => '🖨️', // Press
            'Digital'   => '🖨️', // Digital
            'Folding'   => '📑', // Folding
            'Gathering' => '📚', // Gathering
            'Staple'    => '🖇️', // Stapling
            'Binding'   => '📕', // Binding
            'Cutting'   => '✂️',  // Cutting machine
            'Packaging' => '📦', // Packaging boxes
            'Delivery'  => '🚛', // Heavy transport truck
            default     => '⚙️', // Gear for generic process
        };
    }

    /**
     * Show daily progress tracking modal (AJAX).
     * GET /schedule/progress?year=X&month=Y&day=Z&process=Press
     */
    public function showProgress(Request $request)
    {
        $year = (int) $request->get('year');
        $month = (int) $request->get('month');
        $day = (int) $request->get('day');
        $process = $request->get('process');

        // Get scheduled tasks for this cell
        $schedule = ProductionSchedule::where([
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'process' => $process,
        ])->first();

        if (!$schedule || !$schedule->task) {
            return response()->json([
                'ok' => false,
                'message' => 'No tasks scheduled for this day',
            ], 404);
        }

        // Parse tasks and get progress for each
        $tasks = array_map('trim', explode(',', $schedule->task));
        $progressData = [];

        foreach ($tasks as $taskStr) {
            // Extract task name and total quantity from format: "Task [2d] {100}"
            $qtyMatch = null;
            preg_match('/\{([^}]+)\}/', $taskStr, $qtyMatch);
            $totalQty = $qtyMatch ? (int) $qtyMatch[1] : null;

            // Clean task name
            $taskName = preg_replace('/\[\d+d\]|\{[^}]+\}|\(\d+\/\d+\)/', '', $taskStr);
            $taskName = trim($taskName);

            // Get existing progress
            $progress = ProductionTaskProgress::where([
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'process' => $process,
                'task_name' => $taskName,
            ])->first();

            $progressData[] = [
                'task_name' => $taskName,
                'total_quantity' => $progress->total_quantity ?? $totalQty,
                'printed_quantity' => $progress->printed_quantity ?? 0,
                'note' => $progress->note ?? '',
                'progress_percentage' => $progress ? $progress->progress_percentage : 0,
            ];
        }

        return response()->json([
            'ok' => true,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'process' => $process,
            'tasks' => $progressData,
        ]);
    }

    /**
     * Update daily progress (AJAX).
     * POST /schedule/progress
     */
    public function updateProgress(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'day' => 'required|integer|min:1|max:31',
            'process' => 'required|string',
            'task_name' => 'required|string',
            'total_quantity' => 'nullable|integer|min:0',
            'printed_quantity' => 'required|integer|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $progress = ProductionTaskProgress::updateOrCreate(
            [
                'year' => $request->year,
                'month' => $request->month,
                'day' => $request->day,
                'process' => $request->process,
                'task_name' => $request->task_name,
            ],
            [
                'total_quantity' => $request->total_quantity,
                'printed_quantity' => $request->printed_quantity,
                'note' => $request->note,
            ]
        );

        return response()->json([
            'ok' => true,
            'progress' => $progress,
            'progress_percentage' => $progress->progress_percentage,
            'remaining_quantity' => $progress->remaining_quantity,
            'is_completed' => $progress->is_completed,
        ]);
    }

    /**
     * Show Weekly Schedule Report View
     */
    public function weeklyReport(Request $request): \Illuminate\View\View
    {
        $telegramGroups = \App\Models\TelegramGroup::orderBy('name')->get();
        
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        // Get schedule entries for the current week
        // Note: ProductionSchedule stores year, month, and day.
        $entries = collect();
        $currentDate = $weekStart->copy();
        
        while ($currentDate <= $weekEnd) {
            $dayEntries = \App\Models\ProductionSchedule::where('year', $currentDate->year)
                ->where('month', $currentDate->month)
                ->where('day', $currentDate->day)
                ->whereNotNull('task')
                ->where('task', '!=', '')
                ->get();
            
            $entries = $entries->merge($dayEntries);
            $currentDate->addDay();
        }

        // Group entries by day then by process
        $groupedEntries = [];
        $currentDate = $weekStart->copy();
        while ($currentDate <= $weekEnd) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayLabel = $currentDate->format('l, d/m/Y');
            
            $dayEntries = $entries->filter(function($entry) use ($currentDate) {
                return $entry->year == $currentDate->year && 
                       $entry->month == $currentDate->month && 
                       $entry->day == $currentDate->day;
            });
            
            $groupedByProcess = [];
            foreach ($this->processes as $process) {
                $processEntries = $dayEntries->where('process', $process)->values();
                if ($processEntries->isNotEmpty()) {
                    $groupedByProcess[$process] = $processEntries;
                }
            }
            
            $groupedEntries[$dateStr] = [
                'label' => $dayLabel,
                'processes' => $groupedByProcess,
            ];
            
            $currentDate->addDay();
        }

        // Calculate statistics
        $stats = [
            'total' => $entries->count(),
            'done' => $entries->where('status', 'done')->count(),
            'in_progress' => $entries->where('status', 'in-progress')->count(),
            'delayed' => $entries->where('status', 'delayed')->count(),
        ];
        
        $stats['done_percent'] = $stats['total'] > 0 ? round(($stats['done'] / $stats['total']) * 100) : 0;

        return view('schedule.weekly-report', compact(
            'groupedEntries', 
            'telegramGroups', 
            'weekStart', 
            'weekEnd',
            'stats'
        ));
    }

    /**
     * Generate weekly schedule report json (for copy to clipboard or view)
     */
    public function generateWeeklyReportJson(Request $request)
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $report = $this->buildWeeklyReportText($weekStart, $weekEnd);
        
        return response()->json([
            'success' => true,
            'report' => $report,
            'weekStart' => $weekStart->toDateString(),
            'weekEnd' => $weekEnd->toDateString(),
        ]);
    }

    /**
     * Send Weekly Schedule report to Telegram
     */
    public function sendWeeklyReportTelegram(Request $request)
    {
        $groupId = $request->input('group_id');
        
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        // Prevent double-send: lock on group for 15s.
        $lockKey = 'tg-schedule-weekly:' . md5($weekStart->toDateString() . '|' . ($groupId ?? 'all'));
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 15);

        if (!$lock->get()) {
            $msg = 'កំពុងផ្ញើរួចហើយ សូមរង់ចាំបន្តិច... / A send is already in progress.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 429)
                : back()->with('error', $msg);
        }

        try {
            $report = $this->buildWeeklyReportText($weekStart, $weekEnd, true);
            
            // Inline Keyboard Button to view the full report on Web
            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '🌐 មើលកាលវិភាគពេញលេញ (View Full)', 'url' => url(route('schedule.weekly-report'))]
                    ]
                ]
            ];
            
            // Send to Telegram
            $telegramService = app(\App\Services\TelegramService::class);
            if ($groupId) {
                $success = $telegramService->sendMessage($groupId, $report, null, 'HTML', $replyMarkup);
            } else {
                $groups = \App\Models\TelegramGroup::where('is_active', true)->get();
                $success = true;
                foreach ($groups as $group) {
                    if (!$telegramService->sendMessage($group->chat_id, $report, null, 'HTML', $replyMarkup)) {
                        $success = false;
                    }
                }
            }
            
            $okMsg  = 'របាយការណ៍កាលវិភាគសប្តាហ៍ត្រូវបានផ្ញើទៅ Telegram ជោគជ័យ!';
            $errMsg = 'មិនអាចផ្ញើរបាយការណ៍បានទេ។ សូមពិនិត្យ Telegram Bot ឬ Group។';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => $success,
                    'message' => $success ? $okMsg : $errMsg,
                ], $success ? 200 : 422);
            }

            return back()->with($success ? 'success' : 'error', $success ? $okMsg : $errMsg);

        } catch (\Exception $e) {
            \Log::error('Telegram Weekly Schedule Send Error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'មានបញ្ហាបច្ចេកទេស៖ ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->with('error', 'មានបញ្ហាបច្ចេកទេស៖ ' . $e->getMessage());
        }
    }

    /**
     * Helper to build the text for the weekly schedule report
     */
    private function buildWeeklyReportText($weekStart, $weekEnd, $forTelegram = false): string
    {
        $dateRangeStr = $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y');
        
        $b = $forTelegram ? '<b>' : '';
        $bb = $forTelegram ? '</b>' : '';
        
        $report = "📅 {$b}កាលវិភាគផលិតកម្មប្រចាំសប្តាហ៍{$bb}\n";
        $report .= "🗓 សប្តាហ៍ទី: {$dateRangeStr}\n";
        $report .= "━━━━━━━━━━━━━━━━━━━━\n\n";
        
        $currentDate = $weekStart->copy();
        $hasAnyTasks = false;
        
        while ($currentDate <= $weekEnd) {
            $dayEntries = \App\Models\ProductionSchedule::where('year', $currentDate->year)
                ->where('month', $currentDate->month)
                ->where('day', $currentDate->day)
                ->whereNotNull('task')
                ->where('task', '!=', '')
                ->get();
            
            if ($dayEntries->isNotEmpty()) {
                $hasAnyTasks = true;
                $dayName = match($currentDate->dayOfWeek) {
                    0 => 'អាទិត្យ (Sunday)',
                    1 => 'ច័ន្ទ (Monday)',
                    2 => 'អង្គារ (Tuesday)',
                    3 => 'ពុធ (Wednesday)',
                    4 => 'ព្រហស្បតិ៍ (Thursday)',
                    5 => 'សុក្រ (Friday)',
                    6 => 'សៅរ៍ (Saturday)',
                    default => $currentDate->format('l')
                };
                
                $report .= "🔹 {$b}{$dayName}, " . $currentDate->format('d/m/Y') . "{$bb}\n";
                
                foreach ($this->processes as $process) {
                    $processEntries = $dayEntries->where('process', $process)->values();
                    if ($processEntries->isNotEmpty()) {
                        $report .= "  • {$b}{$process}:{$bb}\n";
                        foreach ($processEntries as $entry) {
                            // Check for done/in-progress status
                            $icon = '🔸';
                            if ($entry->status === 'done') $icon = '✅';
                            if ($entry->status === 'in-progress') $icon = '⏳';
                            if ($entry->status === 'delayed') $icon = '⚠️';
                            
                            $parsed = self::parseTaskShortcuts($entry->task);
                            $taskText = $forTelegram ? $parsed['original'] : $parsed['textFormat'];
                            
                            $report .= "    {$icon} {$taskText}\n";
                            if (!empty($entry->note)) {
                                $report .= "      └ ចំណាំ: {$entry->note}\n";
                            }
                        }
                    }
                }
                $report .= "\n";
            }
            
            $currentDate->addDay();
        }
        
        if (!$hasAnyTasks) {
            $report .= "មិនមានការងារកំណត់ក្នុងកាលវិភាគសម្រាប់សប្តាហ៍នេះទេ។\n";
        }
        
        $report .= "━━━━━━━━━━━━━━━━━━━━\n";
        $report .= "🤖 បង្កើតដោយប្រព័ន្ធ Printing Tracker";
        
        return $report;
    }

    /**
     * Parse task shortcuts into readable badges and text
     */
    public static function parseTaskShortcuts($taskStr)
    {
        $subject = null;
        $type = null;
        $original = $taskStr;
        
        // Subject mappings
        $subjects = [];
        if (preg_match('/\bWG\b/i', $taskStr)) { $subjects[] = 'Writing and Grammar'; $taskStr = preg_replace('/\bWG\b/i', '', $taskStr); }
        if (preg_match('/\bRSS\b/i', $taskStr)) { $subjects[] = 'Reading and Social'; $taskStr = preg_replace('/\bRSS\b/i', '', $taskStr); }
        if (preg_match('/\bLS\b/i', $taskStr)) { $subjects[] = 'Listening and Speaking'; $taskStr = preg_replace('/\bLS\b/i', '', $taskStr); }
        
        // Type mappings
        $types = [];
        if (preg_match('/\bTX\b/i', $taskStr)) { $types[] = 'Textbook'; $taskStr = preg_replace('/\bTX\b/i', '', $taskStr); }
        if (preg_match('/\bWB\b/i', $taskStr)) { $types[] = 'Workbook'; $taskStr = preg_replace('/\bWB\b/i', '', $taskStr); }
        
        $subjectStr = !empty($subjects) ? implode(' & ', $subjects) : null;
        $typeStr = !empty($types) ? implode(' & ', $types) : null;
        
        $bookName = trim(implode(' ', array_filter([$subjectStr, $typeStr])));
        $bookName = $bookName ?: null;

        // Common level expansions
        $taskStr = preg_replace('/\bPre6\b/i', 'Pre School 6', $taskStr);
        $taskStr = preg_replace('/\bPre5\b/i', 'Pre School 5', $taskStr);
        $taskStr = preg_replace('/\bPre4\b/i', 'Pre School 4', $taskStr);
        $taskStr = preg_replace('/\bL([1-6])\b/i', 'Level $1', $taskStr);
        
        $rest = trim(preg_replace('/\s+/', ' ', $taskStr));
        
        // Text format for Telegram:
        $parts = array_filter([$bookName, $rest]);
        $textFormat = !empty($parts) ? implode(' | ', $parts) : $original;
        
        return [
            'original' => $original,
            'subject' => $subject,
            'type' => $type,
            'bookName' => $bookName,
            'rest' => $rest,
            'textFormat' => $textFormat,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // SMART PRODUCTION PLANNING & TRACKING ENDPOINTS
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /schedule/plan/preview — Calculate and preview proposed schedule.
     */
    public function previewPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'quantity'         => 'required|integer|min:1',
            'start_date'       => 'required|date',
            'due_date'         => 'nullable|date',
            'priority'         => 'required|in:low,normal,high,urgent',
            'template_id'      => 'nullable|exists:production_templates,id',
            'book_id'          => 'nullable|exists:books,id',
            'print_request_id' => 'nullable|exists:print_requests,id',
            'include_sundays'  => 'nullable|boolean',
            'stage_machines'   => 'nullable|array',
        ]);

        $plan = $this->planningService->generatePlan($validated);

        return response()->json([
            'ok'   => true,
            'plan' => $plan,
        ]);
    }

    /**
     * POST /schedule/plan/confirm — Commit generated plan to calendar.
     */
    public function confirmPlan(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|array',
            'plan.job' => 'required|array',
            'plan.stages' => 'required|array',
        ]);

        $userName = session('user_name') ?? (auth()->check() ? auth()->user()->name : 'User');
        $job = $this->planningService->confirmPlan($validated['plan'], $userName);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'         => true,
                'message'    => "Production Plan #{$job->job_number} ({$job->name}) has been scheduled successfully!",
                'job_id'     => $job->id,
                'job_number' => $job->job_number,
            ]);
        }

        $startDate = Carbon::parse($job->start_date);
        return redirect()->route('schedule.index', ['year' => $startDate->year, 'month' => $startDate->month])
            ->with('success', "Production Plan #{$job->job_number} created successfully!");
    }

    /**
     * POST /schedule/plan/simulate — Run what-if analysis.
     */
    public function simulatePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'quantity'         => 'required|integer|min:1',
            'start_date'       => 'required|date',
            'due_date'         => 'nullable|date',
            'priority'         => 'required|in:low,normal,high,urgent',
            'template_id'      => 'nullable|exists:production_templates,id',
            'include_sundays'  => 'nullable|boolean',
        ]);

        $simulation = $this->planningService->simulatePlan($validated);

        return response()->json([
            'ok'         => true,
            'simulation' => $simulation,
        ]);
    }

    /**
     * GET /schedule/plan/resources — Load templates, machines, processes, requests for wizard.
     */
    public function getPlanResources(): JsonResponse
    {
        $templates = ProductionTemplate::with('processes')->where('is_active', true)->get();
        $processes = ProductionProcess::where('is_active', true)->orderBy('sequence')->get();
        $machines  = Machine::where('status', '!=', 'retired')->get();
        $books     = Book::ordered()->get(['id', 'title', 'grade', 'category']);
        $requests  = PrintRequest::where('status', 'approved')->with('items')->latest()->get();

        return response()->json([
            'templates' => $templates,
            'processes' => $processes,
            'machines'  => $machines,
            'books'     => $books,
            'requests'  => $requests,
        ]);
    }

    /**
     * POST /schedule/actual/record — Record daily actual output.
     */
    public function recordActualOutput(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:production_schedules,id',
            'actual_qty'  => 'required|integer|min:0',
            'notes'       => 'nullable|string|max:500',
        ]);

        $userName = session('user_name') ?? (auth()->check() ? auth()->user()->name : 'Operator');
        $actual = $this->planningService->recordActual(
            (int)$validated['schedule_id'],
            (int)$validated['actual_qty'],
            $validated['notes'] ?? null,
            $userName
        );

        $schedule = ProductionSchedule::with(['job', 'machine'])->find($validated['schedule_id']);

        return response()->json([
            'ok'        => true,
            'message'   => 'Actual output recorded successfully!',
            'schedule'  => [
                'id'          => $schedule->id,
                'planned_qty' => $schedule->planned_qty,
                'actual_qty'  => $schedule->actual_qty,
                'remaining'   => $schedule->remainingQty(),
                'status'      => $schedule->status,
                'progress'    => $schedule->progressPercent(),
                'variance'    => $actual->variance,
            ],
        ]);
    }

    /**
     * POST /schedule/plan/reschedule-suggest — Generate auto-reschedule suggestion for delayed tasks.
     */
    public function rescheduleSuggestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:production_schedules,id',
            'delay_days'  => 'required|integer|min:1|max:30',
        ]);

        $suggestion = $this->planningService->generateRescheduleSuggestion(
            (int)$validated['schedule_id'],
            (int)$validated['delay_days']
        );

        return response()->json([
            'ok'         => true,
            'suggestion' => $suggestion,
        ]);
    }

    /**
     * POST /schedule/plan/reschedule-apply — Apply approved reschedule shifts.
     */
    public function applyReschedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shifts' => 'required|array',
            'reason' => 'nullable|string|max:255',
        ]);

        $applied = $this->planningService->applyReschedule(
            $validated,
            $validated['reason'] ?? 'Auto-reschedule'
        );

        return response()->json([
            'ok'      => true,
            'message' => "Successfully rescheduled {$applied} tasks across working days.",
            'applied' => $applied,
        ]);
    }

    /**
     * POST /schedule/cell/lock — Toggle locked state for a cell.
     */
    public function toggleLock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:production_schedules,id',
        ]);

        $schedule = ProductionSchedule::findOrFail($validated['schedule_id']);
        $schedule->is_locked = !$schedule->is_locked;
        if ($schedule->is_locked) {
            $schedule->status = 'locked';
        } elseif ($schedule->status === 'locked') {
            $schedule->status = 'planned';
        }
        $schedule->save();

        ScheduleAudit::log(
            $schedule->is_locked ? 'cell_locked' : 'cell_unlocked',
            $schedule->production_job_id,
            $schedule->id,
            null,
            ['is_locked' => $schedule->is_locked]
        );

        return response()->json([
            'ok'        => true,
            'is_locked' => $schedule->is_locked,
            'status'    => $schedule->status,
            'message'   => $schedule->is_locked ? 'Cell has been locked.' : 'Cell has been unlocked.',
        ]);
    }

    /**
     * POST /schedule/bulk-import — Excel / CSV / Text bulk import parser.
     */
    public function bulkImport(Request $request)
    {
        $rawText = $request->input('import_data', '');
        $year    = (int)$request->input('year', now()->year);
        $month   = (int)$request->input('month', now()->month);

        if (empty(trim($rawText))) {
            return back()->with('error', 'Please paste data to import.');
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($rawText));
        $imported = 0;
        $errors = [];

        $allProcesses = ProductionProcess::pluck('name')->toArray();
        if (empty($allProcesses)) {
            $allProcesses = $this->processes;
        }

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) continue;

            // Check if header row
            if (preg_match('/^(job|process|date|day|task)/i', $line)) continue;

            // Format A: CSV / TSV — "Job Name, Quantity, Process, Day/Date, Machine"
            // Format B: Colon syntax — "02/09/2026: Press - Math 10 (8000)"
            $process = null;
            $day = null;
            $task = null;
            $plannedQty = null;

            if (str_contains($line, "\t") || str_contains($line, ',')) {
                $delimiter = str_contains($line, "\t") ? "\t" : ',';
                $parts = array_map('trim', explode($delimiter, $line));

                // Flexible column mapping:
                // If 1st is Process: [Process, Day, Task, Qty]
                // If 1st is Job: [Job, Qty, Process, Day]
                if (in_array(ucfirst($parts[0] ?? ''), $allProcesses)) {
                    $process = ucfirst($parts[0]);
                    $day = isset($parts[1]) ? (int)$parts[1] : 1;
                    $task = $parts[2] ?? 'Imported Task';
                    $plannedQty = isset($parts[3]) ? (int)$parts[3] : null;
                } else {
                    $task = $parts[0] ?? 'Imported Task';
                    $plannedQty = isset($parts[1]) ? (int)$parts[1] : null;
                    $process = isset($parts[2]) ? ucfirst($parts[2]) : 'Press';
                    $day = isset($parts[3]) ? (int)$parts[3] : 1;
                }
            } elseif (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})(?:[\/\-\.](\d{2,4}))?\s*:\s*([^\-]+)\s*-\s*(.+)$/i', $line, $m)) {
                $day = (int)$m[1];
                $process = ucfirst(trim($m[4]));
                $task = trim($m[5]);
            } else {
                $errors[] = "Line " . ($lineNum + 1) . ": Unrecognized format '{$line}'";
                continue;
            }

            if ($day < 1 || $day > 31) {
                $errors[] = "Line " . ($lineNum + 1) . ": Invalid day '{$day}'";
                continue;
            }

            if (!in_array($process, $allProcesses)) {
                $process = 'Other';
            }

            ProductionSchedule::updateOrCreate(
                [
                    'year'    => $year,
                    'month'   => $month,
                    'process' => $process,
                    'day'     => $day,
                ],
                [
                    'task'        => $task,
                    'planned_qty' => $plannedQty,
                    'status'      => 'planned',
                ]
            );
            $imported++;
        }

        $msg = "Successfully imported {$imported} schedule entries!";
        if (!empty($errors)) {
            $msg .= " (" . count($errors) . " lines skipped due to invalid format)";
        }

        return redirect()->route('schedule.index', ['year' => $year, 'month' => $month])
            ->with('success', $msg);
    }

    /**
     * POST /schedule/templates — Save or create a production template.
     */
    public function saveTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id'          => 'nullable|exists:production_templates,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'stages'      => 'required|array|min:1',
            'stages.*.process_name' => 'required|string',
            'stages.*.capacity'     => 'nullable|integer',
        ]);

        $template = ProductionTemplate::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            [
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active'   => true,
            ]
        );

        ProductionTemplateProcess::where('template_id', $template->id)->delete();
        foreach ($validated['stages'] as $index => $stage) {
            ProductionTemplateProcess::create([
                'template_id'  => $template->id,
                'process_name' => $stage['process_name'],
                'sequence'     => $index + 1,
                'capacity'     => !empty($stage['capacity']) ? (int)$stage['capacity'] : null,
            ]);
        }

        return response()->json([
            'ok'       => true,
            'message'  => 'Production template saved successfully!',
            'template' => $template->load('processes'),
        ]);
    }
}

