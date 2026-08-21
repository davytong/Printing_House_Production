@extends('layouts.app')
@section('title', 'ការងារផលិតកម្ម')
@section('page-title', 'Production Tasks')

@section('content')
<style>
/* Dark Mode Overrides for Tasks List */
[data-theme="dark"] .table-light th { background: var(--surface-2) !important; color: var(--text-secondary) !important; border-bottom-color: var(--border) !important; }
[data-theme="dark"] .table > :not(caption) > * > * { background-color: var(--surface); color: var(--text-primary); border-bottom-color: var(--border); }
[data-theme="dark"] .table-hover tbody tr:hover > * { background-color: var(--surface-2); }
[data-theme="dark"] .bg-light.text-dark { background-color: var(--surface-2) !important; color: var(--text-primary) !important; border-color: var(--border) !important; }
[data-theme="dark"] .card { background-color: var(--surface); border-color: var(--border); }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h1 class="section-title">ការងារផលិតកម្ម</h1>
        <p class="section-sub">គ្រប់គ្រង និងតាមដានការងារផលិតកម្មទាំងអស់</p>
    </div>
    <div>
        <a href="{{ route('tasks.kanban') }}" class="btn btn-primary shadow-sm" style="border-radius:8px;font-weight:600;">
            <i class="bi bi-kanban"></i> Kanban Board
        </a>
    </div>
</div>

{{-- Stats row --}}
<div class="row g-3 mb-4">
    @foreach([
        ['total',       'សរុប',           'kpi-blue',   'bi-list-task'],
        ['pending',     'រង់ចាំ',          'kpi-amber',  'bi-hourglass-split'],
        ['in_progress', 'កំពុងដំណើរការ',   'kpi-green',  'bi-play-circle'],
        ['completed',   'បានបញ្ចប់',       'kpi-purple', 'bi-check2-circle'],
        ['urgent',      'បន្ទាន់',          'kpi-rose',   'bi-exclamation-triangle'],
    ] as [$key, $label, $cls, $icon])
    <div class="col-6 col-lg">
        <div class="kpi-card {{ $cls }}" style="padding:1rem;gap:.4rem">
            <div class="kpi-icon" style="width:34px;height:34px;font-size:.9rem">
                <i class="bi {{ $icon }}"></i>
            </div>
            <div>
                <div class="kpi-value" style="font-size:1.5rem">{{ $stats[$key] ?? 0 }}</div>
                <div class="kpi-label" style="font-size:.72rem">{{ $label }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('tasks.index') }}" class="card p-3 mb-4 border-0 shadow-sm">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">ស្ថានភាព</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">ទាំងអស់</option>
                @foreach(['pending'=>'រង់ចាំ','in_progress'=>'កំពុងដំណើរការ','paused'=>'ផ្អាក','completed'=>'បានបញ្ចប់','cancelled'=>'បោះបង់'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">ដំណើរការ</label>
            <select name="process" class="form-select form-select-sm">
                <option value="">ទាំងអស់</option>
                @foreach(['Design','Press','Digital','Folding','Gathering','Staple','Binding','Cutting','Packaging','Delivery','Other'] as $p)
                    <option value="{{ $p }}" @selected(request('process') === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100">
                <i class="bi bi-funnel"></i> តម្រង
            </button>
        </div>
        @if(request()->hasAny(['status','process','machine']))
        <div class="col-md-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-x"></i> សម្អាត
            </a>
        </div>
        @endif
    </div>
</form>

{{-- Tasks Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($tasks->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <p>មិនមានការងារនៅឡើយ</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>ឈ្មោះការងារ</th>
                        <th>ដំណើរការ</th>
                        <th>អាទិភាព</th>
                        <th>ស្ថានភាព</th>
                        <th>ចាប់ផ្ដើម</th>
                        <th>បញ្ចប់</th>
                        <th>ទទួលខុសត្រូវ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    @php
                        $priorityClass = $task->priority === 'urgent' ? 'badge bg-danger' : 'badge bg-secondary';
                        $statusClass   = match($task->status) {
                            'pending'     => 'badge bg-warning text-dark',
                            'in_progress' => 'badge bg-primary',
                            'paused'      => 'badge bg-secondary',
                            'completed'   => 'badge bg-success',
                            'cancelled'   => 'badge bg-light text-muted',
                            default       => 'badge bg-secondary',
                        };
                        $statusLabel = match($task->status) {
                            'pending'     => 'រង់ចាំ',
                            'in_progress' => 'កំពុងដំណើរការ',
                            'paused'      => 'ផ្អាក',
                            'completed'   => 'បានបញ្ចប់',
                            'cancelled'   => 'បោះបង់',
                            default       => $task->status,
                        };
                    @endphp
                    <tr>
                        <td class="text-muted small">{{ $task->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $task->name }}</div>
                            @if($task->notes)
                                <div class="text-muted small text-truncate" style="max-width:200px">{{ $task->notes }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $task->process }}</span>
                        </td>
                        <td>
                            <span class="{{ $priorityClass }}">
                                {{ $task->priority === 'urgent' ? 'បន្ទាន់' : 'ធម្មតា' }}
                            </span>
                        </td>
                        <td><span class="{{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td class="small">
                            {{ $task->scheduled_start_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="small">
                            @if($task->scheduled_end_date)
                                @php $isOverdue = $task->scheduled_end_date->isPast() && $task->status !== 'completed'; @endphp
                                <span class="{{ $isOverdue ? 'text-danger fw-semibold' : '' }}">
                                    {{ $task->scheduled_end_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $task->assigned_to ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tasks->hasPages())
        <div class="px-3 py-2 border-top">
            {{ $tasks->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
