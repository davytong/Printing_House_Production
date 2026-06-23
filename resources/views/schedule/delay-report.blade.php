@extends('layouts.app')

@section('title', 'Delay / Shift Log')

@section('content')
@php
    use Carbon\Carbon;
    $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
@endphp

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h5 class="mb-0">
        <i class="bi bi-journal-text text-warning me-2"></i>
        Delay &amp; Shift Log — {{ $monthName }}
    </h5>
    <a href="{{ route('schedule.index', ['year'=>$year,'month'=>$month]) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Schedule
    </a>
</div>

@if($logs->isEmpty())
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        No delays recorded for {{ $monthName }}. All tasks ran on their original schedule.
    </div>
@else
<div class="card border-0 shadow-sm">
    <div class="card-header" style="background:#fffbeb;border-bottom:1px solid #fde68a;">
        <strong>{{ $logs->count() }} delay event(s) in {{ $monthName }}</strong>
        <span class="ms-3 badge bg-danger">{{ $logs->where('reason_type','urgent_task')->count() }} urgent</span>
        <span class="ms-1 badge bg-warning text-dark">{{ $logs->where('reason_type','machine_downtime')->count() }} downtime</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
            <thead class="table-light">
                <tr>
                    <th>Process</th>
                    <th>Original Task</th>
                    <th>Original Day</th>
                    <th>Shifted To Day</th>
                    <th>Delay (days)</th>
                    <th>Reason Type</th>
                    <th>Reason Detail</th>
                    <th>Logged At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>
                        <span class="badge" style="background:
                            @switch($log->process)
                                @case('Press') #ea4335 @break
                                @case('Design') #4285f4 @break
                                @case('Folding') #9c27b0 @break
                                @case('Gathering') #ff9800 @break
                                @case('Staple') #00bcd4 @break
                                @case('Binding') #e91e63 @break
                                @case('Cutting') #009688 @break
                                @case('Packaging') #4caf50 @break
                                @case('Delivery') #ff5722 @break
                                @default #607d8b
                            @endswitch
                        ">{{ $log->process }}</span>
                    </td>
                    <td><strong>{{ $log->original_task }}</strong></td>
                    <td class="text-center">
                        <span class="badge bg-secondary">Day {{ $log->original_day }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">Day {{ $log->shifted_to_day }}</span>
                    </td>
                    <td class="text-center">
                        @php $delay = $log->shifted_to_day - $log->original_day; @endphp
                        <span class="badge {{ $delay > 3 ? 'bg-danger' : 'bg-warning text-dark' }}">
                            +{{ $delay }}d
                        </span>
                    </td>
                    <td>
                        @if($log->reason_type === 'urgent_task')
                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Urgent Task</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="bi bi-tools me-1"></i>Machine Downtime</span>
                        @endif
                    </td>
                    <td style="max-width:250px;white-space:normal;">{{ $log->reason_detail }}</td>
                    <td style="font-family:var(--font-latin);font-size:.75rem;color:#64748b;">
                        {{ $log->created_at->format('d/m H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 p-3 rounded" style="background:#f8fafc;border:1px solid var(--border);font-size:.82rem;">
    <strong><i class="bi bi-info-circle me-1"></i>Summary:</strong>
    Total working days delayed: <strong>{{ $logs->sum(fn($l) => max(0, $l->shifted_to_day - $l->original_day)) }}</strong> days across
    <strong>{{ $logs->count() }}</strong> tasks.
    This report can be used to explain production delays in the monthly report.
</div>
@endif
@endsection
