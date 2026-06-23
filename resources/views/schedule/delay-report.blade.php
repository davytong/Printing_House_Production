@extends('layouts.app')

@section('title', 'Monthly Work Summary')

@section('content')
@php
    use Carbon\Carbon;
    $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

    $processColors = [
        'Design'    => '#4285f4',
        'Press'     => '#ea4335',
        'Folding'   => '#9c27b0',
        'Gathering' => '#ff9800',
        'Staple'    => '#00bcd4',
        'Binding'   => '#e91e63',
        'Cutting'   => '#009688',
        'Packaging' => '#4caf50',
        'Delivery'  => '#ff5722',
        'Other'     => '#607d8b',
    ];
@endphp

<style>
@media print {
    .sidebar, .topbar, nav, .page-header, .btn, .d-flex.align-items-center.justify-content-between { display: none !important; }
    .page-content { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    body { font-size: 11px; }
}

.summary-card {
    border-radius: 12px;
    border: 1px solid var(--border);
    padding: 1rem 1.25rem;
    background: #fff;
    flex: 1;
    min-width: 160px;
}
.summary-card .sc-value { font-size: 1.6rem; font-weight: 800; line-height: 1; }
.summary-card .sc-label { font-size: .75rem; color: var(--text-secondary); margin-top: .25rem; }
.process-badge {
    display: inline-block;
    padding: .15rem .55rem;
    border-radius: 4px;
    color: #fff;
    font-size: .72rem;
    font-weight: 700;
}
.task-chip {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: .15rem .6rem;
    font-size: .75rem;
    color: #334155;
    margin: 2px;
}
.section-title {
    font-size: .82rem;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: .75rem;
    padding-bottom: .4rem;
    border-bottom: 2px solid var(--border);
}
</style>

{{-- HEADER --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="mb-0">
            <i class="bi bi-clipboard-data text-primary me-2"></i>
            Monthly Work Summary — {{ $monthName }}
        </h5>
        <div style="font-size:.78rem;color:var(--text-secondary);margin-top:.2rem;">
            Production schedule report with delay log
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('schedule.index', ['year'=>$year,'month'=>$month]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Schedule
        </a>
        <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print / PDF
        </button>
    </div>
</div>

{{-- ── TOP STATS ── --}}
<div class="d-flex flex-wrap gap-3 mb-4">
    <div class="summary-card">
        <div class="sc-value text-primary">{{ $allCells->count() }}</div>
        <div class="sc-label"><i class="bi bi-calendar3 me-1"></i>Total Scheduled Cells</div>
    </div>
    <div class="summary-card">
        <div class="sc-value text-success">{{ $completedCells }}</div>
        <div class="sc-label"><i class="bi bi-check-circle me-1"></i>Completed (past days)</div>
    </div>
    <div class="summary-card">
        <div class="sc-value text-danger">{{ $delayedTasks }}</div>
        <div class="sc-label"><i class="bi bi-exclamation-triangle me-1"></i>Urgent Interruptions</div>
    </div>
    <div class="summary-card">
        <div class="sc-value text-warning">{{ $downtimeEvents }}</div>
        <div class="sc-label"><i class="bi bi-tools me-1"></i>Downtime Events</div>
    </div>
    <div class="summary-card">
        <div class="sc-value" style="color:#7c3aed;">+{{ $totalDelayDays }}</div>
        <div class="sc-label"><i class="bi bi-clock-history me-1"></i>Total Days Delayed</div>
    </div>
</div>

{{-- ── PROCESS WORK SUMMARY ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="section-title"><i class="bi bi-bar-chart-steps me-1"></i>Work Done by Process</div>
        @if(empty($processSummary))
            <p class="text-muted" style="font-size:.82rem;">No scheduled work for this month yet.</p>
        @else
        <div class="row g-3">
            @foreach($processSummary as $proc => $info)
            @php $clr = $processColors[$proc] ?? '#475569'; @endphp
            <div class="col-md-6 col-lg-4">
                <div style="border:1px solid {{ $clr }}30;border-left:4px solid {{ $clr }};border-radius:8px;padding:.75rem 1rem;background:#fff;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="process-badge" style="background:{{ $clr }}">{{ $proc }}</span>
                        <span style="font-size:.78rem;color:#64748b;">{{ $info['days'] }} day(s)</span>
                    </div>
                    <div>
                        @foreach($info['tasks'] as $taskName => $dayCount)
                            <span class="task-chip">
                                {{ $taskName }}
                                @if($dayCount > 1)
                                    <span style="background:{{ $clr }};color:#fff;border-radius:10px;padding:0 5px;font-size:.65rem;margin-left:3px;">×{{ $dayCount }}d</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── SCHEDULED TASK TIMELINE ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="section-title"><i class="bi bi-calendar-week me-1"></i>Full Schedule Timeline</div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
                <thead class="table-light">
                    <tr>
                        <th>Day</th>
                        <th>Process</th>
                        <th>Tasks</th>
                        <th>Note</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allCells->sortBy(['day','process']) as $cell)
                    @php
                        $today = now()->day;
                        $isCurrentMonth = ($year == now()->year && $month == now()->month);
                        $isPast   = $isCurrentMonth ? $cell->day < $today : $month < now()->month;
                        $isToday  = $isCurrentMonth && $cell->day == $today;
                        $isFuture = !$isPast && !$isToday;
                        $clr = $processColors[$cell->process] ?? '#475569';
                    @endphp
                    <tr style="{{ $isToday ? 'background:#fef9c3;' : '' }}">
                        <td style="font-family:var(--font-latin);font-weight:600;white-space:nowrap;">
                            {{ str_pad($cell->day,2,'0',STR_PAD_LEFT) }}/{{ str_pad($month,2,'0',STR_PAD_LEFT) }}
                            @if($isToday)<span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Today</span>@endif
                        </td>
                        <td><span class="process-badge" style="background:{{ $clr }}">{{ $cell->process }}</span></td>
                        <td style="max-width:260px;white-space:normal;">
                            @foreach(array_map('trim', explode(',', $cell->task)) as $t)
                                @if($t)
                                @php $isUrgent = str_contains(strtoupper($t), 'URGENT'); $isDowntime = str_starts_with($t,'🔧'); @endphp
                                <span style="display:inline-block;background:{{ $isUrgent ? '#fee2e2' : ($isDowntime ? '#fef3c7' : '#f1f5f9') }};
                                    color:{{ $isUrgent ? '#991b1b' : ($isDowntime ? '#92400e' : '#334155') }};
                                    border-radius:4px;padding:.1rem .4rem;font-size:.72rem;margin:1px;">
                                    @if($isUrgent)<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>@endif
                                    @if($isDowntime)<i class="bi bi-tools me-1"></i>@endif
                                    {{ $t }}
                                </span>
                                @endif
                            @endforeach
                        </td>
                        <td style="font-size:.72rem;color:#64748b;max-width:150px;white-space:normal;">{{ $cell->note }}</td>
                        <td>
                            @if($isDowntime ?? false)
                                <span class="badge bg-warning text-dark">Downtime</span>
                            @elseif($isPast)
                                <span class="badge bg-success">Done</span>
                            @elseif($isToday)
                                <span class="badge bg-warning text-dark">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Planned</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── DELAY & SHIFT LOG ── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="section-title d-flex align-items-center justify-content-between">
            <span><i class="bi bi-journal-text me-1"></i>Delay & Shift Log</span>
            @if($logs->isNotEmpty())
                <span class="badge bg-danger">{{ $logs->where('reason_type','urgent_task')->count() }} urgent</span>
                <span class="badge bg-warning text-dark ms-1">{{ $logs->where('reason_type','machine_downtime')->count() }} downtime</span>
            @endif
        </div>
        @if($logs->isEmpty())
            <div class="alert alert-success mb-0" style="font-size:.82rem;">
                <i class="bi bi-check-circle me-2"></i>
                No delays this month. All tasks ran on their original schedule.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size:.8rem;">
                <thead class="table-light">
                    <tr>
                        <th>Process</th>
                        <th>Task</th>
                        <th>Original Day</th>
                        <th>→ Moved To</th>
                        <th>Delay</th>
                        <th>Reason</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    @php
                        $delay = $log->shifted_to_day - $log->original_day;
                        $clr = $processColors[$log->process] ?? '#475569';
                    @endphp
                    <tr>
                        <td><span class="process-badge" style="background:{{ $clr }}">{{ $log->process }}</span></td>
                        <td><strong>{{ $log->original_task }}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-secondary">Day {{ $log->original_day }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">Day {{ $log->shifted_to_day }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $delay > 3 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ $delay > 0 ? '+' . $delay . 'd' : ($delay < 0 ? $delay.'d earlier' : '—') }}
                            </span>
                        </td>
                        <td>
                            @if($log->reason_type === 'urgent_task')
                                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Urgent</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-tools me-1"></i>Downtime</span>
                            @endif
                        </td>
                        <td style="max-width:220px;white-space:normal;font-size:.72rem;color:#475569;">{{ $log->reason_detail }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2 p-2 rounded" style="background:#f8fafc;border:1px solid var(--border);font-size:.78rem;">
            <i class="bi bi-info-circle me-1 text-primary"></i>
            Total delay: <strong>+{{ $totalDelayDays }} working day(s)</strong> across
            <strong>{{ $logs->count() }}</strong> events —
            <strong>{{ $logs->where('reason_type','urgent_task')->count() }}</strong> urgent interruptions,
            <strong>{{ $logs->where('reason_type','machine_downtime')->count() }}</strong> downtime events.
        </div>
        @endif
    </div>
</div>

@endsection
