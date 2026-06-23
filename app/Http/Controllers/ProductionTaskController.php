<?php

namespace App\Http\Controllers;

use App\Models\MachineDowntime;
use App\Models\ProductionTask;
use App\Models\ScheduleShiftLog;
use App\Services\SchedulingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionTaskController extends Controller
{
    public function __construct(private SchedulingService $scheduler) {}

    // ═══════════════════════════════════════════════════════
    // WEB VIEWS
    // ═══════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $tasks = ProductionTask::query()
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->machine, fn($q, $m) => $q->where('assigned_machine_id', $m))
            ->when($request->process, fn($q, $p) => $q->where('process', $p))
            ->orderByRaw("FIELD(priority, 'urgent', 'standard')")
            ->orderBy('scheduled_start_date')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'total'       => ProductionTask::count(),
            'pending'     => ProductionTask::where('status', 'pending')->count(),
            'in_progress' => ProductionTask::where('status', 'in_progress')->count(),
            'completed'   => ProductionTask::where('status', 'completed')->count(),
            'paused'      => ProductionTask::where('status', 'paused')->count(),
            'urgent'      => ProductionTask::where('priority', 'urgent')
                            ->whereNotIn('status', ['completed','cancelled'])->count(),
        ];

        return view('tasks.index', compact('tasks', 'stats'));
    }

    // ═══════════════════════════════════════════════════════
    // API ENDPOINTS (JSON)
    // ═══════════════════════════════════════════════════════

    /**
     * POST /api/tasks — Create and schedule a new task.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'process'             => 'required|string|max:50',
            'duration_days'       => 'required|integer|min:1|max:60',
            'duration_hours'      => 'nullable|integer|min:1|max:24',
            'priority'            => 'required|in:standard,urgent',
            'assigned_machine_id' => 'nullable|integer|exists:machines,id',
            'scheduled_start_date'=> 'required|date',
            'due_date'            => 'nullable|date',
            'assigned_to'         => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        $task = ProductionTask::create($data);

        // Run scheduling algorithm (calculates end date, handles preemption)
        $task = $this->scheduler->scheduleTask($task);

        return response()->json([
            'ok'      => true,
            'task'    => $task->fresh(),
            'message' => $task->isUrgent()
                ? "Urgent task scheduled — conflicting tasks shifted"
                : "Task scheduled: {$task->scheduled_start_date->format('d/m')} → {$task->scheduled_end_date->format('d/m')}",
        ], 201);
    }

    /**
     * PUT /api/tasks/{id} — Update a task (triggers reschedule if dates/priority change).
     */
    public function update(Request $request, ProductionTask $task): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'sometimes|string|max:255',
            'description'         => 'nullable|string',
            'process'             => 'sometimes|string|max:50',
            'duration_days'       => 'sometimes|integer|min:1|max:60',
            'priority'            => 'sometimes|in:standard,urgent',
            'assigned_machine_id' => 'nullable|integer',
            'scheduled_start_date'=> 'sometimes|date',
            'due_date'            => 'nullable|date',
            'assigned_to'         => 'nullable|string|max:255',
            'status'              => 'sometimes|in:pending,in_progress,paused,completed,cancelled',
            'notes'               => 'nullable|string',
        ]);

        $wasUrgent = $task->isUrgent();
        $task->update($data);

        // If status changed to completed and was urgent → resume paused tasks
        if (($data['status'] ?? '') === 'completed' && $wasUrgent) {
            $this->scheduler->resumeAfterUrgent($task);
        }

        // If priority changed to urgent → preempt
        if (! $wasUrgent && $task->isUrgent()) {
            $task->scheduled_end_date = $task->calculateEndDate();
            $task->save();
            $this->scheduler->preemptForUrgent($task);
        }

        // If dates changed → recalculate end
        if (isset($data['scheduled_start_date']) || isset($data['duration_days'])) {
            $task->scheduled_end_date = $task->calculateEndDate();
            $task->save();
        }

        return response()->json(['ok' => true, 'task' => $task->fresh()]);
    }

    /**
     * POST /api/tasks/{id}/complete — Mark task as completed.
     */
    public function complete(ProductionTask $task): JsonResponse
    {
        $task->update([
            'status'          => 'completed',
            'actual_end_date' => now(),
        ]);

        if (! $task->actual_start_date) {
            $task->update(['actual_start_date' => $task->scheduled_start_date]);
        }

        // Resume any tasks that were paused by this one
        $resumed = $this->scheduler->resumeAfterUrgent($task);

        return response()->json([
            'ok'      => true,
            'task'    => $task->fresh(),
            'resumed' => $resumed,
            'message' => "Completed! {$resumed} paused task(s) resumed.",
        ]);
    }

    /**
     * POST /api/downtime — Log machine downtime, trigger cascade shift.
     */
    public function logDowntime(Request $request): JsonResponse
    {
        $data = $request->validate([
            'machine_id'     => 'required|integer|exists:machines,id',
            'start_time'     => 'required|date',
            'duration_hours' => 'required|integer|min:1',
            'reason'         => 'nullable|string|max:255',
        ]);

        $downtime = MachineDowntime::create($data);

        // Cascade shift affected tasks
        $shifted = $this->scheduler->handleDowntime($downtime);

        return response()->json([
            'ok'       => true,
            'downtime' => $downtime,
            'shifted'  => $shifted,
            'message'  => "{$shifted} task(s) rescheduled due to downtime.",
        ]);
    }

    /**
     * POST /api/schedule/recalculate — Force full schedule recalculation.
     */
    public function recalculate(Request $request): JsonResponse
    {
        $machineId = $request->input('machine_id');
        $resolved = $this->scheduler->resolveConflicts($machineId);

        return response()->json([
            'ok'       => true,
            'resolved' => $resolved,
            'message'  => "{$resolved} conflict(s) resolved.",
        ]);
    }

    /**
     * GET /api/schedule/report — Performance metrics.
     */
    public function report(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->input('to', now()->endOfMonth()));

        $metrics = $this->scheduler->getMetrics($from, $to);

        return response()->json(['ok' => true, 'data' => $metrics]);
    }

    /**
     * GET /api/tasks/timeline — Get tasks for Gantt/timeline view.
     */
    public function timeline(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->input('to', now()->endOfMonth()));

        $tasks = ProductionTask::where('scheduled_start_date', '<=', $to)
            ->where(function ($q) use ($from) {
                $q->where('scheduled_end_date', '>=', $from)
                  ->orWhereNull('scheduled_end_date');
            })
            ->orderBy('assigned_machine_id')
            ->orderBy('scheduled_start_date')
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'name'       => $t->name,
                'process'    => $t->process,
                'start'      => $t->scheduled_start_date->format('Y-m-d'),
                'end'        => $t->scheduled_end_date?->format('Y-m-d'),
                'status'     => $t->status,
                'priority'   => $t->priority,
                'machine_id' => $t->assigned_machine_id,
                'assigned_to'=> $t->assigned_to,
            ]);

        return response()->json(['ok' => true, 'tasks' => $tasks]);
    }

    /**
     * GET /api/schedule/shift-log — Audit trail of all shifts.
     */
    public function shiftLog(Request $request): JsonResponse
    {
        $logs = ScheduleShiftLog::with('task:id,name,process')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json(['ok' => true, 'logs' => $logs]);
    }

    /**
     * GET /api/tasks/today — Today's active work queue per machine.
     */
    public function todayQueue(): JsonResponse
    {
        $queue = $this->scheduler->getTodaysWorkQueue();
        return response()->json(['ok' => true, 'queue' => $queue]);
    }

    /**
     * GET /api/tasks/track/{jobName} — Track a job through the pipeline.
     */
    public function trackJob(string $jobName): JsonResponse
    {
        $tracking = $this->scheduler->trackJob($jobName);
        return response()->json(['ok' => true, 'data' => $tracking]);
    }

    /**
     * GET /api/machines/utilization — Machine utilization this month.
     */
    public function machineUtilization(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()));
        $to   = Carbon::parse($request->input('to', now()->endOfMonth()));

        $utilization = $this->scheduler->getMachineUtilization($from, $to);
        return response()->json(['ok' => true, 'data' => $utilization]);
    }

    /**
     * GET /api/tasks/upcoming — Tasks in next 7 days.
     */
    public function upcoming(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 7);
        $tasks = $this->scheduler->getUpcoming($days);

        return response()->json(['ok' => true, 'tasks' => $tasks->map(fn($t) => [
            'id'       => $t->id,
            'name'     => $t->name,
            'process'  => $t->process,
            'priority' => $t->priority,
            'status'   => $t->status,
            'start'    => $t->scheduled_start_date->format('Y-m-d'),
            'end'      => $t->scheduled_end_date?->format('Y-m-d'),
            'machine'  => $t->machine?->name,
            'days_until'=> max(0, now()->diffInDays($t->scheduled_start_date, false)),
        ])]);
    }
}
