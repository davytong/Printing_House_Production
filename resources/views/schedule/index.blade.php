@extends('layouts.app')

@section('title', 'កាលវិភាគផលិតកម្មប្រចាំខែ — Smart Production Planner')

@section('content')
@php
    use Carbon\Carbon;
    $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
    $monthNameKh = Carbon::createFromDate($year, $month, 1)->locale('km')->translatedFormat('F Y');

    // Process Colors
    $processColors = [
        'Design'     => '#3b82f6',
        'Prepress'   => '#6366f1',
        'Press'      => '#ef4444',
        'Digital'    => '#8b5cf6',
        'Folding'    => '#d946ef',
        'Gathering'  => '#f59e0b',
        'Staple'     => '#06b6d4',
        'Binding'    => '#ec4899',
        'Cutting'    => '#14b8a6',
        'Lamination' => '#e11d48',
        'Packaging'  => '#10b981',
        'Delivery'   => '#f97316',
        'Other'      => '#64748b',
    ];

    // Process Icons
    $processIcons = [
        'Design'     => 'bi-palette-fill',
        'Prepress'   => 'bi-laptop-fill',
        'Press'      => 'bi-printer-fill',
        'Digital'    => 'bi-cpu-fill',
        'Folding'    => 'bi-layers-fill',
        'Gathering'  => 'bi-collection-fill',
        'Staple'     => 'bi-pin-angle-fill',
        'Binding'    => 'bi-journal-bookmark-fill',
        'Cutting'    => 'bi-scissors',
        'Lamination' => 'bi-shield-check',
        'Packaging'  => 'bi-box-seam-fill',
        'Delivery'   => 'bi-truck',
        'Other'      => 'bi-gear-fill',
    ];

    // Build day info
    $days = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = Carbon::createFromDate($year, $month, $d);
        $days[] = [
            'day' => $d,
            'dow' => $date->dayOfWeek, // 0=Sun, 6=Sat
            'date' => $date->format('d/m/Y'),
            'label' => $date->format('D, d/m'),
            'full_label' => $date->format('D, d/m/Y'),
        ];
    }

    $prevDate = Carbon::createFromDate($year, $month, 1)->subMonth();
    $nextDate = Carbon::createFromDate($year, $month, 1)->addMonth();
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap');

.page-content { max-width: 100% !important; padding: 0.75rem 1.25rem; }

.schedule-container {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
    overflow: hidden;
    font-family: var(--font-khmer, 'Kantumruy Pro', 'Outfit', sans-serif);
}

.schedule-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.schedule-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: .6rem;
    letter-spacing: -0.02em;
}

.schedule-nav {
    display: flex;
    align-items: center;
    gap: .5rem;
    background: #f1f5f9;
    padding: 0.25rem 0.4rem;
    border-radius: 12px;
}

.schedule-nav .btn-nav {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: transparent;
    border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #64748b;
}
.schedule-nav .btn-nav:hover {
    background: #ffffff;
    color: #4f46e5;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.schedule-month-label {
    font-size: 0.95rem;
    font-weight: 700;
    min-width: 140px;
    text-align: center;
    color: #334155;
}

/* ── ATTENTION / DELAY BANNER ── */
.delay-banner {
    background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
    border-bottom: 1px solid #fecdd3;
    padding: 0.65rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    color: #9f1239;
    font-size: 0.85rem;
}

/* ── SMART ACTION BUTTON ── */
.btn-smart-wizard {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #ffffff !important;
    border: none;
    font-weight: 700;
    padding: 0.55rem 1.25rem;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.88rem;
}
.btn-smart-wizard:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
    color: #ffffff;
}

/* ── SCROLLABLE GRID ── */
.schedule-grid-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 280px);
    position: relative;
    background: #f8fafc;
    -webkit-overflow-scrolling: touch;
}
.schedule-grid-wrapper::-webkit-scrollbar { height: 10px; width: 10px; }
.schedule-grid-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }
.schedule-grid-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
.schedule-grid-wrapper::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

.schedule-table {
    width: max-content;
    min-width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: .82rem;
}

.schedule-table th,
.schedule-table td {
    border: 1px solid rgba(0, 0, 0, 0.04);
    padding: 0;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}

.schedule-table th.col-process,
.schedule-table td.col-process {
    position: sticky;
    left: 0;
    z-index: 10;
    background: #ffffff;
    min-width: 110px;
    padding: .6rem .85rem;
    font-weight: 700;
    text-align: left;
    border-right: 2px solid rgba(0,0,0,0.06);
    box-shadow: 3px 0 8px -2px rgba(0,0,0,0.04);
}

.schedule-table th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #f8fafc;
    padding: .55rem .7rem;
    font-weight: 700;
    font-size: .76rem;
    color: #475569;
    border-bottom: 2px solid rgba(0,0,0,0.06);
}

.schedule-table th.col-process { z-index: 15; background: #f1f5f9; }
.schedule-table th.weekend { background: repeating-linear-gradient(45deg, #f8fafc, #f8fafc 6px, #f1f5f9 6px, #f1f5f9 12px); color: #94a3b8; }
.schedule-table th.today { background: #eff6ff; color: #1d4ed8; border-bottom: 2px solid #3b82f6; }

/* ── DAY CELLS ── */
.day-cell {
    min-width: 100px;
    height: 58px;
    position: relative;
    cursor: pointer;
    background: #ffffff;
    transition: background 0.15s ease, box-shadow 0.15s ease;
    padding: 3px 4px;
}
.day-cell:hover {
    background: #f8fafc !important;
    box-shadow: inset 0 0 0 2px #4f46e5;
    z-index: 2;
}

.day-cell.weekend { background: repeating-linear-gradient(45deg, #ffffff, #ffffff 6px, #f8fafc 6px, #f8fafc 12px); }
.day-cell.today { background: #f0fdf4 !important; }

.cell-task {
    display: block;
    margin: 1px 0;
    padding: 3px 6px;
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 700;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-shadow: 0 1px 2px rgba(0,0,0,0.15);
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
}

.cell-qty-chip {
    display: inline-block;
    background: rgba(0,0,0,0.18);
    border-radius: 4px;
    padding: 1px 4px;
    font-size: 0.65rem;
    font-weight: 600;
    margin-left: 3px;
}

.cell-lock-icon { position: absolute; top: 2px; right: 2px; font-size: 0.68rem; color: #64748b; }
.cell-note { display: block; font-size: .66rem; color: #64748b; font-style: italic; }

/* ── INFO PANELS ── */
.schedule-info-bar {
    display: flex;
    gap: 0.75rem;
    padding: 0.85rem 1.5rem;
    background: #ffffff;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    flex-wrap: wrap;
}

.info-card {
    flex: 1;
    min-width: 230px;
    background: #f8fafc;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border: 1px solid #e2e8f0;
}
.info-card-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.info-today .info-card-icon { background: #eff6ff; color: #3b82f6; }
.info-tomorrow .info-card-icon { background: #fef3c7; color: #d97706; }
.info-progress .info-card-icon { background: #f3e8ff; color: #9333ea; }

.info-card-content { flex: 1; min-width: 0; font-size: 0.82rem; }
.info-tasks { display: flex; flex-wrap: wrap; gap: 4px; }
.info-task-badge { color: #fff; border-radius: 4px; padding: 1px 6px; font-size: .7rem; font-weight: 600; }

/* ── LEGEND ── */
.schedule-legend {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.65rem;
    padding: 0.85rem 1.5rem;
    background: #ffffff;
    border-top: 1px solid rgba(0,0,0,0.04);
    font-size: .74rem;
}
.legend-title { font-weight: 700; color: #475569; }
.legend-item { display: inline-flex; align-items: center; gap: .3rem; color: #64748b; font-weight: 500; }
.legend-dot { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }

/* ── BATCH TOOLBAR ── */
.batch-toolbar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: #1e293b;
    color: #fff;
    border-radius: 50px;
    padding: 0.5rem 1.5rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    display: none;
    align-items: center;
    gap: 0.75rem;
    z-index: 1000;
}
body.batch-mode .batch-toolbar { display: flex; }
body.batch-mode .batch-cb { display: block !important; }

/* ── MODAL SMART PLAN & PIPELINE TIMELINE ── */
.modal-smart-plan .modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 25px 60px -15px rgba(79, 70, 229, 0.25);
}
.modal-smart-plan .modal-header {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #ffffff;
    border-radius: 20px 20px 0 0;
    padding: 1.2rem 1.6rem;
}
.modal-smart-plan .modal-header .btn-close { filter: brightness(0) invert(1); }

.plan-preview-box {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.15rem;
    margin-top: 1rem;
}

/* Pipeline Flow Stepper */
.pipeline-flow {
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 8px;
    margin-bottom: 12px;
    -webkit-overflow-scrolling: touch;
}
.pipeline-step {
    flex: 0 0 auto;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #334155;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
}
.pipeline-arrow { color: #cbd5e1; font-size: 0.85rem; flex-shrink: 0; }

/* Mobile Responsive Cards for Preview */
.stage-mobile-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #4f46e5;
    border-radius: 10px;
    padding: 0.75rem 0.9rem;
    margin-bottom: 0.6rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

/* Batch Book Quick Pick Chips */
.book-chip-card {
    flex: 0 0 auto;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 145px;
    font-size: 0.76rem;
    user-select: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.book-chip-card:hover {
    border-color: #6366f1;
    background: #f5f3ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(99, 102, 241, 0.12);
}
.book-chip-card.active {
    border-color: #4f46e5 !important;
    background: #eef2ff !important;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25) !important;
}
.book-chip-title {
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}
.book-chip-meta {
    font-size: 0.68rem;
    color: #64748b;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 3px;
}

/* ── Task Builder — Mobile-first grid ────────────────── */
.tb-fields-grid {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    align-items: flex-end;
}
.tb-field-group {
    display: flex;
    flex-direction: column;
    min-width: 70px;
    flex: 1 1 70px;
}
.tb-field-label {
    font-size: .7rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 4px;
    white-space: nowrap;
}
.tb-or-divider {
    color: #94a3b8;
    font-size: 1rem;
    padding-bottom: 8px;
    font-weight: 700;
}
.tb-result {
    min-width: 65px;
    text-align: center;
    font-size: .95rem;
    font-weight: 700;
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    color: #64748b;
    padding: 4px 8px;
    transition: all .2s;
}
@media (max-width: 768px) {
    .page-content { padding: 0.5rem; }
    .schedule-header { padding: 0.75rem; flex-direction: column; align-items: stretch; }
    .schedule-title { font-size: 1.05rem; }
    .schedule-nav { width: 100%; justify-content: space-between; }
    .schedule-info-bar { padding: 0.75rem; flex-direction: column; }
    .info-card { min-width: 100%; }
    .desktop-plan-table { display: none !important; }
    .mobile-plan-cards { display: block !important; }
    .modal-smart-plan .modal-dialog { margin: 0.5rem; }
    .modal-smart-plan .modal-content { border-radius: 14px; }
}

@media (min-width: 769px) {
    .desktop-plan-table { display: table !important; }
    .mobile-plan-cards { display: none !important; }
}
</style>

<div class="schedule-container">
    {{-- HEADER BAR --}}
    <div class="schedule-header">
        <div class="schedule-title">
            <i class="bi bi-calendar3-range-fill text-primary"></i>
            កាលវិភាគផលិតកម្មប្រចាំខែ
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-xs">Smart Planner</span>
        </div>

        {{-- Month Navigator --}}
        <div class="schedule-nav">
            <a href="{{ route('schedule.index', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}" class="btn-nav" title="Previous Month">
                <i class="bi bi-chevron-left"></i>
            </a>
            <span class="schedule-month-label">{{ $monthName }}</span>
            <a href="{{ route('schedule.index', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}" class="btn-nav" title="Next Month">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        {{-- QUICK ACTION BUTTONS --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- PRIMARY 3-STEP SMART WIZARD BUTTON --}}
            <button type="button" class="btn btn-smart-wizard" onclick="openSmartPlanWizard()">
                <i class="bi bi-lightning-charge-fill"></i>
                <span>+ បង្កើតផែនការ (Smart Plan)</span>
            </button>

            {{-- From Print Requests --}}
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#approvedRequestsModal" title="Import approved print requests into schedule">
                <i class="bi bi-inboxes-fill me-1"></i>
                <span class="d-none d-md-inline">From Requests</span>
                @if($approvedRequests->count() > 0)
                    <span class="badge bg-primary text-white ms-1">{{ $approvedRequests->count() }}</span>
                @endif
            </button>

            {{-- Bulk Import --}}
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#bulkImportModal" title="Paste Excel or CSV data">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                <span class="d-none d-md-inline">Import Excel</span>
            </button>

            {{-- Today --}}
            <a href="{{ route('schedule.index', ['year' => now()->year, 'month' => now()->month]) }}#today-col"
               class="btn btn-sm btn-outline-secondary" title="Jump to today">
                <i class="bi bi-calendar-event"></i>
                <span class="d-none d-md-inline ms-1">Today</span>
            </a>

            {{-- Batch Mark Status --}}
            <button type="button" class="btn btn-sm btn-outline-success" id="batchModeBtn"
                    onclick="toggleBatchMode()" title="Batch mark cells as Done / In Progress">
                <i class="bi bi-check2-square"></i>
                <span class="d-none d-md-inline ms-1">Mark</span>
            </button>

            {{-- More Actions Dropdown (Admin / Advanced Settings) --}}
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i>
                    <span class="d-none d-md-inline ms-1">More</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><h6 class="dropdown-header">Configuration & Templates (Admin)</h6></li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#templatesManagerModal">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>Production Templates
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" onclick="openSmartPlanWizard(null, true)">
                            <i class="bi bi-cpu text-info me-2"></i>What-If Simulator
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Overrides & Downtime</h6></li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#urgentTaskModal">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Urgent Override Task
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#downtimeModal">
                            <i class="bi bi-tools text-warning me-2"></i>Delay / Downtime Event
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" onclick="openDelayModal()">
                            <i class="bi bi-journal-text text-warning me-2"></i>Delay & Shift Summary
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Reports & Exports</h6></li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#telegramAlertModal">
                            <i class="bi bi-telegram text-info me-2"></i>Alert Telegram
                        </button>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('schedule.export', ['year' => $year, 'month' => $month, 'format' => 'html']) }}" target="_blank">
                            <i class="bi bi-calendar-week text-success me-2"></i>Printable Calendar / PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('schedule.weekly-report') }}">
                            <i class="bi bi-journal-check text-primary me-2"></i>Weekly Report
                        </a>
                    </li>
                    <li>
                        <button class="dropdown-item" onclick="exportCalendarPDF()">
                            <i class="bi bi-printer me-2"></i>Print Grid
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Maintenance</h6></li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#copyMonthModal">
                            <i class="bi bi-clipboard-plus me-2"></i>Copy Month to Next
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item text-danger" onclick="confirmClearMonth()">
                            <i class="bi bi-trash3 me-2"></i>Clear Month
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Hidden clear form --}}
        <form action="{{ route('schedule.clear') }}" method="POST" id="clearMonthForm">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
        </form>
    </div>

    {{-- ATTENTION REQUIRED / DELAY ALERT BANNER --}}
    @if(isset($delayAlerts) && $delayAlerts->count() > 0)
        <div class="delay-banner">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                <div>
                    <strong>ការយកចិត្តទុកដាក់ (Attention Required):</strong>
                    <span>មានកិច្ចការផលិតកម្មចំនួន {{ $delayAlerts->count() }} កំពុងយឺតជាងផែនការគ្រោងទុក។</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-danger text-white fw-bold" onclick="openDelayModal()">
                    <i class="bi bi-journal-text me-1"></i> មើលការពន្យារពេល (View Delays)
                </button>
            </div>
        </div>
    @endif

    {{-- INFO PANELS --}}
    <div class="schedule-info-bar">
        <div class="info-card info-today">
            <div class="info-card-icon"><i class="bi bi-lightning-fill"></i></div>
            <div class="info-card-content">
                <strong>ថ្ងៃនេះ ({{ now()->format('d/m') }})</strong>
                @if($todayTasks->count() > 0)
                    <div class="info-tasks mt-1">
                        @foreach($todayTasks as $t)
                            @php $clr = $processColors[$t->process] ?? '#475569'; @endphp
                            <span class="info-task-badge" style="background: {{ $clr }};">{{ $t->process }}: {{ $t->task }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted" style="font-size:.75rem;">គ្មានកិច្ចការ</span>
                @endif
            </div>
        </div>

        <div class="info-card info-tomorrow">
            <div class="info-card-icon"><i class="bi bi-sunrise-fill"></i></div>
            <div class="info-card-content">
                <strong>ថ្ងៃស្អែក ({{ now()->addDay()->format('d/m') }})</strong>
                @if($tomorrowTasks->count() > 0)
                    <div class="info-tasks mt-1">
                        @foreach($tomorrowTasks as $t)
                            @php $clr = $processColors[$t->process] ?? '#475569'; @endphp
                            <span class="info-task-badge" style="background: {{ $clr }};">{{ $t->process }}: {{ $t->task }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted" style="font-size:.75rem;">គ្មានកិច្ចការ</span>
                @endif
            </div>
        </div>

        <div class="info-card info-progress">
            <div class="info-card-icon"><i class="bi bi-pie-chart-fill"></i></div>
            <div class="info-card-content">
                <strong>ខែនេះ ({{ $monthName }})</strong>
                @php
                    $daysLeft = $daysInMonth - now()->day;
                    $totalTasks = \App\Models\ProductionSchedule::where('year', $year)->where('month', $month)->whereNotNull('task')->count();
                    $pastTasks = \App\Models\ProductionSchedule::where('year', $year)->where('month', $month)->where('day', '<', now()->day)->whereNotNull('task')->count();
                @endphp
                <div class="d-flex flex-wrap gap-2 mt-1">
                    <span class="badge bg-light text-dark border">📋 {{ $totalTasks }} កិច្ចការ</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">✅ {{ $pastTasks }} បានឆ្លងកាត់</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">⏳ នៅសល់ {{ max(0, $daysLeft) }} ថ្ងៃ</span>
                </div>
            </div>
        </div>
    </div>

    {{-- SCROLLABLE CALENDAR GRID --}}
    <div class="schedule-grid-wrapper" id="scheduleGridWrapper">
        <table class="schedule-table" id="scheduleTable">
            <thead>
                <tr>
                    <th class="col-process">
                        <span class="d-none d-md-inline">Process \ Date</span>
                        <span class="d-md-none">Process</span>
                    </th>
                    @foreach($days as $dayInfo)
                        @php $isTodayHeader = ($year == now()->year && $month == now()->month && $dayInfo['day'] == now()->day); @endphp
                        <th class="{{ $dayInfo['dow'] === 0 ? 'weekend' : '' }} {{ $isTodayHeader ? 'today' : '' }}"
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
                            <span style="color: {{ $color }}; font-weight: 800;">{{ $process }}</span>
                        </td>
                        @foreach($days as $dayInfo)
                            @php
                                $d = $dayInfo['day'];
                                $entry = $procByDay->get($d);
                                $isWeekend = $dayInfo['dow'] === 0;
                                $hasTasks = $entry && $entry->task;
                                $isToday = ($year == now()->year && $month == now()->month && $d == now()->day);
                                $cellClass = 'day-cell';
                                if ($isWeekend && !$hasTasks) $cellClass .= ' weekend';
                                if ($isToday) $cellClass .= ' today';
                                
                                $cellStatus = $entry->status ?? 'planned';
                                $statusOverlay = '';
                                if($cellStatus === 'done') $statusOverlay = 'background:rgba(16,185,129,.08);';
                                elseif($cellStatus === 'in_progress') $statusOverlay = 'background:rgba(245,158,11,.08);';
                                
                                $isDelayed = $entry && $entry->planned_qty > 0 && $d < now()->day && ($entry->actual_qty < $entry->planned_qty);
                                if ($isDelayed) $statusOverlay = 'background:rgba(239,68,68,.08);';
                            @endphp
                            <td class="{{ $cellClass }} status-{{ $entry->status ?? 'planned' }}"
                                data-schedule-id="{{ $entry?->id ?? '' }}"
                                data-process="{{ $process }}"
                                data-day="{{ $d }}"
                                data-task="{{ $entry->task ?? '' }}"
                                data-note="{{ $entry->note ?? '' }}"
                                data-color="{{ $entry->color ?? '' }}"
                                data-status="{{ $entry->status ?? 'planned' }}"
                                data-planned-qty="{{ $entry->planned_qty ?? '' }}"
                                data-actual-qty="{{ $entry->actual_qty ?? 0 }}"
                                data-is-locked="{{ ($entry && $entry->isLocked()) ? '1' : '0' }}"
                                data-job-name="{{ $entry?->job?->name ?? '' }}"
                                onclick="handleCellClick(event, this)"
                                style="{{ $statusOverlay }}">

                                <span class="batch-cb" onclick="event.stopPropagation()" style="display:none;position:absolute;top:2px;left:2px;">
                                    <input type="checkbox" class="form-check-input batch-check"
                                           data-process="{{ $process }}" data-day="{{ $d }}"
                                           style="width:14px;height:14px;cursor:pointer;">
                                </span>

                                @if($entry && $entry->isLocked())
                                    <span class="cell-lock-icon" title="Locked"><i class="bi bi-lock-fill"></i></span>
                                @endif

                                @if($isDelayed)
                                    <span style="position:absolute;bottom:2px;right:2px;font-size:0.65rem;" title="Delayed (-{{ $entry->planned_qty - $entry->actual_qty }})">⚠️</span>
                                @endif

                                @if($entry && $entry->task)
                                    @php $taskList = array_filter(array_map('trim', explode(',', $entry->task))); @endphp
                                    @foreach($taskList as $singleTask)
                                        @if($singleTask)
                                            <span class="cell-task" style="background: linear-gradient(135deg, {{ $entry->color ?: $color }}cc, {{ $entry->color ?: $color }}); color: #fff;" title="{{ $singleTask }}">
                                                {{ $singleTask }}
                                                @if($entry->planned_qty > 0)
                                                    <span class="cell-qty-chip">
                                                        @if($entry->actual_qty >= $entry->planned_qty)
                                                            ✓ {{ number_format($entry->actual_qty) }}
                                                        @elseif($entry->actual_qty > 0)
                                                            {{ number_format($entry->actual_qty) }}/{{ number_format($entry->planned_qty) }}
                                                        @else
                                                            {{ number_format($entry->planned_qty) }}
                                                        @endif
                                                    </span>
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                @endif

                                @if($entry && $entry->note && !in_array($entry->note,['URGENT']))
                                    <span class="cell-note" title="{{ $entry->note }}">{{ $entry->note }}</span>
                                @endif

                                @if($cellStatus === 'done')
                                    <span class="status-pip done-pip" title="Done">✅</span>
                                @elseif($cellStatus === 'in_progress')
                                    <span class="status-pip prog-pip" title="In Progress">🔄</span>
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
        <span class="legend-title">LEGEND — Stages:</span>
        @foreach($processColors as $proc => $clr)
            <span class="legend-item">
                <span class="legend-dot" style="background: {{ $clr }};"></span>
                {{ $proc }}
            </span>
        @endforeach
        <span class="legend-item">
            <span class="legend-dot" style="background: #3b82f6; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #3b82f6;"></span>
            Today
        </span>
        <span class="legend-item">
            <span class="legend-dot" style="background: repeating-linear-gradient(45deg, #f8fafc, #f8fafc 4px, #cbd5e1 4px, #cbd5e1 8px); border: 1px solid #94a3b8;"></span>
            Weekend (Off)
        </span>
        <span class="legend-item ms-2">
            <span style="font-size:.7rem;">🔒</span> Locked
        </span>
        <span class="legend-item">
            <span style="font-size:.7rem;">⚠️</span> Delayed Output
        </span>
        <span class="legend-item ms-auto text-muted" style="font-size:0.75rem;">
            💡 <em>Click cell to enter output or double-click to edit</em>
        </span>
    </div>
</div>

{{-- BATCH STATUS TOOLBAR --}}
<div class="batch-toolbar" id="batchToolbar">
    <span id="batchCount" class="fw-bold">0 selected</span>
    <button class="btn btn-sm btn-success" onclick="applyBatchStatus('done')">
        <i class="bi bi-check-circle-fill me-1"></i>Mark Done
    </button>
    <button class="btn btn-sm btn-warning text-dark" onclick="applyBatchStatus('in_progress')">
        <i class="bi bi-arrow-repeat me-1"></i>In Progress
    </button>
    <button class="btn btn-sm btn-secondary" onclick="applyBatchStatus('planned')">
        <i class="bi bi-clock me-1"></i>Planned
    </button>
    <button class="btn btn-sm btn-outline-light ms-2" onclick="toggleBatchMode()">
        <i class="bi bi-x-lg"></i> Exit
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 1: SMART PRODUCTION PLAN (DESKTOP & MOBILE OPTIMIZED)
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade modal-smart-plan" id="smartPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="smartPlanModalTitle">
                        <i class="bi bi-lightning-charge-fill me-2"></i>
                        បង្កើតផែនការផលិតកម្ម (Smart Production Plan)
                    </h5>
                    <small class="text-white-50 fs-xs">៣ ជំហានងាយៗ — ប្រព័ន្ធនឹងរៀបចំកាលវិភាគដោយស្វ័យប្រវត្តិ</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                {{-- QUICK PICK: BATCH SELECTION & FILTERED BOOKS --}}
                <div class="card border-0 bg-light p-3 mb-3" style="border-radius: 16px; border: 1.5px solid #e0e7ff !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-primary fs-xs text-uppercase mb-0">
                            <i class="bi bi-layers-fill me-1"></i> ១. ជ្រើសរើសសៀវភៅតាមបាច់ (Select Book by Batch)
                        </label>
                        <span class="badge bg-indigo-subtle text-primary border border-primary-subtle fs-xs">
                            <i class="bi bi-box-seam me-1"></i> បាច់បច្ចុប្បន្ន: {{ $currentBatch?->name ?? 'Active Batch' }}
                        </span>
                    </div>

                    <div class="row g-2">
                        {{-- Batch Selector --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-xs text-muted fw-semibold mb-1">📦 បាច់ផលិតកម្ម (Batch)</label>
                            <select class="form-select form-select-sm fw-bold border-primary-subtle" id="wizardBatchSelect" onchange="onWizardBatchChanged(this.value)">
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" {{ $batch->id == ($currentBatch?->id ?? '') ? 'selected' : '' }}>
                                        {{ $batch->name }} ({{ $batch->status == 'active' ? '🟢 កំពុងផលិត' : ($batch->status == 'completed' ? '⚪ រួចរាល់' : '🟡 ផ្អាក') }})
                                    </option>
                                @endforeach
                                <option value="all">🌐 បង្ហាញទាំងអស់ (All {{ $books->count() }} Books)</option>
                            </select>
                        </div>

                        {{-- Filtered Book Selector --}}
                        <div class="col-12 col-md-8">
                            <label class="form-label fs-xs text-muted fw-semibold mb-1">📚 សៀវភៅក្នុងបាច់នេះ (Book in Selected Batch)</label>
                            <select class="form-select form-select-sm fw-semibold" id="wizardBookSelect" onchange="onWizardBookSelected(this)">
                                <option value="">— ជ្រើសរើសសៀវភៅ —</option>
                            </select>
                        </div>
                    </div>

                    {{-- Fast 1-Click Book Chips Container --}}
                    <div class="mt-2 pt-2 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <small class="text-muted fs-xs fw-semibold">
                                <i class="bi bi-lightning-fill text-warning me-1"></i> ចុចជ្រើសរើសរហ័ស (1-Click Fast Pick):
                            </small>
                            <small id="batchBookCountBadge" class="text-primary fs-xs fw-bold"></small>
                        </div>
                        <div class="d-flex gap-2 overflow-x-auto py-1" id="wizardBookChipsList" style="scrollbar-width: thin; -webkit-overflow-scrolling: touch;">
                            {{-- Populated dynamically by JS --}}
                        </div>
                    </div>

                    {{-- Approved Print Requests Alternative Selector --}}
                    @if($approvedRequests->isNotEmpty())
                        <div class="mt-2 pt-2 border-top">
                            <div class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap">
                                <small class="text-muted fs-xs fw-semibold text-nowrap">
                                    <i class="bi bi-file-earmark-check-fill text-success me-1"></i> ឬជ្រើសពីពាក្យស្នើសុំ:
                                </small>
                                <select class="form-select form-select-sm flex-grow-1" id="wizardRequestSelect" onchange="onWizardRequestSelected(this)">
                                    <option value="">— ជ្រើសពីពាក្យស្នើសុំដែលបានអនុម័ត (Approved Requests) —</option>
                                    @foreach($approvedRequests as $req)
                                        <option value="{{ $req->id }}" data-title="{{ $req->title }}" data-qty="{{ $req->totalQty() }}" data-due="{{ $req->required_by?->format('Y-m-d') }}" data-priority="{{ $req->priority }}">
                                            [{{ $req->request_code }}] {{ $req->title }} — {{ number_format($req->totalQty()) }} ក្បាល ({{ $req->department ?? 'General' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <input type="hidden" id="planBookId" value="">
                <input type="hidden" id="planBatchId" value="">

                {{-- STEP 1 & 2: THE 4 SIMPLE INPUTS --}}
                <div class="card border-0 bg-light p-3 mb-3" style="border-radius: 14px;">
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label class="form-label fw-bold text-dark mb-1">១. ឈ្មោះកិច្ចការផលិតកម្ម (Job Name) *</label>
                            <input type="text" class="form-control" id="planJobName" placeholder="e.g. Listening Textbook" required oninput="triggerPlanCalculation()">
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-bold text-dark mb-1">២. ចំនួនសរុប (Quantity) *</label>
                            <div class="input-group">
                                <input type="number" class="form-control fw-bold" id="planQuantity" value="10000" min="1" step="500" required oninput="triggerPlanCalculation()">
                                <span class="input-group-text">ក្បាល</span>
                            </div>
                            {{-- Quick Qty Buttons --}}
                            <div class="d-flex gap-1 mt-2 flex-wrap">
                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 fs-xs fw-semibold flex-fill" onclick="setQuickQty(5000)">5,000</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 fs-xs fw-semibold flex-fill" onclick="setQuickQty(10000)">10,000</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 fs-xs fw-semibold flex-fill" onclick="setQuickQty(20000)">20,000</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 fs-xs fw-semibold flex-fill" onclick="setQuickQty(50000)">50,000</button>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">៣. ថ្ងៃចាប់ផ្ដើម (Start Date) *</label>
                            <input type="date" class="form-control" id="planStartDate" value="{{ now()->format('Y-m-d') }}" onchange="triggerPlanCalculation()">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">៤. គំរូដំណើរការ (Template) *</label>
                            <select class="form-select fw-semibold" id="planTemplateSelect" onchange="triggerPlanCalculation()">
                                @forelse($templates as $t)
                                    <option value="{{ $t->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $t->name }} ({{ $t->processes->count() }} ដំណាក់កាល)</option>
                                @empty
                                    <option value="" selected>Textbook (Perfect Binding) (6 ដំណាក់កាល)</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    {{-- Collapsible Optional Settings --}}
                    <details class="mt-3 pt-2 border-top" style="font-size: 0.85rem;">
                        <summary class="text-muted fw-semibold cursor-pointer user-select-none">
                            <i class="bi bi-sliders me-1"></i> ជម្រើសបន្ថែម (More Options: Deadline, Priority, Sunday rush)
                        </summary>
                        <div class="row g-2 mt-2 pt-1">
                            <div class="col-12 col-md-4">
                                <label class="form-label fs-xs fw-bold">ថ្ងៃកំណត់បញ្ចប់ (Due Date)</label>
                                <input type="date" class="form-control form-control-sm" id="planDueDate" onchange="triggerPlanCalculation()">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fs-xs fw-bold">កម្រិតអាទិភាព (Priority)</label>
                                <select class="form-select form-select-sm" id="planPriority" onchange="triggerPlanCalculation()">
                                    <option value="normal" selected>ធម្មតា (Normal)</option>
                                    <option value="high">ខ្ពស់ (High)</option>
                                    <option value="urgent">បន្ទាន់ (Urgent)</option>
                                    <option value="low">ទាប (Low)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch mt-2 mt-md-3">
                                    <input class="form-check-input" type="checkbox" id="planIncludeSundays" onchange="triggerPlanCalculation()">
                                    <label class="form-check-label fs-xs fw-bold text-muted" for="planIncludeSundays">
                                        ធ្វើការថ្ងៃអាទិត្យ (Sundays)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>

                {{-- STEP 3: LIVE PLAN PREVIEW BOX --}}
                <div class="plan-preview-box" id="planPreviewContainer">
                    <div id="planLoadingState" style="display:none;" class="text-center py-3 text-muted">
                        <span class="spinner-border spinner-border-sm text-primary me-2"></span>
                        កំពុងរៀបចំផែនការផលិតកម្ម... (Generating production plan...)
                    </div>

                    <div id="planContentState">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-sm text-uppercase">
                                <i class="bi bi-calendar-check-fill text-primary me-1"></i>
                                ការពិនិត្យមើលផែនការ (Plan Preview)
                            </span>
                            <span id="planStatusBadge" class="badge bg-success">Optimal</span>
                        </div>

                        {{-- Simple Status Alert --}}
                        <div id="planAlertBox" class="mb-3"></div>

                        {{-- Visual Pipeline Stepper --}}
                        <div class="pipeline-flow" id="planPipelineFlow"></div>

                        {{-- Desktop Table View (>= 768px) --}}
                        <div class="table-responsive desktop-plan-table">
                            <table class="table table-sm table-bordered bg-white mb-0 fs-xs" id="planStagesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 140px;">ដំណាក់កាល (Stage)</th>
                                        <th style="min-width: 150px;">កាលបរិច្ឆេទ (Date)</th>
                                        <th class="text-center" style="min-width: 80px;">រយៈពេល</th>
                                        <th style="min-width: 150px;">ចំនួនគ្រោង (Planned Qty)</th>
                                        <th class="text-center" style="min-width: 100px; white-space: nowrap;">ស្ថានភាព</th>
                                    </tr>
                                </thead>
                                <tbody id="planStagesBody">
                                    <tr><td colspan="5" class="text-center py-3 text-muted">កំពុងគណនា...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards View (< 768px) --}}
                        <div class="mobile-plan-cards" id="planMobileCards"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">បោះបង់ (Cancel)</button>
                <button type="button" class="btn btn-primary fw-bold px-4 py-2 flex-fill flex-md-grow-0" id="btnConfirmPlan" onclick="submitSmartPlan()">
                    <i class="bi bi-check-circle-fill me-2"></i> អនុវត្ត & បញ្ចូលក្នុងកាលវិភាគ (Confirm Plan)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 2: APPROVED PRINT REQUESTS QUICK IMPORT
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="approvedRequestsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-inboxes-fill me-2"></i>ពាក្យស្នើសុំដែលបានអនុម័ត (Approved Print Requests)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @if($approvedRequests->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                        គ្មានពាក្យស្នើសុំដែលនៅរង់ចាំរៀបចំផែនការទេ។
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>កូដស្នើសុំ</th>
                                    <th>ចំណងជើង</th>
                                    <th>ចំនួនសរុប</th>
                                    <th>ថ្ងៃត្រូវបញ្ចប់</th>
                                    <th>អាទិភាព</th>
                                    <th class="text-end">សកម្មភាព</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedRequests as $req)
                                    <tr>
                                        <td><strong>{{ $req->request_code }}</strong></td>
                                        <td>
                                            <div class="fw-bold">{{ $req->title }}</div>
                                            <small class="text-muted">{{ $req->requester_name }} ({{ $req->department ?? 'General' }})</small>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ number_format($req->totalQty()) }} pcs</span></td>
                                        <td>{{ $req->required_by ? $req->required_by->format('d/m/Y') : '—' }}</td>
                                        <td>
                                            <span class="badge {{ $req->priority === 'urgent' ? 'bg-danger' : ($req->priority === 'high' ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                                                {{ ucfirst($req->priority) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-primary fw-bold"
                                                    onclick="planFromRequest({{ $req->id }}, '{{ addslashes($req->title) }}', {{ $req->totalQty() }}, '{{ $req->required_by?->format('Y-m-d') }}', '{{ $req->priority }}')">
                                                <i class="bi bi-lightning-charge-fill me-1"></i> រៀបចំផែនការ
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 3: EXCEL / CSV BULK IMPORT
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="bulkImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content border-0 shadow" method="POST" action="{{ route('schedule.bulk-import') }}">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-spreadsheet me-2"></i>នាំចូលទិន្នន័យជាដុំ (Bulk Import Excel/CSV)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info fs-xs mb-3">
                    <strong>ទម្រង់ដែលអាចចម្លងចូល (Supported Formats):</strong><br>
                    1. <strong>Excel / CSV Format:</strong> <code>Job Name, Quantity, Process, Day (1-31)</code><br>
                    2. <strong>Colon Format:</strong> <code>02/09/2026: Press - Math Grade 10 (8000)</code>
                </div>
                <label class="form-label fw-bold">ចម្លង & បិទភ្ជាប់ទិន្នន័យទីនេះ (Paste rows from Excel):</label>
                <textarea class="form-control font-monospace" name="import_data" rows="8" placeholder="Level 11, 8000, Press, 2
Level 11, 8000, Press, 3
Level 11, 4000, Folding, 4
Level 11, 4000, Binding, 5"></textarea>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                <button type="submit" class="btn btn-success btn-sm fw-bold px-3">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> នាំចូលកាលវិភាគ (Import Schedule)
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 4: RECORD DAILY ACTUAL OUTPUT
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="recordActualModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <div>
                    <h6 class="modal-title fw-bold mb-0" id="actualModalTitle">
                        <i class="bi bi-clipboard-check-fill text-primary me-2"></i>
                        កំណត់ត្រាលទ្ធផលផលិតកម្ម (Daily Output Record)
                    </h6>
                    <small class="text-muted fs-xs">កត់ត្រាចំនួនដែលបានផលិតជាក់ស្ដែងសម្រាប់ថ្ងៃនេះ</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <input type="hidden" id="actualScheduleId">
                <div class="p-3 bg-light rounded mb-3 border">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted fs-xs">កិច្ចការ (Job):</span>
                        <strong id="actualTaskProcess" class="text-dark">Press</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted fs-xs">កាលបរិច្ឆេទ (Date):</span>
                        <strong id="actualDateDisplay">02/09/2026</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-xs">ចំនួនគ្រោងទុក (Planned):</span>
                        <span class="badge bg-primary fs-sm" id="actualPlannedDisplay">8,000</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ចំនួនផលិតជាក់ស្ដែង (Actual Quantity) *</label>
                    <input type="number" class="form-control form-control-lg fw-bold text-center" id="actualQtyInput" min="0" placeholder="0" oninput="calculateActualVariance()">
                </div>

                {{-- Live Calculation Summary Cards --}}
                <div class="d-flex gap-2 text-center mb-3">
                    <div class="p-2 border rounded flex-fill bg-white">
                        <div class="fs-xs text-muted">គម្លាត (Variance)</div>
                        <strong id="actualVarianceDisplay" class="fs-6 text-muted">0</strong>
                    </div>
                    <div class="p-2 border rounded flex-fill bg-white">
                        <div class="fs-xs text-muted">នៅសល់ (Remaining)</div>
                        <strong id="actualRemainingDisplay" class="fs-6 text-muted">0</strong>
                    </div>
                    <div class="p-2 border rounded flex-fill bg-white">
                        <div class="fs-xs text-muted">វឌ្ឍនភាព (Progress)</div>
                        <strong id="actualProgressDisplay" class="fs-6 text-primary">0%</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold fs-xs">កំណត់ចំណាំ (Note / Reason)</label>
                    <input type="text" class="form-control form-control-sm" id="actualNotesInput" placeholder="e.g. ដំណើរការធម្មតា / Normal operation">
                </div>

                {{-- Delay Warning & Reschedule Trigger --}}
                <div id="rescheduleSuggestionBox" style="display:none;" class="p-3 bg-danger-subtle border border-danger-subtle rounded mb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong class="text-danger fs-sm">⚠️ ការផលិតមានភាពយឺតយ៉ាវ (Behind Schedule)</strong>
                            <div class="text-muted fs-xs">ផលិតបានមិនទាន់គ្រប់តាមផែនការគ្រោងទុក</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger fw-bold" onclick="requestRescheduleSuggestion()">
                            <i class="bi bi-arrow-repeat me-1"></i> មើលផែនការកែសម្រួល
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnToggleCellLock" onclick="toggleCellLock()">
                    <i class="bi bi-lock me-1"></i> Lock/Unlock
                </button>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="openRawEditModalFromActual()">
                        <i class="bi bi-pencil"></i> Edit Details
                    </button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3" onclick="saveActualOutput()">
                        <i class="bi bi-check-lg me-1"></i> រក្សាទុក (Save)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 5: RESCHEDULE SUGGESTION MODAL
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="rescheduleSuggestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i>សំណើកែសម្រួលកាលវិភាគ (Suggested Reschedule)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="alert alert-warning fs-sm mb-3">
                    <strong>⚠️ ការផលិតមានភាពយឺតយ៉ាវ:</strong> ប្រព័ន្ធបានគណនា និងស្នើសុំរំកិលដំណាក់កាលបន្ទាប់ទៅមុខតាមថ្ងៃធ្វើការ ដោយមិនប៉ះពាល់ដល់កិច្ចការដែលបានចាក់សោ (Locked)។
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 fs-xs" id="rescheduleShiftsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ដំណាក់កាល (Process)</th>
                                <th>កិច្ចការ (Task)</th>
                                <th class="text-center">ថ្ងៃដើម (Original)</th>
                                <th class="text-center text-primary">ថ្ងៃថ្មីដែលស្នើ (Suggested)</th>
                                <th class="text-center">ស្ថានភាព</th>
                            </tr>
                        </thead>
                        <tbody id="rescheduleShiftsBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់ (Cancel)</button>
                <button type="button" class="btn btn-warning btn-sm fw-bold px-4 text-dark" onclick="applyRescheduleChanges()">
                    <i class="bi bi-check-circle-fill me-1"></i> អនុវត្តការកែសម្រួល (Apply Reschedule)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL 6: TEMPLATES & MACHINE CAPACITY (ADMIN ONLY)
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="templatesManagerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:#4f46e5;">
                <h5 class="modal-title fw-bold"><i class="bi bi-diagram-3 me-2"></i>គំរូដំណើរការ & សមត្ថភាពម៉ាស៊ីន (Templates & Capacity)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="fw-bold text-dark mb-3">គំរូដំណើរការផលិតកម្មសកម្ម (Active Templates)</h6>
                <div class="row g-3">
                    @foreach($templates as $tpl)
                        <div class="col-md-6">
                            <div class="card h-100 border">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-primary mb-1">{{ $tpl->name }}</h6>
                                    <p class="text-muted fs-xs mb-2">{{ $tpl->description ?? 'Standard workflow' }}</p>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($tpl->processes as $tp)
                                            <span class="badge bg-light text-dark border">
                                                {{ $tp->sequence }}. {{ $tp->process_name }}
                                                @if($tp->capacity)<small class="text-muted">({{ number_format($tp->capacity) }})</small>@endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- BASE SUPPORTING MODALS (100% Backward Compatibility) --}}
@include('schedule.partials.legacy-modals')

<script>
// ══════════════════════════════════════════════════════════════════
// SMART PRODUCTION PLANNING ENGINE (Desktop & Mobile Optimized)
// ══════════════════════════════════════════════════════════════════

let _currentCalculatedPlan = null;
let _currentRescheduleData = null;
let _selectedCellForActual = null;
let _selectedBookId = null;

const _batchesData = {!! $batchesJson !!};
const _activeBatchId = {{ $currentBatch?->id ?? 'null' }};

const PROCESS_ICONS = {
    'Design': 'bi-palette-fill', 'Prepress': 'bi-laptop-fill', 'Press': 'bi-printer-fill',
    'Digital': 'bi-cpu-fill', 'Folding': 'bi-layers-fill', 'Gathering': 'bi-collection-fill',
    'Staple': 'bi-pin-angle-fill', 'Binding': 'bi-journal-bookmark-fill', 'Cutting': 'bi-scissors',
    'Lamination': 'bi-shield-check', 'Packaging': 'bi-box-seam-fill', 'Delivery': 'bi-truck', 'Other': 'bi-gear-fill'
};

function getBookTypeIcon(title, category) {
    const t = (title || '').toLowerCase();
    const c = (category || '').toLowerCase();
    if (c === 'perfect_binding' || t.includes('textbook')) return '📘';
    if (c === 'staple' || t.includes('workbook')) return '📗';
    if (t.includes('song')) return '🎵';
    if (t.includes('folktale') || t.includes('forktale')) return '📖';
    if (c === 'cover') return '📄';
    return '📕';
}

function onWizardBatchChanged(batchId) {
    const bookSelect = document.getElementById('wizardBookSelect');
    const chipsList = document.getElementById('wizardBookChipsList');
    const countBadge = document.getElementById('batchBookCountBadge');
    if (!bookSelect) return;

    let books = [];
    if (batchId === 'all') {
        _batchesData.forEach(b => {
            books.push(...b.books.map(bk => ({ ...bk, batch_name: b.name })));
        });
    } else {
        const batch = _batchesData.find(b => String(b.id) === String(batchId));
        if (batch) {
            books = batch.books;
        }
    }

    // Populate dropdown grouped by Grade
    bookSelect.innerHTML = '<option value="">— ជ្រើសរើសសៀវភៅ (Select Book) —</option>';
    
    // Group books by Grade
    const grades = {};
    books.forEach(bk => {
        const g = bk.grade || (bk.batch_name ? bk.batch_name : 'ទូទៅ (General)');
        if (!grades[g]) grades[g] = [];
        grades[g].push(bk);
    });

    for (const [grade, gBooks] of Object.entries(grades)) {
        const optgroup = document.createElement('optgroup');
        optgroup.label = `🎓 ${grade}`;
        gBooks.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.dataset.id = b.id;
            opt.dataset.batchId = b.batch_id;
            opt.dataset.title = b.title;
            opt.dataset.grade = b.grade || '';
            opt.dataset.category = b.category || '';
            opt.dataset.target = b.target_qty;
            opt.dataset.remaining = b.remaining;
            opt.dataset.printed = b.total_printed;

            const icon = getBookTypeIcon(b.title, b.category);
            const statusLabel = b.is_completed ? ' ✅ [រួចរាល់]' : (b.total_printed > 0 ? ` (បានបោះ: ${b.total_printed.toLocaleString()})` : '');
            opt.textContent = `${icon} ${b.grade ? '[' + b.grade + '] ' : ''}${b.title} — 🎯 ${b.target_qty.toLocaleString()} ក្បាល${statusLabel}`;
            optgroup.appendChild(opt);
        });
        bookSelect.appendChild(optgroup);
    }

    if (countBadge) {
        countBadge.textContent = `${books.length} សៀវភៅ`;
    }

    // Render Fast-Pick Chips
    if (chipsList) {
        if (!books.length) {
            chipsList.innerHTML = '<div class="text-muted fs-xs py-1 fst-italic">គ្មានសៀវភៅក្នុងបាច់នេះទេ។</div>';
        } else {
            let chipsHtml = '';
            books.forEach(b => {
                const icon = getBookTypeIcon(b.title, b.category);
                const isSelected = String(b.id) === String(_selectedBookId);
                const activeClass = isSelected ? 'active' : '';
                const progressBadge = b.is_completed
                    ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-1">✅ 100%</span>'
                    : `<span class="badge bg-light text-dark border px-1">🎯 ${b.target_qty.toLocaleString()}</span>`;

                chipsHtml += `
                    <div class="book-chip-card ${activeClass}" onclick="selectBookById(${b.id})" title="${b.grade ? '[' + b.grade + '] ' : ''}${b.title}">
                        <div class="book-chip-title">${icon} ${b.grade ? '<b>[' + b.grade + ']</b> ' : ''}${b.title}</div>
                        <div class="book-chip-meta">
                            <span>${b.category === 'perfect_binding' ? 'Textbook' : 'Workbook'}</span>
                            ${progressBadge}
                        </div>
                    </div>
                `;
            });
            chipsList.innerHTML = chipsHtml;
        }
    }
}

function selectBookById(bookId) {
    if (!bookId) return;
    _selectedBookId = bookId;
    let foundBook = null;
    let foundBatch = null;

    for (const b of _batchesData) {
        const bk = b.books.find(x => String(x.id) === String(bookId));
        if (bk) {
            foundBook = bk;
            foundBatch = b;
            break;
        }
    }

    if (!foundBook) return;

    // Update select dropdown value
    const bookSelect = document.getElementById('wizardBookSelect');
    if (bookSelect) bookSelect.value = bookId;

    // Update hidden fields
    const bookIdInput = document.getElementById('planBookId');
    if (bookIdInput) bookIdInput.value = foundBook.id;
    const batchIdInput = document.getElementById('planBatchId');
    if (batchIdInput) batchIdInput.value = foundBook.batch_id;

    // Highlight chip
    document.querySelectorAll('.book-chip-card').forEach(card => {
        card.classList.remove('active');
    });
    const clickedChip = document.querySelector(`.book-chip-card[onclick="selectBookById(${bookId})"]`);
    if (clickedChip) clickedChip.classList.add('active');

    // Populate Job Name with Grade + Title
    const jobTitle = foundBook.grade ? `[${foundBook.grade}] ${foundBook.title}` : foundBook.title;
    document.getElementById('planJobName').value = jobTitle;

    // Populate Quantity
    const qty = (foundBook.remaining && foundBook.remaining > 0) ? foundBook.remaining : foundBook.target_qty;
    if (qty > 0) {
        document.getElementById('planQuantity').value = qty;
    }

    // Auto-select Template based on category & title
    const tplSelect = document.getElementById('planTemplateSelect');
    if (tplSelect) {
        const cat = (foundBook.category || '').toLowerCase();
        const titleLower = foundBook.title.toLowerCase();

        Array.from(tplSelect.options).forEach(o => {
            const optText = o.text.toLowerCase();
            if (foundBook.printing_method === 'digital' && optText.includes('digital')) {
                o.selected = true;
            } else if ((cat === 'perfect_binding' || titleLower.includes('textbook')) && optText.includes('perfect')) {
                o.selected = true;
            } else if ((cat === 'staple' || titleLower.includes('workbook') || titleLower.includes('song') || titleLower.includes('folk')) && (optText.includes('staple') || optText.includes('workbook'))) {
                o.selected = true;
            } else if (cat === 'cover' && optText.includes('cover')) {
                o.selected = true;
            }
        });
    }

    triggerPlanCalculation();
}

function onWizardBookSelected(sel) {
    if (!sel.value) return;
    selectBookById(sel.value);
}

function onWizardRequestSelected(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    document.getElementById('planJobName').value = opt.dataset.title || '';
    if (opt.dataset.qty) document.getElementById('planQuantity').value = opt.dataset.qty;
    if (opt.dataset.due) document.getElementById('planDueDate').value = opt.dataset.due;
    if (opt.dataset.priority) document.getElementById('planPriority').value = opt.dataset.priority;
    
    // Clear book selection highlight
    _selectedBookId = null;
    document.querySelectorAll('.book-chip-card').forEach(c => c.classList.remove('active'));
    const bookSelect = document.getElementById('wizardBookSelect');
    if (bookSelect) bookSelect.value = '';
    const bookIdInput = document.getElementById('planBookId');
    if (bookIdInput) bookIdInput.value = '';

    triggerPlanCalculation();
}

function setQuickQty(qty) {
    document.getElementById('planQuantity').value = qty;
    triggerPlanCalculation();
}

function openSmartPlanWizard(day = null, isSimulation = false) {
    if (day) {
        const monthStr = String({{ $month }}).padStart(2, '0');
        const dayStr = String(day).padStart(2, '0');
        document.getElementById('planStartDate').value = `{{ $year }}-${monthStr}-${dayStr}`;
    }

    // Initialize Batch Filter & populate books for the active batch
    const batchSelect = document.getElementById('wizardBatchSelect');
    if (batchSelect && _activeBatchId) {
        batchSelect.value = _activeBatchId;
        onWizardBatchChanged(_activeBatchId);
    } else if (batchSelect) {
        onWizardBatchChanged(batchSelect.value);
    }

    new bootstrap.Modal(document.getElementById('smartPlanModal')).show();
    triggerPlanCalculation();
}

function planFromRequest(id, title, qty, due, priority) {
    bootstrap.Modal.getInstance(document.getElementById('approvedRequestsModal'))?.hide();
    
    document.getElementById('planJobName').value = title;
    document.getElementById('planQuantity').value = qty;
    if (due) document.getElementById('planDueDate').value = due;
    if (priority) document.getElementById('planPriority').value = priority;
    
    openSmartPlanWizard();
}

// Reactive Calculation Engine
let _calcTimer = null;
function triggerPlanCalculation() {
    clearTimeout(_calcTimer);
    document.getElementById('planLoadingState').style.display = 'block';
    _calcTimer = setTimeout(calculatePlanAjax, 250);
}

function calculatePlanAjax() {
    const name        = document.getElementById('planJobName').value.trim() || 'Sample Job';
    const quantity    = parseInt(document.getElementById('planQuantity').value) || 1000;
    const startDate   = document.getElementById('planStartDate').value || '{{ now()->format("Y-m-d") }}';
    const dueDate     = document.getElementById('planDueDate').value || null;
    const priority    = document.getElementById('planPriority').value || 'normal';
    const templateId  = document.getElementById('planTemplateSelect').value;
    const includeSundays = document.getElementById('planIncludeSundays').checked;
    const bookId      = document.getElementById('planBookId')?.value || null;

    const payload = {
        name, quantity, start_date: startDate, due_date: dueDate,
        priority, template_id: templateId, include_sundays: includeSundays ? 1 : 0,
        book_id: bookId
    };

    fetch('{{ route("schedule.plan.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('planLoadingState').style.display = 'none';
        if (data.ok && data.plan) {
            _currentCalculatedPlan = data.plan;
            renderPlanPreview(data.plan);
        }
    })
    .catch(err => {
        document.getElementById('planLoadingState').style.display = 'none';
        console.error('Plan calculation error:', err);
    });
}

function renderPlanPreview(plan) {
    const sum = plan.summary;
    const tbody = document.getElementById('planStagesBody');
    const badge = document.getElementById('planStatusBadge');
    const alertBox = document.getElementById('planAlertBox');
    const pipeline = document.getElementById('planPipelineFlow');
    const mobileCards = document.getElementById('planMobileCards');
    const requestedQty = parseInt(document.getElementById('planQuantity').value) || 1000;

    // Status Badge & Simple Alert
    if (sum.status === 'optimal' && (!sum.warnings || !sum.warnings.length)) {
        badge.className = 'badge bg-success';
        badge.textContent = '✓ Optimal & Feasible';
        alertBox.innerHTML = `
            <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-2 fs-sm">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>✓ ផែនការអាចអនុវត្តបាន (Plan is feasible & on schedule)</strong><br>
                    <small class="text-muted">ចាប់ផ្ដើមថ្ងៃ ${sum.start_date} បញ្ចប់ថ្ងៃ ${sum.end_date} (សរុប ${sum.total_working_days} ថ្ងៃធ្វើការ)</small>
                </div>
            </div>
        `;
    } else {
        badge.className = 'badge bg-warning text-dark';
        badge.textContent = '⚠️ Attention';
        const warnText = sum.warnings && sum.warnings.length ? sum.warnings[0] : 'ផែនការនេះអាចយឺតជាងថ្ងៃកំណត់ ឬម៉ាស៊ីនត្រូវប្រើប្រាស់ពេញចំណុះ';
        alertBox.innerHTML = `
            <div class="alert alert-warning d-flex align-items-center py-2 px-3 mb-2 fs-sm">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>
                    <strong>⚠️ ការជូនដំណឹង (Attention)</strong><br>
                    <small class="text-dark">${warnText}</small>
                </div>
            </div>
        `;
    }

    // 1. Pipeline Stepper Flow
    let pipelineHtml = '';
    plan.stages.forEach((st, idx) => {
        const icon = PROCESS_ICONS[st.process_name] || 'bi-gear-fill';
        pipelineHtml += `
            <div class="pipeline-step" style="border-left: 3px solid ${st.process_color};">
                <i class="bi ${icon}" style="color: ${st.process_color}; font-size: 0.9rem;"></i>
                <span>${st.process_name}</span>
                <span class="badge bg-light text-secondary border px-1">${st.duration_days}d</span>
            </div>
        `;
        if (idx < plan.stages.length - 1) {
            pipelineHtml += `<i class="bi bi-chevron-right pipeline-arrow"></i>`;
        }
    });
    pipeline.innerHTML = pipelineHtml;

    // 2. Desktop Table Rows
    let rowsHtml = '';
    // 3. Mobile Vertical Cards
    let cardsHtml = '';

    plan.stages.forEach(st => {
        const totalStageQty = st.quantity || (st.dates && st.dates.reduce((acc, cur) => acc + (parseInt(cur.allocated_qty) || 0), 0)) || requestedQty;
        const datesText = st.start_date === st.end_date ? st.start_date : `${st.start_date} → ${st.end_date}`;
        const splitText = st.dates && st.dates.length > 1
            ? st.dates.map(d => Number(d.allocated_qty).toLocaleString()).join(' + ')
            : '';
        const icon = PROCESS_ICONS[st.process_name] || 'bi-gear-fill';

        // Table row for desktop
        rowsHtml += `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi ${icon}" style="color:${st.process_color};"></i>
                        <strong style="color:${st.process_color};">${st.process_name}</strong>
                    </div>
                    <small class="text-muted fs-xs">${st.machine_name || 'Standard Workflow'}</small>
                </td>
                <td class="font-monospace text-nowrap">${datesText}</td>
                <td class="text-center font-monospace">${st.duration_days} ថ្ងៃ</td>
                <td>
                    <strong>${Number(totalStageQty).toLocaleString()}</strong>
                    ${splitText ? `<br><small class="text-muted">(${splitText})</small>` : ''}
                </td>
                <td class="text-center text-nowrap">
                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">✓ ត្រៀមរួចរាល់</span>
                </td>
            </tr>
        `;

        // Mobile card for mobile view
        cardsHtml += `
            <div class="stage-mobile-card" style="border-left-color: ${st.process_color};">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi ${icon}" style="color:${st.process_color}; font-size: 1rem;"></i>
                        <strong style="color:${st.process_color}; font-size: 0.88rem;">${st.process_name}</strong>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">${st.duration_days} ថ្ងៃ (Days)</span>
                </div>
                <div class="d-flex justify-content-between text-muted fs-xs mt-1">
                    <span>📅 ${datesText}</span>
                    <span>📊 <strong>${Number(totalStageQty).toLocaleString()}</strong> ${splitText ? `(${splitText})` : ''}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center fs-xs mt-2 pt-1 border-top">
                    <span class="text-secondary"><i class="bi bi-cpu me-1"></i>${st.machine_name || 'Standard'}</span>
                    <span class="text-success fw-bold">✓ ត្រៀមរួចរាល់</span>
                </div>
            </div>
        `;
    });

    tbody.innerHTML = rowsHtml;
    mobileCards.innerHTML = cardsHtml;
}

// Submit Confirmed Plan
function submitSmartPlan() {
    if (!_currentCalculatedPlan) {
        showToast('warning', 'កំពុងរៀបចំផែនការ សូមរង់ចាំបន្តិច...');
        return;
    }

    const btn = document.getElementById('btnConfirmPlan');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> កំពុងបញ្ចូល...';

    fetch('{{ route("schedule.plan.confirm") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ plan: _currentCalculatedPlan })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showToast('success', data.message || '✓ ផែនការផលិតកម្មត្រូវបានបង្កើតដោយជោគជ័យ');
            bootstrap.Modal.getInstance(document.getElementById('smartPlanModal'))?.hide();
            setTimeout(() => location.reload(), 600);
        } else {
            showToast('error', data.message || 'Failed to save plan');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> អនុវត្ត & បញ្ចូលក្នុងកាលវិភាគ (Confirm Plan)';
        }
    })
    .catch(() => {
        showToast('error', 'Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> អនុវត្ត & បញ្ចូលក្នុងកាលវិភាគ (Confirm Plan)';
    });
}

// ══════════════════════════════════════════════════════════════════
// DAILY ACTUAL OUTPUT RECORDING (SUPER SIMPLE FOR OPERATORS)
// ══════════════════════════════════════════════════════════════════

function handleCellClick(e, cell) {
    if (document.body.classList.contains('batch-mode')) {
        const cb = cell.querySelector('.batch-check');
        if (cb) { cb.checked = !cb.checked; updateBatchCount(); }
        return;
    }

    _selectedCellForActual = cell;
    const scheduleId = cell.dataset.scheduleId;
    const task       = cell.dataset.task;
    const process    = cell.dataset.process;
    const day        = cell.dataset.day;
    const plannedQty = parseInt(cell.dataset.plannedQty) || 0;
    const actualQty  = parseInt(cell.dataset.actualQty) || 0;
    const isLocked   = cell.dataset.isLocked === '1';

    if (!scheduleId || !task || e.shiftKey) {
        openCellModal(cell);
        return;
    }

    document.getElementById('actualScheduleId').value = scheduleId;
    document.getElementById('actualTaskProcess').textContent = `${process}: ${task}`;
    document.getElementById('actualDateDisplay').textContent = `${String(day).padStart(2,'0')}/{{ str_pad($month,2,"0",STR_PAD_LEFT) }}/{{ $year }}`;
    document.getElementById('actualPlannedDisplay').textContent = plannedQty > 0 ? plannedQty.toLocaleString() : 'Not set';
    document.getElementById('actualQtyInput').value = actualQty > 0 ? actualQty : '';

    const lockBtn = document.getElementById('btnToggleCellLock');
    lockBtn.innerHTML = isLocked
        ? '<i class="bi bi-unlock-fill me-1 text-danger"></i> Unlock'
        : '<i class="bi bi-lock-fill me-1"></i> Lock';

    calculateActualVariance();
    new bootstrap.Modal(document.getElementById('recordActualModal')).show();
}

function calculateActualVariance() {
    const planned = parseInt(document.getElementById('actualPlannedDisplay').textContent.replace(/,/g,'')) || 0;
    const actual  = parseInt(document.getElementById('actualQtyInput').value) || 0;
    const variance = actual - planned;
    const remaining = Math.max(0, planned - actual);
    const progress = planned > 0 ? Math.min(100, Math.round((actual / planned) * 100)) : 0;

    const varEl = document.getElementById('actualVarianceDisplay');
    const remEl = document.getElementById('actualRemainingDisplay');
    const progEl = document.getElementById('actualProgressDisplay');

    if (variance < 0) {
        varEl.className = 'fs-6 text-danger fw-bold';
        varEl.textContent = `${variance.toLocaleString()}`;
    } else if (variance > 0) {
        varEl.className = 'fs-6 text-success fw-bold';
        varEl.textContent = `+${variance.toLocaleString()}`;
    } else {
        varEl.className = 'fs-6 text-muted';
        varEl.textContent = '0';
    }

    remEl.textContent = remaining.toLocaleString();
    progEl.textContent = `${progress}%`;

    const reschedBox = document.getElementById('rescheduleSuggestionBox');
    if (planned > 0 && actual < planned) {
        reschedBox.style.display = 'block';
    } else {
        reschedBox.style.display = 'none';
    }
}

function saveActualOutput() {
    const scheduleId = document.getElementById('actualScheduleId').value;
    const actualQty  = parseInt(document.getElementById('actualQtyInput').value) || 0;
    const notes      = document.getElementById('actualNotesInput').value;

    fetch('{{ route("schedule.actual.record") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ schedule_id: scheduleId, actual_qty: actualQty, notes: notes })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showToast('success', '✓ បានកត់ត្រាលទ្ធផលផលិតកម្មដោយជោគជ័យ');
            bootstrap.Modal.getInstance(document.getElementById('recordActualModal'))?.hide();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('error', data.message || 'Error saving actual output');
        }
    })
    .catch(() => showToast('error', 'Network error.'));
}

function toggleCellLock() {
    const scheduleId = document.getElementById('actualScheduleId').value;
    if (!scheduleId) return;

    fetch('{{ route("schedule.cell.lock") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ schedule_id: scheduleId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showToast('info', data.message);
            const lockBtn = document.getElementById('btnToggleCellLock');
            lockBtn.innerHTML = data.is_locked
                ? '<i class="bi bi-unlock-fill me-1 text-danger"></i> Unlock'
                : '<i class="bi bi-lock-fill me-1"></i> Lock';
        }
    });
}

function openRawEditModalFromActual() {
    bootstrap.Modal.getInstance(document.getElementById('recordActualModal'))?.hide();
    if (_selectedCellForActual) {
        openCellModal(_selectedCellForActual);
    }
}

function requestRescheduleSuggestion() {
    const scheduleId = document.getElementById('actualScheduleId').value;
    if (!scheduleId) return;

    fetch('{{ route("schedule.plan.reschedule-suggest") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ schedule_id: scheduleId, delay_days: 1 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok && data.suggestion) {
            _currentRescheduleData = data.suggestion;
            renderRescheduleModal(data.suggestion);
            bootstrap.Modal.getInstance(document.getElementById('recordActualModal'))?.hide();
            new bootstrap.Modal(document.getElementById('rescheduleSuggestionModal')).show();
        }
    });
}

function renderRescheduleModal(sugg) {
    const tbody = document.getElementById('rescheduleShiftsBody');
    let rows = '';

    sugg.shifts.forEach(s => {
        rows += `
            <tr>
                <td><strong>${s.process}</strong></td>
                <td>${s.task || '—'}</td>
                <td class="text-center font-monospace">${s.original_date || 'Day ' + s.original_day}</td>
                <td class="text-center font-monospace text-primary fw-bold">${s.new_date || 'Day ' + s.new_day}</td>
                <td class="text-center">
                    ${s.is_locked ? '<span class="badge bg-secondary">Locked (Unmoved)</span>' : '<span class="badge bg-warning text-dark">+1 Day Shift</span>'}
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = rows;
}

function applyRescheduleChanges() {
    if (!_currentRescheduleData) return;

    fetch('{{ route("schedule.plan.reschedule-apply") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ shifts: _currentRescheduleData.shifts, reason: 'Actual output delay compensation' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showToast('success', '✓ បានកែសម្រួលកាលវិភាគដោយជោគជ័យ');
            bootstrap.Modal.getInstance(document.getElementById('rescheduleSuggestionModal'))?.hide();
            setTimeout(() => location.reload(), 600);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const batchSelect = document.getElementById('wizardBatchSelect');
    if (batchSelect && _activeBatchId) {
        batchSelect.value = _activeBatchId;
        onWizardBatchChanged(_activeBatchId);
    } else if (batchSelect) {
        onWizardBatchChanged(batchSelect.value);
    }

    @if(request('open_wizard'))
        @if(request('request_id'))
            const reqSel = document.getElementById('wizardRequestSelect');
            if (reqSel) {
                reqSel.value = '{{ request("request_id") }}';
                onWizardRequestSelected(reqSel);
            }
        @endif
        openSmartPlanWizard();
    @endif
});
</script>
@endsection
