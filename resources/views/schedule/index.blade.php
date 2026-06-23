@extends('layouts.app')

@section('title', 'កាលវិភាគផលិតកម្មប្រចាំខែ')

@section('content')
@php
    use Carbon\Carbon;
    $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
    $monthNameKh = Carbon::createFromDate($year, $month, 1)->locale('km')->translatedFormat('F Y');

    // Process colors (bright, light fill — not dark)
    $processColors = [
        'Design'    => '#4285f4',  // bright blue
        'Press'     => '#ea4335',  // bright red
        'Folding'   => '#9c27b0',  // vibrant purple
        'Gathering' => '#ff9800',  // bright orange
        'Staple'    => '#00bcd4',  // cyan
        'Binding'   => '#e91e63',  // pink
        'Cutting'   => '#009688',  // teal
        'Packaging' => '#4caf50',  // green
        'Delivery'  => '#ff5722',  // deep orange
        'Other'     => '#607d8b',  // blue-grey
    ];

    // Build day info
    $days = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = Carbon::createFromDate($year, $month, $d);
        $days[] = [
            'day' => $d,
            'dow' => $date->dayOfWeek, // 0=Sun, 6=Sat
            'date' => $date->format('d/m/Y'),
            'label' => str_pad($d, 2, '0', STR_PAD_LEFT) . '/' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year,
        ];
    }

    // Prev/Next month
    $prevDate = Carbon::createFromDate($year, $month, 1)->subMonth();
    $nextDate = Carbon::createFromDate($year, $month, 1)->addMonth();
@endphp

<style>
/* Override max-width for schedule page — we need full width for the grid */
.page-content { max-width: 100% !important; }

/* ═══════════════════════════════════════════════════════
   SCHEDULE GRID — SCROLLABLE LIKE EXCEL
═══════════════════════════════════════════════════════ */
.schedule-container {
    background: var(--surface);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    overflow: hidden;
}

.schedule-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface-2);
}

.schedule-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: .5rem;
}

.schedule-nav {
    display: flex;
    align-items: center;
    gap: .5rem;
}

.schedule-nav .btn-nav {
    width: 36px; height: 36px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all var(--ease);
    text-decoration: none;
    color: var(--text-secondary);
}
.schedule-nav .btn-nav:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}

.schedule-month-label {
    font-size: .95rem;
    font-weight: 600;
    min-width: 160px;
    text-align: center;
    color: var(--text-primary);
}

/* ── THE SCROLLABLE GRID ── */
.schedule-grid-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 260px);
    position: relative;
}

/* Custom scrollbar for the grid */
.schedule-grid-wrapper::-webkit-scrollbar { height: 10px; width: 10px; }
.schedule-grid-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }
.schedule-grid-wrapper::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 5px; }
.schedule-grid-wrapper::-webkit-scrollbar-thumb:hover { background: #64748b; }
.schedule-grid-wrapper::-webkit-scrollbar-corner { background: #f1f5f9; }

.schedule-table {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
    font-size: .78rem;
}

.schedule-table th,
.schedule-table td {
    border: 1px solid #d1d5db;
    padding: 0;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}

/* ── STICKY FIRST COLUMN (Process names) ── */
.schedule-table th.col-process,
.schedule-table td.col-process {
    position: sticky;
    left: 0;
    z-index: 10;
    background: #ffffff;
    color: var(--text-primary);
    font-weight: 600;
    font-size: .8rem;
    min-width: 110px;
    max-width: 110px;
    padding: .5rem .75rem;
    text-align: left;
    border-color: #d1d5db;
}

/* ── HEADER ROW ── */
.schedule-table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #f1f5f9;
    color: #334155;
    padding: .4rem .3rem;
    font-weight: 600;
    font-size: .7rem;
    min-width: 95px;
    font-family: var(--font-latin);
    border-bottom: 2px solid #cbd5e1;
}

/* Corner cell (process header) */
.schedule-table thead th.col-process {
    z-index: 20;
    background: #f1f5f9;
    color: #334155;
    font-size: .75rem;
}

/* ── DAY CELLS ── */
.schedule-table td.day-cell {
    min-width: 95px;
    max-width: 140px;
    height: 42px;
    padding: .2rem .3rem;
    cursor: pointer;
    transition: background .15s;
    position: relative;
}

.schedule-table td.day-cell:hover {
    background: #e0f2fe !important;
}

.schedule-table td.day-cell.weekend {
    background: #fef3c7;
}

.schedule-table td.day-cell.holiday {
    background: #fecaca;
}

.schedule-table td.day-cell.today {
    outline: 2px solid #ef4444;
    outline-offset: -2px;
}

/* Task content inside cell */
.cell-task {
    font-size: .68rem;
    font-weight: 600;
    padding: .15rem .35rem;
    border-radius: 4px;
    display: block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.3;
    color: #fff;
    text-shadow: 0 1px 1px rgba(0,0,0,.15);
    margin-bottom: 2px;
}

.cell-note {
    font-size: .62rem;
    color: #64748b;
    display: block;
    margin-top: 1px;
}

/* ═══════════════════════════════════════════════════════
   LEGEND
═══════════════════════════════════════════════════════ */
.schedule-legend {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border);
    background: var(--surface-2);
    align-items: center;
}

.legend-title {
    font-weight: 700;
    font-size: .78rem;
    color: var(--text-primary);
    margin-right: .5rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: .3rem;
    font-size: .72rem;
    color: var(--text-secondary);
}

.legend-dot {
    width: 14px; height: 14px;
    border-radius: 3px;
    flex-shrink: 0;
}

/* ═══════════════════════════════════════════════════════
   INFO BAR (Today/Tomorrow/Progress)
═══════════════════════════════════════════════════════ */
.schedule-info-bar {
    display: flex;
    gap: .75rem;
    padding: .75rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: #fafbfc;
    flex-wrap: wrap;
}

.info-card {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    padding: .5rem .75rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: #fff;
    flex: 1;
    min-width: 200px;
}

.info-card-icon {
    width: 28px; height: 28px;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem;
    flex-shrink: 0;
}

.info-today .info-card-icon { background: #dbeafe; color: #2563eb; }
.info-tomorrow .info-card-icon { background: #fef3c7; color: #d97706; }
.info-progress .info-card-icon { background: #ede9fe; color: #7c3aed; }

.info-card-content {
    display: flex;
    flex-direction: column;
    gap: .2rem;
}

.info-card-content strong {
    font-size: .78rem;
    color: var(--text-primary);
}

.info-tasks {
    display: flex;
    flex-wrap: wrap;
    gap: .25rem;
}

.info-task-badge {
    font-size: .68rem;
    padding: .1rem .4rem;
    border-radius: 3px;
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 500;
}

/* ═══════════════════════════════════════════════════════
   EDIT MODAL
═══════════════════════════════════════════════════════ */
.modal-cell-edit .modal-body { padding: 1.5rem; }
.modal-cell-edit .form-label { font-size: .82rem; font-weight: 600; }

/* Process row colors (left strip) */
@foreach($processColors as $proc => $clr)
.process-row-{{ Str::slug($proc) }} td.col-process {
    border-left: 4px solid {{ $clr }};
}
@endforeach
</style>

<div class="schedule-container">
    {{-- HEADER --}}
    <div class="schedule-header">
        <div class="schedule-title">
            <i class="bi bi-calendar3" style="color:var(--primary);"></i>
            កាលវិភាគផលិតកម្មប្រចាំខែ
        </div>
        <div class="schedule-nav">
            <a href="{{ route('schedule.index', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}" class="btn-nav">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="schedule-month-label">{{ $monthName }}</span>
            <a href="{{ route('schedule.index', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}" class="btn-nav">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('schedule.index', ['year' => now()->year, 'month' => now()->month]) }}#today-col" id="todayBtn"  class="btn btn-sm btn-outline-primary">
                <i class="bi bi-calendar-event"></i> ថ្ងៃនេះ
            </a>
            <a href="{{ route('schedule.export', ['year' => $year, 'month' => $month, 'format' => 'html']) }}" target="_blank" class="btn btn-sm btn-outline-success">
                <i class="bi bi-calendar-week"></i> Calendar View / PDF
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportCalendarPDF()">
                <i class="bi bi-printer"></i> Print Grid
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#telegramAlertModal">
                <i class="bi bi-telegram"></i> Alert Telegram
            </button>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#urgentTaskModal">
                <i class="bi bi-exclamation-triangle-fill"></i> Urgent Task
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#downtimeModal">
                <i class="bi bi-tools"></i> Machine Downtime
            </button>
            <a href="{{ route('schedule.delay-report', ['year'=>$year,'month'=>$month]) }}" class="btn btn-sm btn-outline-warning" target="_blank">
                <i class="bi bi-journal-text"></i> Delay Log
            </a>
            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#copyMonthModal">
                <i class="bi bi-clipboard-plus"></i> Copy to Next Month
            </button>
            <form action="{{ route('schedule.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('⚠️ ពិតជាចង់ជម្រះទិន្នន័យទាំងអស់សម្រាប់ខែនេះ?')">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash3"></i> Clear Month
                </button>
            </form>
        </div>
    </div>

    {{-- INFO PANELS: Today + Tomorrow + Progress --}}
    <div class="schedule-info-bar">
        {{-- Today's Tasks --}}
        <div class="info-card info-today">
            <div class="info-card-icon"><i class="bi bi-lightning-fill"></i></div>
            <div class="info-card-content">
                <strong>ថ្ងៃនេះ ({{ now()->format('d/m') }})</strong>
                @if($todayTasks->count() > 0)
                    <div class="info-tasks">
                        @foreach($todayTasks as $t)
                            <span class="info-task-badge">{{ $t->process }}: {{ $t->task }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted" style="font-size:.75rem;">គ្មានកិច្ចការ</span>
                @endif
            </div>
        </div>

        {{-- Tomorrow --}}
        <div class="info-card info-tomorrow">
            <div class="info-card-icon"><i class="bi bi-sunrise"></i></div>
            <div class="info-card-content">
                <strong>ថ្ងៃស្អែក ({{ now()->addDay()->format('d/m') }})</strong>
                @if($tomorrowTasks->count() > 0)
                    <div class="info-tasks">
                        @foreach($tomorrowTasks as $t)
                            <span class="info-task-badge">{{ $t->process }}: {{ $t->task }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted" style="font-size:.75rem;">គ្មានកិច្ចការ</span>
                @endif
            </div>
        </div>

        {{-- Progress --}}
        <div class="info-card info-progress">
            <div class="info-card-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="info-card-content">
                <strong>ខែនេះ</strong>
                @php
                    $daysLeft = $daysInMonth - now()->day;
                    $totalTasks = \App\Models\ProductionSchedule::where('year', $year)->where('month', $month)->whereNotNull('task')->count();
                    $pastTasks = \App\Models\ProductionSchedule::where('year', $year)->where('month', $month)->where('day', '<', now()->day)->whereNotNull('task')->count();
                @endphp
                <span style="font-size:.72rem;">📋 {{ $totalTasks }} កិច្ចការសរុប</span>
                <span style="font-size:.72rem;">✅ {{ $pastTasks }} បានឆ្លងកាត់</span>
                <span style="font-size:.72rem;">⏳ នៅសល់ {{ $daysLeft }} ថ្ងៃ</span>
            </div>
        </div>
    </div>

    {{-- SCROLLABLE GRID --}}
    <div class="schedule-grid-wrapper">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th class="col-process">
                        Process \ Date
                    </th>
                    @foreach($days as $dayInfo)
                        @php $isTodayHeader = ($year == now()->year && $month == now()->month && $dayInfo['day'] == now()->day); @endphp
                        <th class="{{ in_array($dayInfo['dow'], [0, 6]) ? 'weekend' : '' }} {{ $isTodayHeader ? 'today' : '' }}"
                            {!! $isTodayHeader ? 'id="today-col"' : '' !!}>
                            {{ $dayInfo['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($processes as $process)
                    @php
                        $procSchedules = $schedules->get($process, collect());
                        $procByDay = $procSchedules->keyBy('day');
                        $color = $processColors[$process] ?? '#475569';
                    @endphp
                    <tr class="process-row-{{ Str::slug($process) }}">
                        <td class="col-process">
                            <span style="color: {{ $color }}; font-weight: 700;">{{ $process }}</span>
                        </td>
                        @foreach($days as $dayInfo)
                            @php
                                $d = $dayInfo['day'];
                                $entry = $procByDay->get($d);
                                $isWeekend = in_array($dayInfo['dow'], [0, 6]);
                                $isToday = ($year == now()->year && $month == now()->month && $d == now()->day);
                                $todayId = $isToday ? 'id="today-col"' : '';
                                $cellClass = 'day-cell';
                                if ($isWeekend) $cellClass .= ' weekend';
                                if ($isToday) $cellClass .= ' today';
                            @endphp
                            <td class="{{ $cellClass }}"
                                data-process="{{ $process }}"
                                data-day="{{ $d }}"
                                data-task="{{ $entry->task ?? '' }}"
                                data-note="{{ $entry->note ?? '' }}"
                                data-color="{{ $entry->color ?? '' }}"
                                onclick="openCellModal(this)">
                                @if($entry && $entry->task)
                                    @foreach(explode(',', $entry->task) as $singleTask)
                                        @if(trim($singleTask))
                                            <span class="cell-task" style="background: {{ $entry->color ?: $color }}; color: #fff;">
                                                {{ trim($singleTask) }}
                                            </span>
                                        @endif
                                    @endforeach
                                @endif
                                @if($entry && $entry->note)
                                    <span class="cell-note">{{ $entry->note }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- LEGEND --}}
    <div class="schedule-legend">
        <span class="legend-title">LEGEND — Stage Colours:</span>
        @foreach($processColors as $proc => $clr)
            <span class="legend-item">
                <span class="legend-dot" style="background: {{ $clr }};"></span>
                {{ $proc }}
            </span>
        @endforeach
        <span class="legend-item">
            <span class="legend-dot" style="background: #ef4444; outline: 2px solid #ef4444; outline-offset: 1px;"></span>
            Today ►
        </span>
        <span class="legend-item">
            <span class="legend-dot" style="background: #fef3c7; border: 1px solid #d1d5db;"></span>
            Weekend
        </span>
    </div>
</div>

{{-- EDIT CELL MODAL --}}
<div class="modal fade modal-cell-edit" id="cellModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
            <div class="modal-header" style="background: #f8fafc; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h6 class="modal-title" id="cellModalTitle">
                    <i class="bi bi-pencil-square text-primary"></i>
                    កែប្រែកាលវិភាគ
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cellForm" method="POST" action="{{ route('schedule.store') }}">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="process" id="cellProcess">
                <input type="hidden" name="day" id="cellDay">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ដំណើរការ (Process)</label>
                        <input type="text" class="form-control" id="cellProcessDisplay" readonly style="background: #f1f5f9;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ថ្ងៃ (Day)</label>
                        <input type="text" class="form-control" id="cellDayDisplay" readonly style="background: #f1f5f9;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">កិច្ចការ (Task)</label>
                        <input type="text" class="form-control" name="task" id="cellTask"
                               placeholder="e.g. Listening Textbook, Reading Workbook (comma = multiple)"
                               list="taskSuggestions">
                        <datalist id="taskSuggestions">
                            <option value="All Cover">
                            <option value="Listening Textbook">
                            <option value="Listening Workbook">
                            <option value="Reading Textbook">
                            <option value="Reading Workbook">
                            <option value="Writing Textbook">
                            <option value="Writing Workbook">
                            <option value="Song">
                            <option value="Folktale">
                            <option value="Grammar">
                            <option value="Test Book">
                            <option value="Teacher Book">
                            <option value="Level 1">
                            <option value="Level 2">
                            <option value="Level 3">
                            <option value="Level 4">
                            <option value="Level 5">
                            <option value="Level 6">
                            <option value="Delivery">
                            <option value="Maintenance">
                        </datalist>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem">
                            <i class="bi bi-info-circle"></i> Use <strong>comma (,)</strong> to add multiple tasks: <code>Listening Textbook, Reading Workbook, Writing</code>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">កំណត់ចំណាំ (Note)</label>
                        <input type="text" class="form-control" name="note" id="cellNote"
                               placeholder="e.g. ថ្ងៃអាទិត្យ, ថ្ងៃឈប់សម្រាក...">
                    </div>
                    {{-- SPAN: copy task across multiple consecutive days --}}
                    <div class="mb-3">
                        <label class="form-label">Span (days)</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="number" class="form-control" name="span_days" id="cellSpanDays"
                                   min="1" max="30" value="1"
                                   style="width:80px;font-family:var(--font-latin);font-weight:700">
                            <span style="font-size:.78rem;color:var(--text-muted)">
                                Set &gt; 1 to copy this task across multiple consecutive days
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ពណ៌ (Color)</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($processColors as $proc => $clr)
                                <label class="color-option" style="cursor:pointer;">
                                    <input type="radio" name="color" value="{{ $clr }}" class="d-none color-radio">
                                    <span class="legend-dot" style="background: {{ $clr }}; width:24px; height:24px; display:block; border-radius:4px; border: 2px solid transparent;" title="{{ $proc }}"></span>
                                </label>
                            @endforeach
                            <label class="color-option" style="cursor:pointer;">
                                <input type="radio" name="color" value="" class="d-none color-radio" checked>
                                <span class="legend-dot" style="background: #e2e8f0; width:24px; height:24px; display:block; border-radius:4px; border: 2px solid transparent;" title="Default"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCell">
                        <i class="bi bi-trash3"></i> ជម្រះ
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check-lg"></i> រក្សាទុក
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- COPY TO NEXT MONTH MODAL --}}
<div class="modal fade" id="copyMonthModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
            <div class="modal-header" style="background: #fef3c7; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h6 class="modal-title">
                    <i class="bi bi-clipboard-plus text-warning"></i>
                    Copy ទៅខែបន្ទាប់
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('schedule.copy') }}" method="POST">
                @csrf
                <input type="hidden" name="from_year" value="{{ $year }}">
                <input type="hidden" name="from_month" value="{{ $month }}">
                <input type="hidden" name="to_year" value="{{ $nextDate->year }}">
                <input type="hidden" name="to_month" value="{{ $nextDate->month }}">
                <div class="modal-body" style="padding: 1.5rem;">
                    <p style="font-size:.85rem; margin-bottom: 1rem;">
                        Copy កាលវិភាគពី <strong>{{ $monthName }}</strong> ទៅ <strong>{{ $nextDate->translatedFormat('F Y') }}</strong>?
                    </p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="overwrite" id="copyOverwrite">
                        <label class="form-check-label" for="copyOverwrite" style="font-size:.8rem;">
                            សរសេរជាន់លើទិន្នន័យចាស់ (Overwrite existing)
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="bi bi-clipboard-plus"></i> Copy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TELEGRAM ALERT MODAL (select group + day) --}}
<div class="modal fade" id="telegramAlertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
            <div class="modal-header" style="background: #e0f7fa; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h6 class="modal-title">
                    <i class="bi bi-telegram text-info"></i>
                    ផ្ញើការជូនដំណឹងទៅ Telegram
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('schedule.alert') }}" method="POST">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600; font-size:.82rem;">ជ្រើសរើសថ្ងៃ (Select Day)</label>
                        <select name="day" class="form-select">
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                <option value="{{ $d }}" {{ $d == now()->day ? 'selected' : '' }}>
                                    {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}
                                    @if($d == now()->day) — ថ្ងៃនេះ @endif
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600; font-size:.82rem;">ជ្រើសរើស Group (Select Telegram Group)</label>
                        @php $telegramGroups = \App\Models\TelegramGroup::all(); @endphp
                        @if($telegramGroups->count() > 0)
                            <select name="group_id" class="form-select">
                                <option value="all">📢 ផ្ញើទៅគ្រប់ Group ទាំងអស់</option>
                                @foreach($telegramGroups as $group)
                                    <option value="{{ $group->id }}">
                                        {{ $group->displayLabel() }}
                                        @if($group->purpose) ({{ $group->purpose }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert alert-warning mb-0" style="font-size:.8rem;">
                                <i class="bi bi-exclamation-triangle"></i>
                                មិនទាន់មាន Telegram Group។ សូមទៅ <a href="{{ route('telegram.setup') }}">ការកំណត់ Telegram</a> ដើម្បីបន្ថែម។
                            </div>
                        @endif
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-weight:600; font-size:.82rem;">មើលជាមុន (Preview)</label>
                        <div class="p-2 rounded" style="background:#f1f5f9; font-size:.75rem; max-height:150px; overflow-y:auto;" id="alertPreview">
                            <em class="text-muted">ជ្រើសរើសថ្ងៃដើម្បីមើលកិច្ចការ...</em>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-info text-white" {{ $telegramGroups->count() == 0 ? 'disabled' : '' }}>
                        <i class="bi bi-send"></i> ផ្ញើ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



{{-- URGENT TASK MODAL --}}
<div class="modal fade" id="urgentTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header" style="background:#fee2e2;border-radius:16px 16px 0 0">
                <h6 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger"></i> Urgent Task / ការងារបន្ទាន់</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Mode selector --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Mode</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="urgentMode" id="modeNew" value="new" checked onchange="toggleUrgentMode()">
                            <label class="form-check-label" for="modeNew">
                                <strong>New Urgent Work</strong> — New job arrives, push planned work forward
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="urgentMode" id="modeExisting" value="existing" onchange="toggleUrgentMode()">
                            <label class="form-check-label" for="modeExisting">
                                <strong>Existing Work is Urgent</strong> — Move already-planned work to an earlier day
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Process *</label>
                        <select class="form-select" id="urgentProcess">
                            <option value="Design">Design</option>
                            <option value="Press" selected>Press</option>
                            <option value="Folding">Folding</option>
                            <option value="Gathering">Gathering</option>
                            <option value="Staple">Staple</option>
                            <option value="Binding">Binding</option>
                            <option value="Cutting">Cutting</option>
                            <option value="Packaging">Packaging</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="urgentDayWrap">
                        <label class="form-label" id="urgentDayLabel">Start Day (in this month) *</label>
                        <input type="number" class="form-control" id="urgentDay" value="{{ now()->day }}"
                               min="1" max="{{ $daysInMonth }}" style="font-family:var(--font-latin)">
                    </div>
                </div>

                {{-- New mode fields --}}
                <div id="newModeFields">
                    <div class="mb-3">
                        <label class="form-label">Task Name *</label>
                        <input type="text" class="form-control" id="urgentName"
                               placeholder="e.g. Emergency Reprint Level 1" list="taskSuggestions2">
                        <datalist id="taskSuggestions2">
                            <option value="Listening Textbook"><option value="Listening Workbook">
                            <option value="Reading Textbook"><option value="Reading Workbook">
                            <option value="Writing Textbook"><option value="Test Book">
                            <option value="All Cover"><option value="Song">
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (working days) *</label>
                        <input type="number" class="form-control" id="urgentDuration" value="1" min="1" max="30"
                               style="font-family:var(--font-latin);width:100px">
                    </div>
                </div>

                {{-- Existing mode fields --}}
                <div id="existingModeFields" style="display:none">
                    <div class="mb-3">
                        <label class="form-label">Which task to make urgent? *</label>
                        <input type="text" class="form-control" id="urgentExistingName"
                               placeholder="Type exact task name as on the grid (e.g. Listening Textbook)">
                        <div class="form-text">This task will be moved to the day above. Work between that day and its current position will shift forward.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason / Why urgent?</label>
                    <input type="text" class="form-control" id="urgentReason"
                           placeholder="e.g. Customer order, Deadline moved up">
                </div>

                <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:.75rem;font-size:.82rem;color:#991b1b">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Auto-shift:</strong> Tasks on the same process that are displaced will automatically move to the next available working day, and a delay log is recorded for the monthly report.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="submitUrgentTask()">
                    <i class="bi bi-exclamation-triangle-fill"></i> Apply Urgent
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MACHINE DOWNTIME MODAL --}}
<div class="modal fade" id="downtimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header" style="background:#fef3c7;border-radius:16px 16px 0 0">
                <h6 class="modal-title"><i class="bi bi-tools text-warning"></i> Machine Downtime / ម៉ាស៊ីនខូច</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Process (Row) *</label>
                        <select class="form-select" id="downtimeProcess">
                            <option value="Design">Design</option>
                            <option value="Press" selected>Press</option>
                            <option value="Folding">Folding</option>
                            <option value="Gathering">Gathering</option>
                            <option value="Staple">Staple</option>
                            <option value="Binding">Binding</option>
                            <option value="Cutting">Cutting</option>
                            <option value="Packaging">Packaging</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="form-text">Which production row is affected?</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Day (in this month) *</label>
                        <input type="number" class="form-control" id="downtimeDay" value="{{ now()->day }}"
                               min="1" max="{{ $daysInMonth }}" style="font-family:var(--font-latin)">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Downtime Duration (working days) *</label>
                        <input type="number" class="form-control" id="downtimeDays" value="1" min="1" max="30"
                               style="font-family:var(--font-latin)">
                        <div class="form-text">1 day = 1 full working day blocked</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Machine (for record)</label>
                        <select class="form-select" id="downtimeMachine">
                            <option value="">— None / Unknown —</option>
                            @foreach(\App\Models\Machine::all() as $m)
                                <option value="{{ $m->code }}">{{ $m->code }} — {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason *</label>
                    <input type="text" class="form-control" id="downtimeReason"
                           placeholder="e.g. Press breakdown, Maintenance, Power outage">
                </div>
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:.75rem;font-size:.82rem;color:#92400e">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Cascade shift:</strong> ALL tasks on the selected process row from this day forward will shift by the downtime duration. A delay log entry is recorded for each shifted task.
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('schedule.delay-report', ['year'=>$year,'month'=>$month]) }}" class="btn btn-sm btn-outline-secondary me-auto" target="_blank">
                    <i class="bi bi-journal-text"></i> View Delay Log
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning btn-sm" onclick="submitDowntime()">
                    <i class="bi bi-tools"></i> Apply Downtime
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
{{-- Toast handled by layout --}}
@endif

@if(session('error'))
{{-- Toast handled by layout --}}
@endif

<script>
    // Cell edit modal
    function openCellModal(cell) {
        const process = cell.dataset.process;
        const day = cell.dataset.day;
        const task = cell.dataset.task;
        const note = cell.dataset.note;
        const color = cell.dataset.color;

        document.getElementById('cellProcess').value = process;
        document.getElementById('cellDay').value = day;
        document.getElementById('cellProcessDisplay').value = process;
        document.getElementById('cellDayDisplay').value = day + '/{{ str_pad($month, 2, "0", STR_PAD_LEFT) }}/{{ $year }}';
        document.getElementById('cellTask').value = task;
        document.getElementById('cellNote').value = note;

        document.querySelectorAll('.color-radio').forEach(r => {
            r.checked = (r.value === color);
            r.closest('.color-option').querySelector('.legend-dot').style.borderColor =
                (r.value === color) ? '#4f46e5' : 'transparent';
        });

        const modal = new bootstrap.Modal(document.getElementById('cellModal'));
        modal.show();
    }

    // Color selection visual feedback
    document.querySelectorAll('.color-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.color-option .legend-dot').forEach(dot => {
                dot.style.borderColor = 'transparent';
            });
            this.closest('.color-option').querySelector('.legend-dot').style.borderColor = '#4f46e5';
        });
    });

    // Clear cell
    document.getElementById('btnClearCell').addEventListener('click', function() {
        document.getElementById('cellTask').value = '';
        document.getElementById('cellNote').value = '';
        document.querySelectorAll('.color-radio').forEach(r => r.checked = r.value === '');
        document.getElementById('cellForm').submit();
    });

    // Print / Export PDF — opens browser print dialog (save as PDF)
    function exportCalendarPDF() {
        const grid = document.querySelector('.schedule-container');
        const printWin = window.open('', '_blank');
        printWin.document.write(`
            <html>
            <head>
                <title>Production Schedule — {{ $monthName }}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; }
                    h1 { font-size: 18px; margin-bottom: 10px; text-align: center; }
                    .subtitle { text-align: center; font-size: 12px; color: #666; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; font-size: 9px; }
                    th, td { border: 1px solid #ccc; padding: 4px 3px; text-align: center; }
                    th { background: #f0f0f0; font-weight: 600; }
                    td.process { text-align: left; font-weight: 700; background: #fafafa; width: 80px; }
                    .task-badge { padding: 1px 4px; border-radius: 3px; color: #fff; font-size: 8px; display: inline-block; }
                    .weekend { background: #fffde7; }
                    @media print {
                        @page { size: landscape; margin: 10mm; }
                    }
                </style>
            </head>
            <body>
                <h1>📅 Production Schedule — {{ $monthName }}</h1>
                <p class="subtitle">Printing Tracker | Generated: ${new Date().toLocaleDateString()}</p>
                ${grid.querySelector('.schedule-grid-wrapper').innerHTML}
                <script>window.print(); window.close();<\/script>
            </body>
            </html>
        `);
        printWin.document.close();
    }

    // Telegram alert preview: show tasks for selected day
    @php
        $allMonthTasks = \App\Models\ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->whereNotNull('task')
            ->get()
            ->groupBy('day')
            ->map(fn($tasks) => $tasks->map(fn($t) => $t->process . ': ' . $t->task)->toArray());
    @endphp
    const monthTasks = @json($allMonthTasks);

    document.querySelector('[name="day"]')?.addEventListener('change', function() {
        const day = this.value;
        const preview = document.getElementById('alertPreview');
        const tasks = monthTasks[day];
        if (tasks && tasks.length > 0) {
            preview.innerHTML = tasks.map(t => `<div>• ${t}</div>`).join('');
        } else {
            preview.innerHTML = '<em class="text-muted">គ្មានកិច្ចការសម្រាប់ថ្ងៃនេះ</em>';
        }
    });

    // Trigger preview on load
    document.addEventListener('DOMContentLoaded', function() {
        const daySelect = document.querySelector('[name="day"]');
        if (daySelect) daySelect.dispatchEvent(new Event('change'));
    });

// ── Scroll to Today column on page load ──────────────────
document.addEventListener('DOMContentLoaded', function() {
    const todayCol = document.getElementById('today-col');
    if (todayCol) {
        const wrapper = document.querySelector('.schedule-grid-wrapper');
        if (wrapper) {
            // Calculate scroll position: center today in viewport
            const colLeft = todayCol.offsetLeft;
            const wrapperWidth = wrapper.clientWidth;
            const scrollTo = colLeft - (wrapperWidth / 3);
            wrapper.scrollLeft = Math.max(0, scrollTo);
        }
    }
});


// ── Urgent Task — operates on production_schedules grid ──────────────────
function toggleUrgentMode() {
    const mode = document.querySelector('input[name="urgentMode"]:checked').value;
    document.getElementById('newModeFields').style.display = (mode === 'new') ? '' : 'none';
    document.getElementById('existingModeFields').style.display = (mode === 'existing') ? '' : 'none';
    document.getElementById('urgentDayLabel').textContent =
        (mode === 'existing') ? 'Move TO this day *' : 'Start Day (in this month) *';
}

function submitUrgentTask() {
    const mode    = document.querySelector('input[name="urgentMode"]:checked').value;
    const process = document.getElementById('urgentProcess').value;
    const day     = parseInt(document.getElementById('urgentDay').value);
    const reason  = document.getElementById('urgentReason').value;

    if (!process || !day) {
        showToast('warning', 'Please select process and day');
        return;
    }

    let taskName = '';
    let duration = 1;

    if (mode === 'new') {
        taskName = document.getElementById('urgentName').value.trim();
        duration = parseInt(document.getElementById('urgentDuration').value) || 1;
        if (!taskName) { showToast('warning', 'Please enter a task name'); return; }
    } else {
        taskName = document.getElementById('urgentExistingName').value.trim();
        if (!taskName) { showToast('warning', 'Please enter the task name to make urgent'); return; }
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/schedule/urgent';

    const fields = {
        _token:        document.querySelector('meta[name="csrf-token"]').content,
        year:          '{{ $year }}',
        month:         '{{ $month }}',
        process:       process,
        urgent_day:    day,
        duration_days: duration,
        task_name:     taskName,
        note:          'URGENT',
        reason:        reason,
        mode:          mode,
    };

    Object.entries(fields).forEach(([k, v]) => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = v;
        form.appendChild(inp);
    });

    document.body.appendChild(form);
    form.submit();
}

// ── Machine Downtime — operates on production_schedules grid ──────────────
function submitDowntime() {
    const process  = document.getElementById('downtimeProcess').value;
    const day      = parseInt(document.getElementById('downtimeDay').value);
    const days     = parseInt(document.getElementById('downtimeDays').value) || 1;
    const reason   = document.getElementById('downtimeReason').value.trim();
    const machine  = document.getElementById('downtimeMachine').value;

    if (!process || !day) {
        showToast('warning', 'Please select process and start day');
        return;
    }
    if (!reason) {
        showToast('warning', 'Please provide a reason for the downtime');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/schedule/downtime';

    const fields = {
        _token:         document.querySelector('meta[name="csrf-token"]').content,
        year:           '{{ $year }}',
        month:          '{{ $month }}',
        process:        process,
        downtime_day:   day,
        downtime_days:  days,
        reason:         (machine ? machine + ' — ' : '') + reason,
    };

    Object.entries(fields).forEach(([k, v]) => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = v;
        form.appendChild(inp);
    });

    document.body.appendChild(form);
    form.submit();
}

</script>
@endsection
