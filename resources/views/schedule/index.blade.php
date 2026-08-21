@extends('layouts.app')

@section('title', 'កាលវិភាគផលិតកម្មប្រចាំខែ')

@section('content')
@php
    use Carbon\Carbon;
    $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
    $monthNameKh = Carbon::createFromDate($year, $month, 1)->locale('km')->translatedFormat('F Y');

    // Premium Process Colors (Vibrant & Harmonious)
    $processColors = [
        'Design'    => '#3b82f6',  // Vivid Blue
        'Press'     => '#ef4444',  // Rose Red
        'Digital'   => '#8b5cf6',  // Violet
        'Folding'   => '#d946ef',  // Fuchsia
        'Gathering' => '#f59e0b',  // Amber
        'Staple'    => '#06b6d4',  // Cyan
        'Binding'   => '#ec4899',  // Pink
        'Cutting'   => '#14b8a6',  // Teal
        'Packaging' => '#10b981',  // Emerald
        'Delivery'  => '#f97316',  // Deep Orange
        'Other'     => '#64748b',  // Slate
    ];

    // Build day info
    $days = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = Carbon::createFromDate($year, $month, $d);
        $days[] = [
            'day' => $d,
            'dow' => $date->dayOfWeek, // 0=Sun, 6=Sat
            'date' => $date->format('d/m/Y'),
            'label' => $date->format('D, d/m/Y'),
        ];
    }

    // Prev/Next month
    $prevDate = Carbon::createFromDate($year, $month, 1)->subMonth();
    $nextDate = Carbon::createFromDate($year, $month, 1)->addMonth();
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

/* Override max-width for schedule page — we need full width for the grid */
.page-content { max-width: 100% !important; }

/* ═══════════════════════════════════════════════════════
   SCHEDULE GRID — SCROLLABLE LIKE EXCEL (PREMIUM UI)
═══════════════════════════════════════════════════════ */
.schedule-container {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.03);
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
    overflow: hidden;
    font-family: var(--font-khmer);
}

.schedule-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.25rem 2rem;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    background: linear-gradient(to right, #ffffff, #f8fafc);
}

.schedule-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: .75rem;
    letter-spacing: -0.02em;
}

.schedule-nav {
    display: flex;
    align-items: center;
    gap: .75rem;
    background: #f1f5f9;
    padding: 0.35rem;
    border-radius: 12px;
}

.schedule-nav .btn-nav {
    width: 38px; height: 38px;
    border-radius: 8px;
    background: transparent;
    border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    color: #64748b;
}
.schedule-nav .btn-nav:hover {
    background: #ffffff;
    color: #3b82f6;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.schedule-month-label {
    font-size: 1.05rem;
    font-weight: 700;
    min-width: 160px;
    text-align: center;
    color: #334155;
    letter-spacing: 0.01em;
}

/* ── THE SCROLLABLE GRID ── */
.schedule-grid-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 280px);
    position: relative;
    border-radius: 0 0 16px 16px;
    background: #f8fafc;
}

/* Custom scrollbar for the grid */
.schedule-grid-wrapper::-webkit-scrollbar { height: 12px; width: 12px; }
.schedule-grid-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }
.schedule-grid-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; border: 3px solid #f1f5f9; }
.schedule-grid-wrapper::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.schedule-grid-wrapper::-webkit-scrollbar-corner { background: #f1f5f9; }

.schedule-table {
    width: max-content;
    min-width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: .85rem;
}

.schedule-table th,
.schedule-table td {
    border: 1px solid rgba(0, 0, 0, 0.025);
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
    color: #1e293b;
    font-weight: 700;
    font-size: .85rem;
    min-width: 120px;
    max-width: 120px;
    padding: .75rem 1rem;
    text-align: left;
    border-right: 1px solid rgba(0,0,0,0.04);
    box-shadow: 4px 0 15px -4px rgba(0,0,0,0.06);
}

/* ── HEADER ROW ── */
.schedule-table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #f8fafc;
    color: #475569;
    padding: .6rem .4rem;
    font-weight: 700;
    font-size: .75rem;
    min-width: 110px;
    border-bottom: 2px solid rgba(0,0,0,0.04);
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

/* Corner cell (process header) */
.schedule-table thead th.col-process {
    z-index: 20;
    background: #ffffff;
    color: #64748b;
    font-size: .75rem;
}

/* ── DAY CELLS ── */
.schedule-table td.day-cell {
    min-width: 110px;
    max-width: 150px;
    height: 56px;
    padding: .4rem .5rem;
    cursor: pointer;
    transition: background 0.2s ease;
    position: relative;
    background: #ffffff;
}

.schedule-table td.day-cell:hover {
    background: #f1f5f9 !important;
}

.schedule-table td.day-cell.weekend {
    background: repeating-linear-gradient(45deg, #f8fafc, #f8fafc 10px, #f1f5f9 10px, #f1f5f9 20px);
    border-right: 1px solid #e2e8f0;
}

.schedule-table td.day-cell.holiday {
    background: #fef2f2;
}

.schedule-table td.day-cell.today {
    background: rgba(59, 130, 246, 0.08);
    position: relative;
    border-left: 2px solid rgba(59, 130, 246, 0.4);
    border-right: 2px solid rgba(59, 130, 246, 0.4);
}
.schedule-table td.day-cell.today::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    pointer-events: none;
    box-shadow: inset 0 0 15px rgba(59, 130, 246, 0.05);
    animation: pulse-today 3s infinite alternate;
}
@keyframes pulse-today {
    from { opacity: 0.4; }
    to { opacity: 1; }
}
.schedule-table thead th.today {
    background: #3b82f6;
    color: #ffffff;
    border-bottom: 2px solid #2563eb;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Task content inside cell (Premium Pill Design) */
.cell-task {
    font-size: .75rem;
    font-weight: 700;
    padding: .45rem .6rem;
    border-radius: 8px;
    display: block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.4;
    color: #ffffff;
    box-shadow: 0 4px 10px -2px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.25);
    border: none;
    margin-bottom: 6px;
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
    letter-spacing: 0.01em;
    position: relative;
    will-change: transform, box-shadow;
}

.cell-task::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 50%;
    background: linear-gradient(180deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
    border-radius: 8px 8px 0 0;
    pointer-events: none;
}

.cell-task:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 12px 16px -4px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.4);
    z-index: 10;
}

.cell-note {
    font-size: .7rem;
    color: #94a3b8;
    display: block;
    margin-top: 3px;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

/* ═══════════════════════════════════════════════════════
   LEGEND
═══════════════════════════════════════════════════════ */
.schedule-legend {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    padding: 1rem 2rem;
    border-top: 1px solid rgba(0,0,0,0.04);
    background: #ffffff;
    align-items: center;
}

.legend-title {
    font-weight: 700;
    font-size: .8rem;
    color: #1e293b;
    margin-right: .5rem;
    letter-spacing: 0.02em;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: .75rem;
    font-weight: 500;
    color: #64748b;
}

.legend-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* ═══════════════════════════════════════════════════════
   INFO BAR (Today/Tomorrow/Progress)
═══════════════════════════════════════════════════════ */
.schedule-info-bar {
    display: flex;
    gap: 1.25rem;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid rgba(0,0,0,0.03);
    background: #f8fafc;
    flex-wrap: wrap;
}

.info-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.8);
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
    flex: 1;
    min-width: 250px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.06);
    background: rgba(255, 255, 255, 0.95);
}

.info-card-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
    background: #f1f5f9;
}

.info-today .info-card-icon { background: #eff6ff; color: #3b82f6; }
.info-tomorrow .info-card-icon { background: #fef3c7; color: #f59e0b; }
.info-progress .info-card-icon { background: #f3e8ff; color: #9333ea; }

.info-card-content {
    display: flex;
    flex-direction: column;
    gap: .3rem;
}

.info-card-content strong {
    font-size: .9rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: 0.01em;
}

.info-tasks {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
}

.info-task-badge {
    font-size: .75rem;
    padding: .25rem .6rem;
    border-radius: 6px;
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

/* ═══════════════════════════════════════════════════════
   EDIT MODAL & OTHERS
═══════════════════════════════════════════════════════ */
.modal-cell-edit .modal-body { padding: 1.5rem; }
.modal-cell-edit .form-label { font-size: .85rem; font-weight: 600; color: #334155; }

/* Status pip */
.status-pip { position:absolute; bottom:4px; right:4px; font-size:.7rem; line-height:1; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1)); }
.done-pip {}
.prog-pip {}

/* Batch mode */
body.batch-mode .day-cell { cursor:crosshair !important; }
body.batch-mode .batch-cb { display:block !important; }
.batch-toolbar {
    display:none;
    position:fixed; bottom:2rem; left:50%; transform:translateX(-50%);
    background: rgba(15, 23, 42, 0.9);
    backdrop-filter: blur(12px);
    color:#fff; border-radius:100px;
    padding:.75rem 1.5rem; gap:1rem; align-items:center;
    box-shadow: 0 10px 40px rgba(0,0,0,.3);
    z-index:9999; font-size:.9rem; font-weight: 600;
    border: 1px solid rgba(255,255,255,0.1);
}
body.batch-mode .batch-toolbar { display:flex; }

/* Compact toolbar tweaks */
.schedule-header .dropdown-menu { min-width:220px; font-size:.85rem; border:none; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 12px; }
.schedule-header .btn { border-radius: 8px; font-weight: 600; padding: 0.4rem 0.75rem; transition: all 0.2s; }
.schedule-header .btn-outline-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); }

/* Status row tints */
.schedule-table td.status-done { background: rgba(16,185,129,.04) !important; }
.schedule-table td.status-in_progress { background: rgba(245,158,11,.04) !important; }

/* Process row colors (left strip) */
@foreach($processColors as $proc => $clr)
.process-row-{{ Str::slug($proc) }} td.col-process {
    border-left: 4px solid {{ $clr }};
}
@endforeach

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
    min-height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    color: #64748b;
    padding: 6px 8px;
    transition: all .2s;
}
.task-builder-row:focus-within .tb-result {
    border-color: #8b5cf6;
    color: #8b5cf6;
    background: #f3e8ff;
}

/* ── Telegram preview list ────────────────────────────── */
#alertPreview .prev-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: 6px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: .85rem;
}
#alertPreview .prev-badge {
    border-radius: 6px;
    padding: 2px 8px;
    font-size: .75rem;
    font-weight: 700;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* ── Premium Modal UI ────────────────────────────────────── */
.premium-input {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.85rem 1.15rem;
    font-size: 1rem;
    font-weight: 500;
    color: #0f172a;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.premium-input:focus {
    background: #ffffff;
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
    outline: none;
}
.modal-downtime .premium-input:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
}
.premium-input::placeholder {
    color: #94a3b8;
}
.premium-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
}
.premium-radio-card {
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.15rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
}
.premium-radio-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 8px 16px -4px rgba(0,0,0,0.06);
}
.premium-radio-card.active {
    border-color: #ef4444;
    background: #fef2f2;
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.12);
}
.premium-radio-card input[type="radio"] {
    margin-top: 0.25rem;
    transform: scale(1.3);
    accent-color: #dc2626;
}
@media (max-width: 991px) {
    /* Responsive Premium Modals */
    .modal-content { border-radius: 12px !important; }
    .modal-header, .modal-body, .modal-footer { padding: 1rem !important; }
    .premium-input { padding: 0.65rem 1rem; font-size: 0.95rem; }
    .premium-radio-card { padding: 0.85rem; }

    .schedule-header { padding: 1rem; flex-direction: column; align-items: flex-start; gap: 0.75rem; }
    .schedule-info-bar { padding: 1rem; flex-direction: column; }
    .info-card { width: 100%; min-width: 100%; padding: 1rem; }
    
    /* Let the page scroll naturally vertically on mobile instead of inner scrollbar */
    .schedule-grid-wrapper {
        max-height: none !important;
        overflow-y: visible !important;
    }
    
    /* Make legend smaller and wrap nicely */
    .schedule-legend {
        padding: 1rem;
        gap: 0.5rem;
    }
    .legend-item {
        font-size: 0.65rem;
    }
    
    /* Shrink sticky column */
    .schedule-table th.col-process,
    .schedule-table td.col-process {
        min-width: 75px !important;
        max-width: 75px !important;
        font-size: 0.65rem;
        padding: 0.4rem 0.25rem;
    }
    .schedule-table thead th.col-process {
        font-size: 0.6rem;
        white-space: normal;
        line-height: 1.1;
    }

    /* Make day cells narrower */
    .schedule-table td.day-cell {
        min-width: 80px !important;
        padding: 0.25rem;
    }
    
    /* Shrink the task pills */
    .cell-task {
        font-size: 0.6rem;
        padding: 0.25rem 0.4rem;
        margin-bottom: 3px;
    }
    .cell-note {
        font-size: 0.55rem;
    }
}
/* ── DARK MODE OVERRIDES ── */
[data-theme="dark"] .schedule-container { background: var(--surface); border-color: var(--border); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3); }
[data-theme="dark"] .schedule-header { background: linear-gradient(to right, var(--surface), var(--surface-2)); border-bottom-color: var(--border); }
[data-theme="dark"] .schedule-title { color: var(--text-primary); }
[data-theme="dark"] .schedule-nav { background: var(--surface-2); }
[data-theme="dark"] .schedule-nav .btn-nav { color: var(--text-secondary); }
[data-theme="dark"] .schedule-nav .btn-nav:hover { background: var(--surface); color: var(--primary-light); }
[data-theme="dark"] .schedule-month-label { color: var(--text-primary); }
[data-theme="dark"] .schedule-grid-wrapper { background: var(--surface-2); }
[data-theme="dark"] .schedule-grid-wrapper::-webkit-scrollbar-track { background: var(--surface-2); }
[data-theme="dark"] .schedule-grid-wrapper::-webkit-scrollbar-thumb { background: #475569; border-color: var(--surface-2); }
[data-theme="dark"] .schedule-grid-wrapper::-webkit-scrollbar-thumb:hover { background: #64748b; }
[data-theme="dark"] .schedule-grid-wrapper::-webkit-scrollbar-corner { background: var(--surface-2); }
[data-theme="dark"] .schedule-table th, [data-theme="dark"] .schedule-table td { border-color: rgba(255,255,255,0.05); }
[data-theme="dark"] .schedule-table th.col-process, [data-theme="dark"] .schedule-table td.col-process { background: var(--surface); color: var(--text-primary); border-right-color: var(--border); }
[data-theme="dark"] .schedule-table thead th { background: var(--surface-2); color: var(--text-secondary); border-bottom-color: var(--border); }
[data-theme="dark"] .schedule-table thead th.col-process { background: var(--surface); color: var(--text-secondary); }
[data-theme="dark"] .schedule-table td.day-cell { background: var(--surface); }
[data-theme="dark"] .schedule-table td.day-cell:hover { background: var(--surface-2); }
[data-theme="dark"] .day-cell.is-today { background: rgba(59, 130, 246, 0.15) !important; border-left: 2px solid var(--primary-light); border-right: 2px solid var(--primary-light); }
[data-theme="dark"] .day-cell.is-weekend { background: repeating-linear-gradient(45deg, rgba(255,255,255,0.02), rgba(255,255,255,0.02) 10px, transparent 10px, transparent 20px); }
[data-theme="dark"] .day-cell.is-downtime { background: rgba(239, 68, 68, 0.1); }
[data-theme="dark"] .cell-date-num { color: var(--text-muted); }
[data-theme="dark"] .is-today .cell-date-num { color: var(--primary-light); }
[data-theme="dark"] .is-weekend .cell-date-num { color: var(--text-muted); opacity: 0.6; }
[data-theme="dark"] .downtime-overlay { background: linear-gradient(180deg, rgba(0,0,0,0.5) 0%, transparent 100%); }
[data-theme="dark"] .info-card { background: var(--surface-2); border-color: var(--border); }
[data-theme="dark"] .info-card-value { color: var(--text-primary); }
[data-theme="dark"] .info-card-label { color: var(--text-secondary); }
[data-theme="dark"] .info-today .info-card-icon { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
[data-theme="dark"] .info-tomorrow .info-card-icon { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
[data-theme="dark"] .info-progress .info-card-icon { background: rgba(147, 51, 234, 0.15); color: #c084fc; }
[data-theme="dark"] .info-card-content strong { color: var(--text-primary) !important; }
[data-theme="dark"] .schedule-info-bar { background: var(--surface-2) !important; border-bottom-color: var(--border) !important; }
[data-theme="dark"] .schedule-legend { background: var(--surface-2) !important; border-top-color: var(--border) !important; }
[data-theme="dark"] .legend-title { color: var(--text-primary) !important; }
[data-theme="dark"] .legend-item { color: var(--text-secondary) !important; }
[data-theme="dark"] .context-menu { background: var(--surface); border-color: var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
[data-theme="dark"] .context-menu-title { border-bottom-color: var(--border); color: var(--text-secondary); }
[data-theme="dark"] .context-menu-item { color: var(--text-primary); }
[data-theme="dark"] .context-menu-item:hover { background: rgba(255,255,255,0.05); }
[data-theme="dark"] .modal-content { background: var(--surface); border-color: var(--border); }
[data-theme="dark"] .modal-header { border-bottom-color: var(--border); }
[data-theme="dark"] .modal-footer { border-top-color: var(--border); background: var(--surface-2); }
[data-theme="dark"] .form-label { color: var(--text-secondary); }
[data-theme="dark"] .form-control, [data-theme="dark"] .form-select { background: var(--surface-2); border-color: var(--border); color: var(--text-primary); }
[data-theme="dark"] .form-control:focus, [data-theme="dark"] .form-select:focus { background: var(--surface); border-color: var(--primary); }
[data-theme="dark"] .badge[style*="background: #f8fafc"] { background: var(--surface-2) !important; color: var(--text-secondary) !important; border-color: var(--border) !important; }
[data-theme="dark"] .badge[style*="background: #f0fdf4"] { background: rgba(22, 163, 74, 0.15) !important; color: #4ade80 !important; border-color: rgba(22, 163, 74, 0.3) !important; }
[data-theme="dark"] .badge[style*="background: #fffbeb"] { background: rgba(217, 119, 6, 0.15) !important; color: #fcd34d !important; border-color: rgba(217, 119, 6, 0.3) !important; }
[data-theme="dark"] .schedule-table td.status-done { background: rgba(16,185,129,.1) !important; }
[data-theme="dark"] .schedule-table td.status-in_progress { background: rgba(245,158,11,.1) !important; }

/* ── MODAL & TASK BUILDER DARK MODE FIXES ── */
[data-theme="dark"] .modal-cell-edit .modal-header { background: var(--surface-2) !important; border-bottom-color: var(--border) !important; }
[data-theme="dark"] #cellProcessDisplay, [data-theme="dark"] #cellDayDisplay { background: var(--surface-2) !important; color: var(--text-primary) !important; border-color: var(--border) !important; }
[data-theme="dark"] .task-builder-row { background: var(--surface-2) !important; border-color: var(--border) !important; }
[data-theme="dark"] .task-builder-row .tb-result { background: var(--surface) !important; border-color: var(--border) !important; color: var(--text-primary) !important; }
[data-theme="dark"] .task-builder-row .btn-outline-secondary { background: var(--surface) !important; border-color: var(--border) !important; color: var(--text-primary) !important; }
[data-theme="dark"] .task-builder-row .btn-outline-secondary:hover { background: var(--surface-2) !important; }
[data-theme="dark"] .task-builder-row span[style*="background:#e0e7ff"] { background: rgba(79, 70, 229, 0.2) !important; color: var(--primary-light) !important; }
[data-theme="dark"] #btnAddTaskRow { border-color: var(--border) !important; color: var(--text-secondary) !important; }
[data-theme="dark"] #btnAddTaskRow:hover { border-color: var(--primary-light) !important; color: var(--primary-light) !important; }
[data-theme="dark"] .modal-content[style*="background: linear-gradient"] { background: var(--surface) !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; border-color: var(--border) !important; }
[data-theme="dark"] .tb-field-label { color: var(--text-secondary) !important; }
[data-theme="dark"] .tb-or-divider { color: var(--text-muted) !important; }
[data-theme="dark"] .task-builder-row .form-control { background: var(--surface) !important; border-color: var(--border) !important; color: var(--text-primary) !important; }
[data-theme="dark"] .task-builder-row .form-control:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2) !important; }
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
        {{-- COMPACT TOOLBAR: single row with grouped dropdowns --}}
        <div class="d-flex align-items-center gap-1 flex-wrap">
            {{-- Today --}}
            <a href="{{ route('schedule.index', ['year' => now()->year, 'month' => now()->month]) }}#today-col"
               class="btn btn-sm btn-outline-primary" title="Jump to today">
                <i class="bi bi-calendar-event"></i>
                <span class="d-none d-md-inline ms-1">Today</span>
            </a>

            {{-- Batch Mark Status --}}
            <button type="button" class="btn btn-sm btn-outline-success" id="batchModeBtn"
                    onclick="toggleBatchMode()" title="Batch mark cells as Done / In Progress">
                <i class="bi bi-check2-square"></i>
                <span class="d-none d-md-inline ms-1">Mark</span>
            </button>

            {{-- Operations dropdown --}}
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i>
                    <span class="d-none d-md-inline ms-1">Actions</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Schedule</h6></li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#urgentTaskModal">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Urgent Task
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#downtimeModal">
                            <i class="bi bi-tools text-warning me-2"></i>Delay / Downtime Event
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Reports</h6></li>
                    <li>
                        <button class="dropdown-item" onclick="openDelayModal()">
                            <i class="bi bi-journal-text text-warning me-2"></i>Delay & Work Summary
                        </button>
                    </li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#telegramAlertModal">
                            <i class="bi bi-telegram text-info me-2"></i>Alert Telegram
                        </button>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('schedule.export', ['year' => $year, 'month' => $month, 'format' => 'html']) }}" target="_blank">
                            <i class="bi bi-calendar-week text-success me-2"></i>Calendar View / PDF
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
                    <li><h6 class="dropdown-header">Manage</h6></li>
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#copyMonthModal">
                            <i class="bi bi-clipboard-plus me-2"></i>Copy to Next Month
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

    {{-- INFO PANELS: Today + Tomorrow + Progress --}}
    <div class="schedule-info-bar">
        {{-- Today's Tasks --}}
        <div class="info-card info-today">
            <div class="info-card-icon"><i class="bi bi-lightning-fill"></i></div>
            <div class="info-card-content">
                <strong>ថ្ងៃនេះ ({{ now()->format('d/m') }})</strong>
                @if($todayTasks->count() > 0)
                    <div class="info-tasks mt-1">
                        @foreach($todayTasks as $t)
                            @php $clr = $processColors[$t->process] ?? '#475569'; @endphp
                            <span class="info-task-badge" style="background: linear-gradient(135deg, {{ $clr }}ee, {{ $clr }});">{{ $t->process }}: {{ $t->task }}</span>
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
                    <div class="info-tasks mt-1">
                        @foreach($tomorrowTasks as $t)
                            @php $clr = $processColors[$t->process] ?? '#475569'; @endphp
                            <span class="info-task-badge" style="background: linear-gradient(135deg, {{ $clr }}ee, {{ $clr }});">{{ $t->process }}: {{ $t->task }}</span>
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
                <div class="d-flex flex-wrap gap-2 mt-1">
                    <span class="badge" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">📋 {{ $totalTasks }} កិច្ចការសរុប</span>
                    <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">✅ {{ $pastTasks }} បានឆ្លងកាត់</span>
                    <span class="badge" style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a;">⏳ នៅសល់ {{ $daysLeft }} ថ្ងៃ</span>
                </div>
            </div>
        </div>
    </div>

    {{-- SCROLLABLE GRID --}}
    <div class="schedule-grid-wrapper">
        <table class="schedule-table">
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
                            <span style="color: {{ $color }}; font-weight: 700;">{{ $process }}</span>
                        </td>
                        @foreach($days as $dayInfo)
                            @php
                                $d = $dayInfo['day'];
                                $entry = $procByDay->get($d);
                                $isWeekend = $dayInfo['dow'] === 0; // Sunday only
                                $hasTasks = $entry && $entry->task;
                                $isToday = ($year == now()->year && $month == now()->month && $d == now()->day);
                                $todayId = $isToday ? 'id="today-col"' : '';
                                $cellClass = 'day-cell';
                                if ($isWeekend && !$hasTasks) $cellClass .= ' weekend';
                                if ($isToday) $cellClass .= ' today';
                            @endphp
                            @php
                                $cellStatus = $entry->status ?? 'planned';
                                $statusOverlay = '';
                                if($cellStatus === 'done') $statusOverlay = 'background:rgba(16,185,129,.08);';
                                elseif($cellStatus === 'in_progress') $statusOverlay = 'background:rgba(245,158,11,.08);';
                            @endphp
                            <td class="{{ $cellClass }} status-{{ $entry->status ?? 'planned' }}"
                                data-process="{{ $process }}"
                                data-day="{{ $d }}"
                                data-task="{{ $entry->task ?? '' }}"
                                data-note="{{ $entry->note ?? '' }}"
                                data-color="{{ $entry->color ?? '' }}"
                                data-status="{{ $entry->status ?? 'planned' }}"
                                onclick="handleCellClick(event, this)"
                                style="{{ $statusOverlay }}">
                                {{-- Batch checkbox (hidden until batch mode) --}}
                                <span class="batch-cb" onclick="event.stopPropagation()" style="display:none;position:absolute;top:2px;left:2px;">
                                    <input type="checkbox" class="form-check-input batch-check"
                                           data-process="{{ $process }}" data-day="{{ $d }}"
                                           style="width:14px;height:14px;cursor:pointer;">
                                </span>
                                @if($entry && $entry->task)
                                    @php
                                        $taskList = array_filter(array_map('trim', explode(',', $entry->task)));
                                    @endphp
                                    @if(count($taskList) > 3)
                                        <div style="position:absolute;top:2px;right:2px;font-size:.7rem;animation: pulse 2s infinite;" title="Warning: Overload ({{ count($taskList) }} tasks)">⚠️</div>
                                    @endif
                                    @foreach($taskList as $singleTask)
                                        @if($singleTask)
                                            <span class="cell-task" style="background: linear-gradient(135deg, {{ $entry->color ?: $color }}cc, {{ $entry->color ?: $color }}); color: #fff;" title="{{ $singleTask }}">
                                                {{ $singleTask }}
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
        <span class="legend-title">LEGEND — Stage Colours:</span>
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
        <span class="legend-item ms-3" style="border-left:1px solid #e2e8f0;padding-left:.75rem;">
            <span style="font-size:.68rem;">✅</span> Done
        </span>
        <span class="legend-item">
            <span style="font-size:.68rem;">🔄</span> In Progress
        </span>
        <span class="legend-item ms-2" style="font-size:.7rem;color:#7c3aed;">
            <i class="bi bi-check2-square me-1"></i>Right-click cell or use <strong>Mark</strong> button to set status
        </span>
    </div>
</div>

{{-- BATCH STATUS TOOLBAR (floats at bottom when batch mode active) --}}
<div class="batch-toolbar" id="batchToolbar">
    <span id="batchCount">0 selected</span>
    <button class="btn btn-sm btn-success" onclick="applyBatchStatus('done')">
        <i class="bi bi-check-circle-fill me-1"></i>Mark Done
    </button>
    <button class="btn btn-sm btn-warning" onclick="applyBatchStatus('in_progress')">
        <i class="bi bi-arrow-repeat me-1"></i>In Progress
    </button>
    <button class="btn btn-sm btn-secondary" onclick="applyBatchStatus('planned')">
        <i class="bi bi-clock me-1"></i>Planned
    </button>
    <button class="btn btn-sm btn-outline-light ms-2" onclick="toggleBatchMode()">
        <i class="bi bi-x-lg"></i> Exit
    </button>
</div>

{{-- DELAY LOG / WORK SUMMARY MODAL (inline, no new tab) --}}
<div class="modal fade modal-xl" id="delayLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;">
            <div class="modal-header" style="background:#fffbeb;border-radius:16px 16px 0 0;">
                <h6 class="modal-title">
                    <i class="bi bi-clipboard-data text-warning me-2"></i>
                    Monthly Work Summary
                    <span id="dlModalMonth" class="ms-2 text-muted" style="font-weight:400;font-size:.82rem;"></span>
                </h6>
                <div class="d-flex gap-2 ms-auto me-3">
                    <a id="dlFullPageBtn" href="{{ route('schedule.delay-report', ['year'=>$year,'month'=>$month]) }}"
                       class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Full Page
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="overflow-y:auto;padding:1.25rem;" id="delayLogBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <div class="mt-2 text-muted" style="font-size:.82rem;">Loading summary...</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- EDIT CELL MODAL --}}
<div class="modal fade modal-cell-edit" id="cellModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="cellForm" method="POST" action="{{ route('schedule.store') }}" style="border-radius: var(--radius-lg); border: none; max-height:95dvh;">
            <div class="modal-header" style="background: #f8fafc; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h6 class="modal-title" id="cellModalTitle">
                    <i class="bi bi-pencil-square text-primary"></i>
                    កែប្រែកាលវិភាគ
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="process" id="cellProcess">
                <input type="hidden" name="day" id="cellDay">
                <input type="hidden" name="task" id="cellTaskHidden">
                <div class="modal-body" style="padding:1.25rem;">

                    {{-- Process + Day (read-only) --}}
                    <div class="d-flex gap-2 mb-3">
                        <div style="flex:1">
                            <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;">ដំណើរការ</label>
                            <input type="text" class="form-control form-control-sm" id="cellProcessDisplay" readonly style="background:#f1f5f9;font-weight:700;">
                        </div>
                        <div style="flex:1">
                            <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;">ថ្ងៃចាប់ផ្ដើម</label>
                            <input type="text" class="form-control form-control-sm" id="cellDayDisplay" readonly style="background:#f1f5f9;">
                        </div>
                    </div>

                    {{-- Task List --}}
                    <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;">
                        កិច្ចការ
                        <span style="font-size:.7rem;color:#64748b;font-weight:400;">— add one or more, each with its own span</span>
                    </label>
                    <div id="taskBuilderList" style="display:flex;flex-direction:column;gap:0.5rem;"></div>

                    <button type="button" id="btnAddTaskRow"
                            style="margin-top:.5rem;font-size:.78rem;background:none;border:1.5px dashed #94a3b8;
                                   border-radius:6px;padding:.3rem .8rem;color:#64748b;cursor:pointer;width:100%;
                                   transition:.15s;" onmouseover="this.style.borderColor='#4f46e5';this.style.color='#4f46e5';"
                            onmouseout="this.style.borderColor='#94a3b8';this.style.color='#64748b';">
                        <i class="bi bi-plus-circle"></i> បន្ថែមកិច្ចការ
                    </button>

                    {{-- Preview strip --}}
                    <div id="taskPreviewStrip" style="margin-top:.6rem;display:none;">
                        <div style="font-size:.7rem;color:#64748b;margin-bottom:.25rem;">Preview — ថ្ងៃដែលត្រូវវាង:</div>
                        <div id="taskPreviewDays" style="display:flex;flex-wrap:wrap;gap:4px;"></div>
                    </div>

                    {{-- Note + Color --}}
                    <div class="d-flex gap-2 mt-3">
                        <div style="flex:1">
                            <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;">កំណត់ចំណាំ</label>
                            <input type="text" class="form-control form-control-sm" name="note" id="cellNote"
                                   placeholder="ចំណាំ...">
                        </div>
                        <div>
                            <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;">ពណ៌</label>
                            <div class="d-flex flex-wrap gap-1" style="max-width:180px;">
                                @foreach($processColors as $proc => $clr)
                                    <label class="color-option" style="cursor:pointer;">
                                        <input type="radio" name="color" value="{{ $clr }}" class="d-none color-radio">
                                        <span class="legend-dot" style="background:{{ $clr }};width:20px;height:20px;display:block;border-radius:3px;border:2px solid transparent;" title="{{ $proc }}"></span>
                                    </label>
                                @endforeach
                                <label class="color-option" style="cursor:pointer;">
                                    <input type="radio" name="color" value="" class="d-none color-radio" checked>
                                    <span class="legend-dot" style="background:#e2e8f0;width:20px;height:20px;display:block;border-radius:3px;border:2px solid transparent;" title="Default"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    {{-- Schedule Options --}}
                    <div class="mt-3 pt-3" style="border-top: 1px dashed var(--border);">
                        <div class="form-check form-switch mb-0" style="font-size:.78rem; font-weight: 600;">
                            <input class="form-check-input" type="checkbox" role="switch" name="include_sundays" id="includeSundays" value="1">
                            <label class="form-check-label" for="includeSundays" style="cursor:pointer;" title="If checked, tasks will be scheduled on Sundays instead of skipping them.">
                                រួមបញ្ចូលថ្ងៃអាទិត្យ (Include Sundays for Rush Jobs)
                            </label>
                        </div>
                    </div>

                    {{-- Copy to other processes --}}
                    <div class="mt-4 pt-3" style="border-top: 1px dashed var(--border);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0" style="font-size:.78rem;font-weight:600;">
                                ចម្លងទៅផ្នែកផ្សេងទៀត (Copy to other processes)
                            </label>
                            <div class="form-check form-switch mb-0" style="font-size:.75rem;">
                                <input class="form-check-input" type="checkbox" role="switch" name="copy_1_day" id="copy1Day" value="1" checked>
                                <label class="form-check-label text-muted" for="copy1Day" style="cursor:pointer;" title="If checked, ignores multi-day duration and copies as a 1-day task.">រយៈពេល ១ថ្ងៃ (1 day duration)</label>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2" id="copyProcessContainer">
                            @foreach($processes as $p)
                                <label class="btn btn-outline-secondary btn-sm process-checkbox-wrapper" style="font-size:.75rem; border-radius: 20px;">
                                    <input type="checkbox" name="copy_processes[]" value="{{ $p }}" class="d-none process-checkbox" onchange="this.parentElement.classList.toggle('btn-primary', this.checked); this.parentElement.classList.toggle('btn-outline-secondary', !this.checked); this.parentElement.classList.toggle('text-white', this.checked);">
                                    {{ $p }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);">
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCell">
                        <i class="bi bi-trash3"></i> ជម្រះ
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="openMoveModal()">
                        <i class="bi bi-arrows-move"></i> ផ្លាស់ទី (Move)
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnSaveCell">
                        <i class="bi bi-check-lg"></i> រក្សាទុក
                    </button>
                </div>
        </form>
    </div>
</div>

{{-- MOVE CELL MODAL --}}
<div class="modal fade" id="moveCellModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content" method="POST" action="{{ route('schedule.move') }}" style="border-radius: var(--radius-lg); border: none;">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="from_process" id="moveFromProcess">
            <input type="hidden" name="from_day" id="moveFromDay">
            
            <div class="modal-header" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-radius: var(--radius-lg) var(--radius-lg) 0 0; border-bottom: 1px solid #7dd3fc;">
                <h6 class="modal-title" style="color: #0369a1; font-weight: 800; letter-spacing: -0.01em;">
                    <i class="bi bi-arrows-move me-2"></i> ផ្លាស់ទីកិច្ចការ
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem; background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
                <p style="font-size:.85rem; color: #64748b; margin-bottom: 1rem;">
                    រំកិលកិច្ចការទាំងអស់ក្នុងប្រអប់នេះទៅទីតាំងថ្មីដោយស្វ័យប្រវត្តិ។ (Move all tasks to a new location)
                </p>
                <div class="mb-4">
                    <label class="form-label premium-label">ផ្នែកថ្មី (New Process)</label>
                    <select name="to_process" id="moveToProcess" class="form-select premium-input" required>
                        @foreach($processes as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label premium-label">ថ្ងៃថ្មី (New Day)</label>
                    <input type="number" name="to_day" id="moveToDay" class="form-control premium-input" min="1" max="{{ $daysInMonth }}" required>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                <button type="submit" class="btn btn-sm btn-info text-white" style="font-weight: 600; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);">
                    <i class="bi bi-check-circle"></i> Confirm Move
                </button>
            </div>
        </form>
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
        <div class="modal-content" style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 25px 50px -12px rgba(220, 38, 38, 0.25); background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
            <div class="modal-header" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-radius: 20px 20px 0 0; border-bottom: 1px solid #fecaca; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight: 800; color: #991b1b; letter-spacing: -0.01em;"><i class="bi bi-exclamation-triangle-fill text-danger me-2" style="font-size: 1.2rem;"></i> Urgent Task <span style="font-weight: 400; opacity: 0.7;">/ ការងារបន្ទាន់</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 1.75rem;">
                {{-- Mode selector --}}
                <div class="mb-4">
                    <label class="premium-label">Mode</label>
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <label class="premium-radio-card active flex-fill" for="modeNew" id="labelModeNew">
                            <input class="form-check-input" type="radio" name="urgentMode" id="modeNew" value="new" checked onchange="toggleUrgentMode()">
                            <div>
                                <strong style="display:block;color:#991b1b;margin-bottom:2px;">New Urgent Work</strong>
                                <span style="font-size:0.8rem;color:#64748b;">New job arrives, push planned work forward</span>
                            </div>
                        </label>
                        <label class="premium-radio-card flex-fill" for="modeExisting" id="labelModeExisting">
                            <input class="form-check-input" type="radio" name="urgentMode" id="modeExisting" value="existing" onchange="toggleUrgentMode()">
                            <div>
                                <strong style="display:block;color:#991b1b;margin-bottom:2px;">Existing Work is Urgent</strong>
                                <span style="font-size:0.8rem;color:#64748b;">Move already-planned work to an earlier day</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="premium-label">Process *</label>
                        <select class="form-select premium-input" id="urgentProcess">
                            <option value="Design">Design</option>
                            <option value="Press" selected>Press</option>
                            <option value="Digital">Digital</option>
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
                        <label class="premium-label" id="urgentDayLabel">Start Day (in this month) *</label>
                        <input type="number" class="form-control premium-input" id="urgentDay" value="{{ now()->day }}"
                               min="1" max="{{ $daysInMonth }}" style="font-family:var(--font-latin)">
                    </div>
                </div>

                {{-- New mode fields --}}
                <div id="newModeFields">
                    <div class="mb-4">
                        <label class="premium-label">Task Name *</label>
                        <input type="text" class="form-control premium-input" id="urgentName"
                               placeholder="e.g. Emergency Reprint Level 1" list="taskSuggestions2">
                        <datalist id="taskSuggestions2">
                            <option value="Listening Textbook"><option value="Listening Workbook">
                            <option value="Reading Textbook"><option value="Reading Workbook">
                            <option value="Writing Textbook"><option value="Test Book">
                            <option value="All Cover"><option value="Song">
                        </datalist>
                    </div>
                    <div class="mb-4">
                        <label class="premium-label">Duration (working days) *</label>
                        <input type="number" class="form-control premium-input" id="urgentDuration" value="1" min="1" max="30"
                               style="font-family:var(--font-latin);width:120px">
                    </div>
                </div>

                {{-- Existing mode fields --}}
                <div id="existingModeFields" style="display:none">
                    <div class="mb-4">
                        <label class="premium-label">Which task to make urgent? *</label>
                        <input type="text" class="form-control premium-input" id="urgentExistingName"
                               placeholder="Type exact task name as on the grid (e.g. Listening Textbook)">
                        <div class="form-text" style="font-size: 0.8rem; margin-top: 0.4rem; color: #64748b;">This task will be moved to the day above. Work between that day and its current position will shift forward.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="premium-label">Reason / Why urgent?</label>
                    <input type="text" class="form-control premium-input" id="urgentReason"
                           placeholder="e.g. Customer order, Deadline moved up">
                </div>

                <div style="background: linear-gradient(135deg, #fef2f2 0%, #fff 100%); border: 1px solid #fca5a5; border-radius: 12px; padding: 1rem; font-size: .85rem; color: #991b1b; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.05);">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2 mt-1" style="font-size: 1.1rem;"></i>
                        <div>
                            <strong style="display:block; margin-bottom: 0.2rem;">Auto-shift Enabled</strong>
                            Tasks on the same process that are displaced will automatically move to the next available working day, and a delay log is recorded for the monthly report.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1.25rem 1.75rem; border-top: 1px solid #e2e8f0; background: rgba(248, 250, 252, 0.5); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitUrgentTask()" style="border-radius: 8px; font-weight: 600; padding: 0.55rem 1.25rem; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Apply Urgent
                </button>
            </div>
        </div>
    </div>
</div>

{{-- DELAY / DOWNTIME MODAL --}}
<div class="modal fade modal-downtime" id="downtimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 25px 50px -12px rgba(245, 158, 11, 0.25); background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
            <div class="modal-header" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 20px 20px 0 0; border-bottom: 1px solid #fde68a; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight: 800; color: #92400e; letter-spacing: -0.01em;"><i class="bi bi-tools text-warning me-2" style="font-size: 1.2rem;"></i> Delay / Downtime <span style="font-weight: 400; opacity: 0.7;">/ ពន្យារពេល</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 1.75rem;">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="premium-label">Process (Row) *</label>
                        <select class="form-select premium-input" id="downtimeProcess">
                            <option value="Design">Design</option>
                            <option value="Press" selected>Press</option>
                            <option value="Digital">Digital</option>
                            <option value="Folding">Folding</option>
                            <option value="Gathering">Gathering</option>
                            <option value="Staple">Staple</option>
                            <option value="Binding">Binding</option>
                            <option value="Cutting">Cutting</option>
                            <option value="Packaging">Packaging</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="form-text" style="font-size: 0.8rem; margin-top: 0.4rem; color: #64748b;">Which production row is affected?</div>
                    </div>
                    <div class="col-md-6">
                        <label class="premium-label">Start Day (in this month) *</label>
                        <input type="number" class="form-control premium-input" id="downtimeDay" value="{{ now()->day }}"
                               min="1" max="{{ $daysInMonth }}" style="font-family:var(--font-latin)">
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="premium-label">Delay Duration (working days) *</label>
                        <input type="number" class="form-control premium-input" id="downtimeDays" value="1" min="0.5" max="30" step="0.5"
                               style="font-family:var(--font-latin)">
                        <div class="form-text" style="font-size: 0.8rem; margin-top: 0.4rem; color: #64748b;">e.g. 0.5 for half day, 1 for full day</div>
                    </div>
                    <div class="col-md-6">
                        <label class="premium-label">Machine (if breakdown)</label>
                        <select class="form-select premium-input" id="downtimeMachine">
                            <option value="">— N/A (General Delay) —</option>
                            @foreach(\App\Models\Machine::all() as $m)
                                <option value="{{ $m->id }}">{{ $m->code }} — {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="premium-label">Reason / Event *</label>
                    <input type="text" class="form-control premium-input" id="downtimeReason"
                           placeholder="e.g. Press breakdown, Material shortage, Client delay">
                </div>
                <div class="mb-4">
                    <label class="premium-label">Rescheduling Strategy</label>
                    <select class="form-select premium-input" id="downtimeStrategy">
                        <option value="shift_workflow" selected>Shift Entire Workflow (Recommended)</option>
                        <option value="shift_single">Shift Only This Process</option>
                    </select>
                </div>
                <div style="background: linear-gradient(135deg, #fffbeb 0%, #fff 100%); border: 1px solid #fde68a; border-radius: 12px; padding: 1rem; font-size: .85rem; color: #92400e; box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.05);">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2 mt-1" style="font-size: 1.1rem;"></i>
                        <div>
                            <strong style="display:block; margin-bottom: 0.2rem;">Cascade Shift Enabled</strong>
                            Depending on the strategy, tasks on the selected process row (and potentially downstream processes) from this day forward will shift by the downtime duration. A delay log entry is recorded for each shifted task.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1.25rem 1.75rem; border-top: 1px solid #e2e8f0; background: rgba(248, 250, 252, 0.5); border-radius: 0 0 20px 20px; flex-wrap: nowrap;">
                <a href="{{ route('schedule.delay-report', ['year'=>$year,'month'=>$month]) }}" class="btn btn-outline-secondary me-auto" style="border-radius: 8px; font-weight: 600; padding: 0.55rem 1rem;" target="_blank">
                    <i class="bi bi-journal-text me-1"></i> View Log
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitDowntime()" style="border-radius: 8px; font-weight: 600; padding: 0.55rem 1.25rem; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                    <i class="bi bi-tools me-1"></i> Apply Delay
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('success')){{-- Toast handled by layout --}}@endif
@if(session('error')){{-- Toast handled by layout --}}@endif

<script>
// ═══════════════════════════════════════════════════════
// TASK BUILDER — production, Mon-Sat, skip Sunday only
// ═══════════════════════════════════════════════════════
const TASK_SUGGESTIONS = [
    'All Cover','Listening Textbook','Listening Workbook',
    'Reading Textbook','Reading Workbook','Writing Textbook',
    'Writing Workbook','Song','Folktale','Grammar',
    'Test Book','Teacher Book','Level 1','Level 2',
    'Level 3','Level 4','Level 5','Level 6','Delivery','Maintenance'
];

// Sunday = 0, Saturday = 6. Only Sunday is a holiday.
function isSunday(year, month, day) {
    return new Date(year, month - 1, day).getDay() === 0;
}

// Get working days a task occupies (Mon-Sat, skip Sunday)
function getTaskDays(year, month, startDay, span) {
    const dim = new Date(year, month, 0).getDate();
    const result = [];
    let day = startDay, count = 0;
    while (count < span && day <= dim) {
        if (!isSunday(year, month, day)) { result.push(day); count++; }
        day++;
    }
    return result;
}

// Short Khmer day name
function dayName(year, month, day) {
    return ['អា','ច','អ','ព','ព្រ','សុ','ស'][new Date(year, month-1, day).getDay()];
}

let _modalYear, _modalMonth, _modalStartDay;
let _taskRows = [];
let _rowCounter = 0;

// Calculate span: children mode wins over manual days
function calcSpan(row) {
    const ch = parseFloat(row.totalChildren) || 0;
    const dy = parseFloat(row.dailyChildren) || 0;
    const mn = parseInt(row.manualDays) || 0;
    if (ch > 0 && dy > 0) return Math.ceil(ch / dy);
    return mn > 0 ? mn : 1;
}

function rebuildPreview() {
    const strip = document.getElementById('taskPreviewStrip');
    const cont  = document.getElementById('taskPreviewDays');
    if (!cont) return;
    const year = _modalYear, month = _modalMonth;
    const dim  = new Date(year, month, 0).getDate();

    // All tasks start from the same day (parallel)
    let startDay = _modalStartDay;
    while (startDay <= dim && isSunday(year, month, startDay)) startDay++;

    const colors = ['#4f46e5','#0ea5e9','#16a34a','#d97706','#dc2626','#7c3aed','#0891b2'];
    const groups = [];

    _taskRows.forEach((row, idx) => {
        const span = calcSpan(row);
        const days = getTaskDays(year, month, startDay, span); // each task resets to startDay
        if (!days.length) return;
        const clr  = colors[idx % colors.length];
        const name = row.name || '?';

        const dayChips = days.map((d, i) =>
            `<span style="display:inline-flex;align-items:center;gap:2px;background:${clr};color:#fff;` +
            `border-radius:4px;padding:2px 7px;font-size:.7rem;font-weight:600;">` +
            `${dayName(year,month,d)}&nbsp;${d}` +
            (span > 1 ? `<span style="opacity:.65;font-size:.62rem;">${i+1}/${span}</span>` : '') +
            `</span>`
        ).join('');

        groups.push(
            `<div style="display:flex;align-items:center;flex-wrap:wrap;gap:3px;margin-bottom:3px;">` +
            `<span style="font-size:.68rem;color:#64748b;white-space:nowrap;max-width:90px;` +
                   `overflow:hidden;text-overflow:ellipsis;" title="${name}">${name}:</span>` +
            dayChips +
            `</div>`
        );
        // No cursor advance — next task also starts from startDay
    });

    if (groups.length) {
        cont.innerHTML = groups.join('');
        strip.style.display = 'block';
    } else {
        strip.style.display = 'none';
    }
    buildHiddenTask();
}

function buildHiddenTask() {
    const year = _modalYear, month = _modalMonth;
    const dim  = new Date(year, month, 0).getDate();
    let startDay = _modalStartDay;
    while (startDay <= dim && isSunday(year, month, startDay)) startDay++;

    const parts = [];
    _taskRows.forEach(row => {
        const span = calcSpan(row);
        const name = (row.name || '').trim();
        if (!name) return;
        let str = name;
        if (span > 1) str += ` [${span}d]`;
        const ch = parseFloat(row.totalChildren) || 0;
        const dy = parseFloat(row.dailyChildren) || 0;
        if (ch > 0 && dy > 0) str += ` {${ch}|${dy}/day}`;
        parts.push(str);
        // No cursor advance — parallel start
    });

    document.getElementById('cellTaskHidden').value = parts.join(', ');
}

function updateRowResult(el, row) {
    const span = calcSpan(row);
    const res  = el.querySelector('.tb-result');
    const ch = parseFloat(row.totalChildren)||0, dy = parseFloat(row.dailyChildren)||0;
    if (ch > 0 && dy > 0) {
        res.style.cssText = 'min-width:52px;text-align:center;font-size:.82rem;font-weight:700;padding:4px 8px;border-radius:5px;border:1.5px solid #06b6d4;background:#ecfeff;color:#0e7490;';
        res.textContent = span + ' ថ្ងៃ';
    } else if (span > 1) {
        res.style.cssText = 'min-width:52px;text-align:center;font-size:.82rem;font-weight:700;padding:4px 8px;border-radius:5px;border:1.5px solid #86efac;background:#f0fdf4;color:#15803d;';
        res.textContent = span + ' ថ្ងៃ';
    } else {
        res.style.cssText = 'min-width:52px;text-align:center;font-size:.82rem;font-weight:700;padding:4px 8px;border-radius:5px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#94a3b8;';
        res.textContent = '1 ថ្ងៃ';
    }
}

function renderTaskRow(rowData) {
    const id = rowData.id;
    const el = document.createElement('div');
    el.className = 'task-builder-row';
    el.dataset.rowId = id;
    el.style.cssText = 'background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:.65rem .8rem;transition:border-color .15s;';

    // Row number badge
    const rowNum = _taskRows.length; // 1-based index at time of render

    el.innerHTML =
        // ── Name row ──────────────────────────────────────────────
        `<div style="display:flex;gap:.4rem;align-items:center;margin-bottom:.5rem;">` +
            `<span style="background:#e0e7ff;color:#4f46e5;border-radius:5px;padding:2px 7px;` +
                   `font-size:.68rem;font-weight:700;white-space:nowrap;flex-shrink:0;">#${rowNum}</span>` +
            `<input type="text" class="form-control form-control-sm tb-name" value="${rowData.name}" ` +
                `placeholder="ឈ្មោះកិច្ចការ..." list="tbSuggestions" autocomplete="off" ` +
                `style="flex:1;font-size:.9rem;min-height:38px;">` +
            `<button type="button" onclick="duplicateTaskRow(${id})" ` +
                `style="background:none;border:none;color:#3b82f6;font-size:1.1rem;cursor:pointer;` +
                       `padding:4px 6px;line-height:1;border-radius:5px;flex-shrink:0;" ` +
                `title="ចម្លងកិច្ចការ (Duplicate)">` +
                `<i class="bi bi-files"></i></button>` +
            `<button type="button" onclick="removeTaskRow(${id})" ` +
                `style="background:none;border:none;color:#ef4444;font-size:1.1rem;cursor:pointer;` +
                       `padding:4px 6px;line-height:1;border-radius:5px;flex-shrink:0;" ` +
                `title="លុបកិច្ចការ">` +
                `<i class="bi bi-x-lg"></i></button>` +
        `</div>` +
        // ── Duration fields ────────────────────────────────────────
        `<div class="tb-fields-grid">` +

            // Manual days
            `<div class="tb-field-group">` +
                `<label class="tb-field-label">📅 ថ្ងៃ</label>` +
                `<div class="input-group">` +
                    `<button class="btn btn-outline-secondary" type="button" onclick="stepInput(this, -1)" style="padding:0 8px;font-weight:bold;min-height:38px;background:#fff;">-</button>` +
                    `<input type="number" class="form-control tb-days" ` +
                        `value="${rowData.manualDays||''}" placeholder="—" min="1" max="60" ` +
                        `style="text-align:center;font-weight:700;font-size:.9rem;min-height:38px;padding:0 4px;">` +
                    `<button class="btn btn-outline-secondary" type="button" onclick="stepInput(this, 1)" style="padding:0 8px;font-weight:bold;min-height:38px;background:#fff;">+</button>` +
                `</div>` +
            `</div>` +

            // OR divider
            `<div class="tb-or-divider">ឬ</div>` +

            // Children
            `<div class="tb-field-group">` +
                `<label class="tb-field-label">📄 ចំនួនកូន</label>` +
                `<div class="input-group">` +
                    `<button class="btn btn-outline-secondary" type="button" onclick="stepInput(this, -1)" style="padding:0 8px;font-weight:bold;min-height:38px;background:#fff;">-</button>` +
                    `<input type="number" class="form-control tb-children" ` +
                        `value="${rowData.totalChildren||''}" placeholder="100" min="0" ` +
                        `style="text-align:center;font-size:.9rem;min-height:38px;padding:0 4px;">` +
                    `<button class="btn btn-outline-secondary" type="button" onclick="stepInput(this, 1)" style="padding:0 8px;font-weight:bold;min-height:38px;background:#fff;">+</button>` +
                `</div>` +
            `</div>` +

            // Divide symbol
            `<div class="tb-or-divider">÷</div>` +

            // Daily capacity
            `<div class="tb-field-group">` +
                `<label class="tb-field-label">⚡ ក្នុង១ថ្ងៃ</label>` +
                `<div class="input-group">` +
                    `<button class="btn btn-outline-secondary" type="button" onclick="stepInput(this, -1)" style="padding:0 8px;font-weight:bold;min-height:38px;background:#fff;">-</button>` +
                    `<input type="number" class="form-control tb-daily" ` +
                        `value="${rowData.dailyChildren||''}" placeholder="25" min="1" ` +
                        `style="text-align:center;font-size:.9rem;min-height:38px;padding:0 4px;">` +
                    `<button class="btn btn-outline-secondary" type="button" onclick="stepInput(this, 1)" style="padding:0 8px;font-weight:bold;min-height:38px;background:#fff;">+</button>` +
                `</div>` +
            `</div>` +

            // Equals + result
            `<div class="tb-or-divider">=</div>` +
            `<div class="tb-field-group">` +
                `<label class="tb-field-label">📊 គិត</label>` +
                `<div class="tb-result" ` +
                    `style="text-align:center;font-size:.88rem;font-weight:700;min-height:38px;` +
                           `display:flex;align-items:center;justify-content:center;` +
                           `border-radius:6px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#94a3b8;` +
                           `padding:4px 6px;">` +
                    `1 ថ្ងៃ` +
                `</div>` +
            `</div>` +

        `</div>`; // end tb-fields-grid

    // Wire events
    el.querySelector('.tb-name').addEventListener('input', e => { rowData.name = e.target.value; rebuildPreview(); });
    el.querySelector('.tb-days').addEventListener('input', e => {
        rowData.manualDays     = e.target.value;
        // Children mode is mutually exclusive — clear it
        rowData.totalChildren  = '';
        rowData.dailyChildren  = '';
        el.querySelector('.tb-children').value = '';
        el.querySelector('.tb-daily').value    = '';
        updateRowResult(el, rowData);
        rebuildPreview();
    });
    el.querySelector('.tb-children').addEventListener('input', e => {
        rowData.totalChildren = e.target.value;
        // Manual days is mutually exclusive — clear it
        rowData.manualDays    = '';
        el.querySelector('.tb-days').value = '';
        updateRowResult(el, rowData);
        rebuildPreview();
    });
    el.querySelector('.tb-daily').addEventListener('input', e => {
        rowData.dailyChildren = e.target.value;
        // Manual days is mutually exclusive — clear it
        rowData.manualDays    = '';
        el.querySelector('.tb-days').value = '';
        updateRowResult(el, rowData);
        rebuildPreview();
    });
    updateRowResult(el, rowData);
    return el;
}

function addTaskRow(data = {}) {
    _rowCounter++;
    const rowData = { id:_rowCounter, name:data.name||'', manualDays:data.manualDays||'',
                      totalChildren:data.totalChildren||'', dailyChildren:data.dailyChildren||'' };
    _taskRows.push(rowData);
    const el = renderTaskRow(rowData);
    document.getElementById('taskBuilderList').appendChild(el);
    rebuildPreview();
    setTimeout(() => el.querySelector('.tb-name').focus(), 50);
}

function removeTaskRow(id) {
    _taskRows = _taskRows.filter(r => r.id !== id);
    const el = document.querySelector(`.task-builder-row[data-row-id="${id}"]`);
    if (el) el.remove();
    // Re-number remaining badges
    document.querySelectorAll('.task-builder-row').forEach((row, i) => {
        const badge = row.querySelector('span[style*="e0e7ff"]');
        if (badge) badge.textContent = '#' + (i + 1);
    });
    rebuildPreview();
}

// Parse stored task string back into row data
function parseStoredTasks(taskStr) {
    if (!taskStr) return [];
    const rows = [];
    // Split on ", " but only at top level (not inside {})
    const tokens = [];
    let depth = 0, cur = '';
    for (const ch of taskStr) {
        if (ch === '{') depth++;
        if (ch === '}') depth--;
        if (ch === ',' && depth === 0) { tokens.push(cur.trim()); cur = ''; }
        else cur += ch;
    }
    if (cur.trim()) tokens.push(cur.trim());

    tokens.forEach(t => {
        // Remove (X/Y) part-labels from stored multi-day cells
        t = t.replace(/\s*\(\d+\/\d+\)/g, '').trim();
        const daysM  = t.match(/\[(\d+)d\]/);
        const childM = t.match(/\{([^}]+)\}/);
        let manualDays='', totalChildren='', dailyChildren='';
        if (childM) {
            const parts = childM[1].split('|');
            totalChildren = parts[0] || '';
            if (parts[1]) dailyChildren = parts[1].replace('/day','');
        } else if (daysM) {
            manualDays = daysM[1];
        }
        const name = t.replace(/\s*\[\d+d\]/g,'').replace(/\s*\{[^}]+\}/g,'').trim();
        if (name) rows.push({ name, manualDays, totalChildren, dailyChildren });
    });
    return rows;
}

// ── Open modal ────────────────────────────────────────────────────────────
function openCellModal(cell) {
    const process = cell.dataset.process;
    const day     = parseInt(cell.dataset.day);
    const task    = cell.dataset.task  || '';
    const note    = cell.dataset.note  || '';
    const color   = cell.dataset.color || '';

    _modalYear     = {{ $year }};
    _modalMonth    = {{ $month }};
    _modalStartDay = day;
    _taskRows      = [];
    _rowCounter    = 0;

    document.getElementById('cellProcess').value       = process;
    document.getElementById('cellDay').value           = day;
    document.getElementById('cellProcessDisplay').value= process;
    document.getElementById('cellDayDisplay').value    =
        String(day).padStart(2,'0') + '/{{ str_pad($month,2,"0",STR_PAD_LEFT) }}/{{ $year }}';
    document.getElementById('cellNote').value          = note;
    document.getElementById('taskBuilderList').innerHTML = '';
    document.getElementById('taskPreviewStrip').style.display = 'none';

    const rows = parseStoredTasks(task);
    if (rows.length) rows.forEach(r => addTaskRow(r));
    else addTaskRow();

    // Color selection
    document.querySelectorAll('.color-radio').forEach(r => {
        r.checked = (r.value === color);
        r.closest('.color-option').querySelector('.legend-dot').style.borderColor =
            r.value === color ? '#4f46e5' : 'transparent';
    });

    // Reset and filter copy processes
    document.querySelectorAll('.process-checkbox-wrapper').forEach(el => {
        const cb = el.querySelector('input');
        cb.checked = false; // Reset checkbox
        el.className = 'btn btn-outline-secondary btn-sm process-checkbox-wrapper'; // Reset classes
        if (cb.value === process) {
            el.style.display = 'none'; // Hide current process
        } else {
            el.style.display = 'inline-block';
        }
    });

    new bootstrap.Modal(document.getElementById('cellModal')).show();
}

// ── Batch / cell click handler ────────────────────────────────────────────
function handleCellClick(e, cell) {
    if (document.body.classList.contains('batch-mode')) {
        const cb = cell.querySelector('.batch-check');
        if (cb) { cb.checked = !cb.checked; updateBatchCount(); }
        return;
    }
    openCellModal(cell);
}

// ── DOMContentLoaded — single handler for all setup ──────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Datalist for task name suggestions
    const dl = document.createElement('datalist');
    dl.id = 'tbSuggestions';
    TASK_SUGGESTIONS.forEach(s => { const o = document.createElement('option'); o.value = s; dl.appendChild(o); });
    document.body.appendChild(dl);

    // Add-row button
    document.getElementById('btnAddTaskRow')?.addEventListener('click', () => addTaskRow());

    // Color radio visual feedback
    document.querySelectorAll('.color-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.color-option .legend-dot').forEach(d => d.style.borderColor = 'transparent');
            this.closest('.color-option').querySelector('.legend-dot').style.borderColor = '#4f46e5';
        });
    });

    // Clear cell button
    document.getElementById('btnClearCell')?.addEventListener('click', () => {
        document.getElementById('cellTaskHidden').value = '';
        document.getElementById('cellNote').value = '';
        document.querySelectorAll('.color-radio').forEach(r => r.checked = r.value === '');
        document.getElementById('cellForm').submit();
    });

    // Ensure hidden task is up-to-date on submit
    document.getElementById('cellForm')?.addEventListener('submit', () => buildHiddenTask());

    // Telegram alert preview
    const daySelect = document.querySelector('select[name="day"]');
    if (daySelect) {
        daySelect.addEventListener('change', function () {
            const preview = document.getElementById('alertPreview');
            if (!preview) return;
            const tasks = (window._monthTasks || {})[this.value];
            if (tasks && tasks.length) {
                // Render each "Process: Task" line with a coloured process badge
                const processColors = {
                    Design:'#4285f4', Press:'#ea4335', Digital:'#8b5cf6', Folding:'#9c27b0',
                    Gathering:'#ff9800', Staple:'#00bcd4', Binding:'#e91e63',
                    Cutting:'#009688', Packaging:'#4caf50', Delivery:'#ff5722', Other:'#607d8b'
                };
                preview.innerHTML = tasks.map(t => {
                    const colon = t.indexOf(':');
                    const proc  = colon > -1 ? t.substring(0, colon).trim() : '';
                    const task  = colon > -1 ? t.substring(colon + 1).trim() : t;
                    const clr   = processColors[proc] || '#475569';
                    return `<div class="prev-item">` +
                        `<span class="prev-badge" style="background:${clr};">${proc}</span>` +
                        `<span class="prev-task">${task}</span>` +
                        `</div>`;
                }).join('');
            } else {
                preview.innerHTML = '<em class="text-muted" style="font-size:.78rem;">គ្មានកិច្ចការសម្រាប់ថ្ងៃនេះ</em>';
            }
        });
        daySelect.dispatchEvent(new Event('change'));
    }

    // Scroll to today column
    const todayCol = document.getElementById('today-col');
    if (todayCol) {
        const wrapper = document.querySelector('.schedule-grid-wrapper');
        if (wrapper) wrapper.scrollLeft = Math.max(0, todayCol.offsetLeft - wrapper.clientWidth / 3);
    }

    // Batch checkbox change
    document.addEventListener('change', e => {
        if (e.target.classList.contains('batch-check')) updateBatchCount();
    });
});

// ── Telegram month-tasks data (for alert preview) ────────────────────────
@php
    $allMonthTasks = \App\Models\ProductionSchedule::where('year', $year)
        ->where('month', $month)
        ->whereNotNull('task')
        ->get()
        ->sortBy(fn($t) => array_search($t->process, $processes)) // match grid order
        ->groupBy('day')
        ->map(fn($dayTasks) => $dayTasks->values()->map(fn($t) => $t->process . ': ' . $t->task)->toArray());
@endphp
window._monthTasks = @json($allMonthTasks);

// ── Batch mode ────────────────────────────────────────────────────────────
let _batchActive = false;

function toggleBatchMode() {
    _batchActive = !_batchActive;
    document.body.classList.toggle('batch-mode', _batchActive);
    const btn = document.getElementById('batchModeBtn');
    if (!btn) return;
    if (_batchActive) {
        btn.classList.replace('btn-outline-success', 'btn-success');
        btn.innerHTML = '<i class="bi bi-x-square"></i> <span class="d-none d-md-inline ms-1">Exit Mark</span>';
    } else {
        btn.classList.replace('btn-success', 'btn-outline-success');
        btn.innerHTML = '<i class="bi bi-check2-square"></i> <span class="d-none d-md-inline ms-1">Mark</span>';
        document.querySelectorAll('.batch-check').forEach(cb => cb.checked = false);
        updateBatchCount();
    }
}

function updateBatchCount() {
    const n  = document.querySelectorAll('.batch-check:checked').length;
    const el = document.getElementById('batchCount');
    if (el) el.textContent = n + ' selected';
}

function applyBatchStatus(status) {
    const checks = document.querySelectorAll('.batch-check:checked');
    if (!checks.length) { showToast('warning', 'No cells selected'); return; }
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    Promise.all(Array.from(checks).map(cb =>
        fetch('/schedule/status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ year:'{{ $year }}', month:'{{ $month }}',
                                   process: cb.dataset.process, day: cb.dataset.day, status })
        }).then(r => r.json())
    )).then(results => {
        const ok = results.filter(r => r.ok).length;
        const fail = results.length - ok;
        if (fail) showToast('warning', ok + ' updated, ' + fail + ' failed (empty cells cannot be marked)');
        else showToast('success', ok + ' cell(s) marked as ' + status.replace('_',' '));
        setTimeout(() => location.reload(), 900);
    }).catch(() => showToast('error', 'Network error — please try again'));
}

// ── Urgent Task ───────────────────────────────────────────────────────────
function toggleUrgentMode() {
    const mode = document.querySelector('input[name="urgentMode"]:checked').value;
    document.getElementById('newModeFields').style.display      = mode === 'new'      ? '' : 'none';
    document.getElementById('existingModeFields').style.display = mode === 'existing' ? '' : 'none';
    document.getElementById('urgentDayLabel').textContent =
        mode === 'existing' ? 'Move TO this day *' : 'Start Day (in this month) *';
}

function submitUrgentTask() {
    const mode    = document.querySelector('input[name="urgentMode"]:checked').value;
    const process = document.getElementById('urgentProcess').value;
    const day     = parseInt(document.getElementById('urgentDay').value);
    const reason  = document.getElementById('urgentReason').value;
    if (!process || !day) { showToast('warning', 'Please select process and day'); return; }
    let taskName = '', duration = 1;
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
    form.action = '{{ route("schedule.urgent") }}';
    const fields = {
        _token: document.querySelector('meta[name="csrf-token"]').content,
        year: '{{ $year }}', month: '{{ $month }}',
        process, urgent_day: day, duration_days: duration,
        task_name: taskName, note: 'URGENT', reason, mode,
    };
    Object.entries(fields).forEach(([k, v]) => {
        const i = document.createElement('input'); i.type='hidden'; i.name=k; i.value=v; form.appendChild(i);
    });
    document.body.appendChild(form); form.submit();
}

// ── Machine Downtime / Delay Event ──────────────────────────────────────
function submitDowntime() {
    const process  = document.getElementById('downtimeProcess').value;
    const day      = parseInt(document.getElementById('downtimeDay').value);
    const days     = parseFloat(document.getElementById('downtimeDays').value) || 1;
    const reason   = document.getElementById('downtimeReason').value.trim();
    const machine  = document.getElementById('downtimeMachine').value;
    const strategy = document.getElementById('downtimeStrategy').value;
    if (!process || !day) { showToast('warning', 'Please select process and start day'); return; }
    if (!reason)          { showToast('warning', 'Please provide a reason for the downtime'); return; }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("schedule.downtime") }}';
    const fields = {
        _token: document.querySelector('meta[name="csrf-token"]').content,
        year: '{{ $year }}', month: '{{ $month }}',
        process, downtime_day: day, downtime_days: days,
        reason: reason,
        machine_id: machine,
        strategy: strategy
    };
    Object.entries(fields).forEach(([k, v]) => {
        const i = document.createElement('input'); i.type='hidden'; i.name=k; i.value=v; form.appendChild(i);
    });
    document.body.appendChild(form); form.submit();
}

// ── Delay log modal ───────────────────────────────────────────────────────
const _processColors = {
    Design:'#4285f4', Press:'#ea4335', Digital:'#8b5cf6', Folding:'#9c27b0', Gathering:'#ff9800',
    Staple:'#00bcd4', Binding:'#e91e63', Cutting:'#009688', Packaging:'#4caf50',
    Delivery:'#ff5722', Other:'#607d8b'
};

function openDelayModal() {
    const el = document.getElementById('delayLogModal');
    if (!el) { window.location.href = '{{ route("schedule.delay-report", ["year"=>$year,"month"=>$month]) }}'; return; }
    new bootstrap.Modal(el).show();
    loadDelayLog();
}

function loadDelayLog() {
    const body = document.getElementById('delayLogBody');
    if (!body) return;
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning"></div>' +
        '<div class="mt-2 text-muted" style="font-size:.82rem;">Loading...</div></div>';
    fetch('{{ route("schedule.delay-json", ["year"=>$year,"month"=>$month]) }}')
        .then(r => r.json()).then(data => {
            const s = data.stats;
            let html = `<div class="d-flex flex-wrap gap-3 mb-3">
                <div class="p-3 rounded border text-center flex-fill"><div style="font-size:1.5rem;font-weight:800;color:#3b82f6;">${s.total}</div><div style="font-size:.72rem;color:#64748b;">Scheduled</div></div>
                <div class="p-3 rounded border text-center flex-fill"><div style="font-size:1.5rem;font-weight:800;color:#ef4444;">${s.urgent}</div><div style="font-size:.72rem;color:#64748b;">Urgent</div></div>
                <div class="p-3 rounded border text-center flex-fill"><div style="font-size:1.5rem;font-weight:800;color:#f59e0b;">${s.downtime}</div><div style="font-size:.72rem;color:#64748b;">Downtime</div></div>
                <div class="p-3 rounded border text-center flex-fill"><div style="font-size:1.5rem;font-weight:800;color:#7c3aed;">+${s.delayDays}</div><div style="font-size:.72rem;color:#64748b;">Days Delayed</div></div>
            </div>`;
            if (Object.keys(data.processSummary).length) {
                html += `<div style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:2px solid #e2e8f0;padding-bottom:.3rem;margin-bottom:.75rem;">Work by Process</div><div class="row g-2 mb-3">`;
                for (const [proc, info] of Object.entries(data.processSummary)) {
                    const clr = _processColors[proc] || '#475569';
                    const chips = Object.entries(info.tasks).map(([t,d]) =>
                        `<span style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:20px;padding:.1rem .5rem;font-size:.7rem;margin:2px;display:inline-block;">${t.replace(/</g,'&lt;')}${d>1?` <span style="background:${clr};color:#fff;border-radius:10px;padding:0 5px;font-size:.62rem;">×${d}d</span>`:''}</span>`
                    ).join('');
                    html += `<div class="col-md-6"><div style="border-left:4px solid ${clr};border:1px solid ${clr}30;border-radius:8px;padding:.6rem .8rem;background:#fff;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span style="background:${clr};color:#fff;border-radius:4px;padding:.1rem .4rem;font-size:.7rem;font-weight:700;">${proc}</span>
                            <span style="font-size:.7rem;color:#64748b;">${info.days}d</span>
                        </div><div>${chips}</div></div></div>`;
                }
                html += `</div>`;
            }
            if (data.logs.length) {
                html += `<div style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:2px solid #e2e8f0;padding-bottom:.3rem;margin-bottom:.75rem;">Delay & Shift Log (${data.logs.length})</div>
                    <div class="table-responsive"><table class="table table-sm table-hover" style="font-size:.78rem;">
                    <thead class="table-light"><tr><th>Process</th><th>Task</th><th class="text-center">Was Day</th><th class="text-center">Moved To</th><th class="text-center">+Days</th><th>Reason</th></tr></thead><tbody>`;
                data.logs.forEach(log => {
                    const clr   = _processColors[log.process] || '#475569';
                    const delay = log.shifted_to_day - log.original_day;
                    const badge = log.reason_type === 'urgent_task'
                        ? `<span class="badge bg-danger">Urgent</span>`
                        : `<span class="badge bg-warning text-dark">Downtime</span>`;
                    html += `<tr><td><span style="background:${clr};color:#fff;border-radius:3px;padding:.1rem .4rem;font-size:.7rem;">${log.process}</span></td>
                        <td><strong>${(log.original_task||'').replace(/</g,'&lt;')}</strong></td>
                        <td class="text-center"><span class="badge bg-secondary">Day ${log.original_day}</span></td>
                        <td class="text-center"><span class="badge bg-primary">Day ${log.shifted_to_day}</span></td>
                        <td class="text-center"><span class="badge ${delay>3?'bg-danger':'bg-warning text-dark'}">${delay>0?'+':''}${delay}d</span></td>
                        <td>${badge}</td></tr>`;
                });
                html += `</tbody></table></div>`;
            } else {
                html += `<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i>No delays recorded this month.</div>`;
            }
            body.innerHTML = html;
        })
        .catch(() => { body.innerHTML = `<div class="alert alert-danger">Failed to load. <a href="{{ route('schedule.delay-report', ['year'=>$year,'month'=>$month]) }}">Open full page</a></div>`; });
}

// ── Clear month confirm ───────────────────────────────────────────────────
function confirmClearMonth() {
    if (confirm('⚠️ Clear ALL schedule data for this month? This cannot be undone.')) {
        document.getElementById('clearMonthForm').submit();
    }
}

// ── Print / Export PDF ────────────────────────────────────────────────────
function exportCalendarPDF() {
    const grid = document.querySelector('.schedule-container');
    const printWin = window.open('', '_blank');
    const safeHtml = grid.querySelector('.schedule-grid-wrapper').innerHTML
        .replace(/<script[\s\S]*?<\/script>/gi, '');   // strip any scripts
    printWin.document.write(`<!DOCTYPE html><html><head>
        <meta charset="UTF-8">
        <title>Production Schedule — {{ $monthName }}</title>
        <style>
            *{margin:0;padding:0;box-sizing:border-box;}
            body{font-family:'Segoe UI',Arial,sans-serif;padding:20px;}
            h1{font-size:18px;margin-bottom:10px;text-align:center;}
            .subtitle{text-align:center;font-size:12px;color:#666;margin-bottom:20px;}
            table{width:100%;border-collapse:collapse;font-size:9px;}
            th,td{border:1px solid #ccc;padding:4px 3px;text-align:center;}
            th{background:#f0f0f0;font-weight:600;}
            td.col-process{text-align:left;font-weight:700;background:#fafafa;width:80px;}
            .cell-task{padding:1px 4px;border-radius:3px;color:#fff;font-size:8px;display:inline-block;}
            .weekend{background:#fffde7;}
            @media print{@page{size:landscape;margin:10mm;}}
        </style>
    </head><body>
        <h1>📅 Production Schedule — {{ $monthName }}</h1>
        <p class="subtitle">Printing Tracker | Generated: ${new Date().toLocaleDateString()}</p>
        ${safeHtml}
        <script>window.onload=function(){window.print();window.close();};<\/script>
    </body></html>`);
    printWin.document.close();
}

// ── Task Builder Helpers ───────────────────────────────────────────────────
function stepInput(btn, delta) {
    const input = btn.parentElement.querySelector('input');
    let val = parseFloat(input.value) || 0;
    val += delta;
    if (input.min && val < parseFloat(input.min)) val = parseFloat(input.min);
    if (input.max && val > parseFloat(input.max)) val = parseFloat(input.max);
    input.value = val;
    // Trigger input event to update previews instantly
    input.dispatchEvent(new Event('input'));
}

function duplicateTaskRow(id) {
    const rowToCopy = _taskRows.find(r => r.id === id);
    if (!rowToCopy) return;
    
    // Create deep copy and generate new unique ID
    const newRowData = JSON.parse(JSON.stringify(rowToCopy));
    newRowData.id = Date.now();
    
    _taskRows.push(newRowData);
    
    // Add row to DOM
    document.getElementById('taskBuilderList').appendChild(renderTaskRow(newRowData));
    
    // Refresh the hidden task string and preview
    rebuildPreview();
}

// ── Move Cell Modal ────────────────────────────────────────────────────────
function openMoveModal() {
    const fromProc = document.getElementById('cellProcess').value;
    const fromDay = document.getElementById('cellDay').value;
    
    if (!fromProc || !fromDay) {
        showToast('warning', 'Please select a cell first');
        return;
    }
    
    document.getElementById('moveFromProcess').value = fromProc;
    document.getElementById('moveFromDay').value = fromDay;
    
    // Default the destination to the current process, and suggest moving it to the next day
    document.getElementById('moveToProcess').value = fromProc;
    let nextDay = parseInt(fromDay) + 1;
    const maxDay = {{ $daysInMonth }};
    if (nextDay > maxDay) nextDay = maxDay;
    document.getElementById('moveToDay').value = nextDay;
    
    // Close the edit modal and open move modal
    const editModalEl = document.getElementById('cellModal');
    const editModal = bootstrap.Modal.getInstance(editModalEl);
    if (editModal) editModal.hide();
    
    new bootstrap.Modal(document.getElementById('moveCellModal')).show();
}
</script>
@endsection
