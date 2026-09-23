@extends('layouts.app')

@section('title', __('reports.daily_tracking'))

@section('content')
<!-- Google Fonts: Kantumruy Pro (Khmer) + Plus Jakarta Sans (Numbers / Latin) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════
   PREMIUM KHMER TYPOGRAPHY SYSTEM (Kantumruy Pro)
═══════════════════════════════════════════════════════ */
:root {
    --font-khmer-modern: 'Kantumruy Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --font-latin-modern: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Enforce Kantumruy Pro across the entire tracking UI, modals, buttons & tables */
.tracking-shell,
.tracking-shell *,
#reportViewModal,
#reportViewModal * {
    font-family: var(--font-khmer-modern) !important;
    letter-spacing: 0.01em;
}

/* Crisp tabular Latin numbers for timestamps, KPI values, and codes */
.font-number,
.kpi-value,
.slot-time,
.date-input-styled,
.font-mono-code,
.staff-meta-tg span {
    font-family: var(--font-latin-modern), var(--font-khmer-modern) !important;
    font-feature-settings: "tnum" 1;
}

/* Base line-height for Khmer script to prevent clipped vowels & subscripts */
.tracking-shell {
    line-height: 1.65;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}

/* ═══════════════════════════════════════════════════════
   RESPONSIVE CANVAS (Desktop Full-Width & Mobile Friendly)
═══════════════════════════════════════════════════════ */
.page-content {
    max-width: 100% !important;
    padding: 1.25rem 1.75rem 3rem !important;
    margin: 0 auto !important;
}

@media (max-width: 991.98px) {
    .page-content {
        padding: 1rem 1rem 90px !important;
    }
}
@media (max-width: 767.98px) {
    .page-content {
        padding: 0.65rem 0.5rem 110px !important; /* Clears floating mobile bottom nav */
    }
}

.tracking-shell {
    --track-primary: #4f46e5;
    --track-primary-rgb: 79, 70, 229;
    --track-success: #10b981;
    --track-warning: #f59e0b;
    --track-danger: #ef4444;
    --track-radius: 16px;
    --track-radius-sm: 10px;
    --track-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
    --track-shadow-lg: 0 12px 32px -4px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
}

/* ═══════════════════════════════════════════════════════
   HERO HEADER & CONTROLS (RESPONSIVE)
═══════════════════════════════════════════════════════ */
.tracking-hero-card {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: var(--track-radius);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--track-shadow);
    margin-bottom: 1.25rem;
    position: relative;
    overflow: hidden;
}
.tracking-hero-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #4f46e5 0%, #06b6d4 50%, #10b981 100%);
}

.hero-icon-badge {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    box-shadow: 0 8px 18px -3px rgba(79, 70, 229, 0.35);
    flex-shrink: 0;
}

/* Date Navigator Capsule */
.date-nav-capsule {
    background: var(--surface-2, #f8fafc);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 4px 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.date-nav-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 1px solid transparent;
    background: transparent;
    color: var(--text-secondary, #475569);
    transition: all 0.2s ease;
    text-decoration: none;
}
.date-nav-btn:hover {
    background: var(--surface, #ffffff);
    color: var(--track-primary);
    border-color: var(--border, #e2e8f0);
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.date-input-styled {
    border: 1px solid var(--border, #e2e8f0);
    background: var(--surface, #ffffff);
    color: var(--text-primary, #0f172a);
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.35rem 0.75rem;
    border-radius: 10px;
    transition: all 0.2s ease;
}
.date-input-styled:focus {
    outline: none;
    border-color: var(--track-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

.btn-today-pill {
    padding: 0.4rem 0.85rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}
.btn-today-pill.is-active {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}
.btn-today-pill:not(.is-active) {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    color: var(--text-secondary, #475569);
}

/* Action Toolbar Buttons */
.action-pill-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0.5rem 0.9rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    min-height: 40px;
}
.action-pill-btn:hover {
    transform: translateY(-2px);
}
.btn-alert-check {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.25);
    color: #dc2626;
}
.btn-alert-check:hover {
    background: rgba(239, 68, 68, 0.16);
    color: #b91c1c;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.2);
}
.btn-telegram-summary {
    background: linear-gradient(135deg, #0088cc 0%, #29b6f6 100%);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(0, 136, 204, 0.3);
}
.btn-telegram-summary:hover {
    background: linear-gradient(135deg, #0077b5 0%, #0099e6 100%);
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(0, 136, 204, 0.4);
}
.btn-settings-pill {
    background: var(--surface-2, #f8fafc);
    border-color: var(--border, #cbd5e1);
    color: var(--text-primary, #1e293b);
}
.btn-settings-pill:hover {
    background: var(--surface, #ffffff);
    border-color: var(--track-primary);
    color: var(--track-primary);
}

/* Mobile Hero Adjustments */
@media (max-width: 767.98px) {
    .tracking-hero-card {
        padding: 1rem;
        border-radius: 14px;
    }
    .hero-icon-badge {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
        border-radius: 11px;
    }
    .date-nav-capsule {
        width: 100%;
        justify-content: space-between;
    }
    .date-input-styled {
        flex: 1;
        min-width: 0;
        text-align: center;
        font-size: 0.82rem;
        padding: 0.3rem 0.25rem;
    }
    .hero-actions-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
        width: 100%;
    }
    .action-pill-btn {
        padding: 0.45rem 0.35rem;
        font-size: 0.78rem;
        width: 100%;
    }
    .action-pill-btn span.d-desktop-only {
        display: none !important;
    }
}

/* ═══════════════════════════════════════════════════════
   SaaS KPI STAT CARDS (RESPONSIVE GRID)
═══════════════════════════════════════════════════════ */
.kpi-cards-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.85rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 1199.98px) {
    .kpi-cards-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 767.98px) {
    .kpi-cards-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem;
    }
    .kpi-card.kpi-total-card {
        grid-column: span 2;
    }
}

.kpi-card {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: var(--track-radius);
    padding: 1rem 1.15rem;
    box-shadow: var(--track-shadow);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    cursor: pointer;
    user-select: none;
}
.kpi-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3.5px;
    background: var(--kpi-accent, #4f46e5);
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--track-shadow-lg);
    border-color: var(--kpi-accent, #4f46e5);
}
.kpi-card.is-active-filter {
    border-color: var(--kpi-accent, #4f46e5);
    box-shadow: 0 0 0 3px rgba(var(--kpi-accent-rgb, 79, 70, 229), 0.22), var(--track-shadow-lg);
}

.kpi-total   { --kpi-accent: #4f46e5; --kpi-accent-rgb: 79, 70, 229; }
.kpi-success { --kpi-accent: #10b981; --kpi-accent-rgb: 16, 185, 129; }
.kpi-warning { --kpi-accent: #f59e0b; --kpi-accent-rgb: 245, 158, 11; }
.kpi-danger  { --kpi-accent: #ef4444; --kpi-accent-rgb: 239, 68, 68; }
.kpi-pending { --kpi-accent: #64748b; --kpi-accent-rgb: 100, 116, 139; }

.kpi-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.kpi-total .kpi-icon-wrap   { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
.kpi-success .kpi-icon-wrap { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.kpi-warning .kpi-icon-wrap { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.kpi-danger .kpi-icon-wrap  { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.kpi-pending .kpi-icon-wrap { background: rgba(100, 116, 139, 0.1); color: #64748b; }

.kpi-value {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1.1;
    margin-top: 0.25rem;
    color: var(--text-primary, #0f172a);
}
@media (max-width: 767.98px) {
    .kpi-card {
        padding: 0.75rem 0.85rem;
        border-radius: 13px;
    }
    .kpi-value {
        font-size: 1.45rem;
    }
    .kpi-icon-wrap {
        width: 32px;
        height: 32px;
        font-size: 1rem;
        border-radius: 9px;
    }
}

.kpi-progress-bar {
    height: 4px;
    border-radius: 999px;
    background: var(--surface-2, #f1f5f9);
    margin-top: 0.65rem;
    overflow: hidden;
}
.kpi-progress-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s ease;
}

/* ═══════════════════════════════════════════════════════
   3-SLOT SHIFT OPERATIONS (DESKTOP & MOBILE CARD VIEWS)
═══════════════════════════════════════════════════════ */
.matrix-container-card {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: var(--track-radius);
    box-shadow: var(--track-shadow);
    margin-bottom: 1.5rem;
    overflow: hidden;
}
.matrix-header {
    background: var(--surface, #ffffff);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

/* Desktop Matrix Table */
.table-matrix {
    margin-bottom: 0;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.table-matrix thead th {
    background: var(--surface-2, #f8fafc);
    color: var(--text-secondary, #475569);
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--border, #e2e8f0);
    vertical-align: middle;
}
.table-matrix tbody td {
    padding: 0.95rem 1rem;
    border-bottom: 1px solid var(--border, #e2e8f0);
    vertical-align: middle;
}
.table-matrix tbody tr:hover {
    background-color: rgba(79, 70, 229, 0.025);
}

/* Shift Header Badges */
.shift-header-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 8px;
    margin-top: 3px;
}
.shift-morning   { background: rgba(14, 165, 233, 0.1); color: #0284c7; border: 1px solid rgba(14, 165, 233, 0.2); }
.shift-afternoon { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); }
.shift-evening   { background: rgba(99, 102, 241, 0.1); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.2); }

/* Staff Avatar & Profile */
.staff-avatar-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}
.staff-monogram {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}
.staff-meta-name {
    font-weight: 700;
    font-size: 0.92rem;
    color: var(--text-primary, #0f172a);
    line-height: 1.3;
}
.staff-meta-tg {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: var(--text-secondary, #64748b);
    margin-top: 2px;
}

/* Slot Pills Styling */
.slot-pill-wrapper {
    width: 100%;
}
.slot-pill {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.42rem 0.75rem;
    border-radius: 11px;
    border: 1px solid transparent;
    min-width: 120px;
    transition: all 0.2s ease;
    text-align: center;
    width: 100%;
}
.slot-pill.is-clickable {
    cursor: pointer;
}
.slot-pill.is-clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 14px rgba(0,0,0,0.08);
}

.slot-pill-top {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-weight: 700;
    font-size: 0.85rem;
    line-height: 1.2;
}
.slot-pill-badge {
    font-size: 0.72rem;
    font-weight: 600;
    margin-top: 2px;
    padding: 1px 6px;
    border-radius: 6px;
}

.slot-on-time { background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25); color: #047857; }
.badge-on-time { background: rgba(16, 185, 129, 0.15); color: #065f46; }

.slot-late { background: rgba(245, 158, 11, 0.09); border-color: rgba(245, 158, 11, 0.28); color: #b45309; }
.badge-late { background: rgba(245, 158, 11, 0.18); color: #92400e; font-weight: 700; }

.slot-missed { background: rgba(239, 68, 68, 0.07); border-color: rgba(239, 68, 68, 0.22); color: #b91c1c; }
.badge-missed { background: rgba(239, 68, 68, 0.12); color: #991b1b; }

.slot-pending { background: rgba(99, 102, 241, 0.06); border: 1px dashed rgba(99, 102, 241, 0.3); color: #4338ca; }
.badge-pending { background: rgba(99, 102, 241, 0.1); color: #3730a3; }

.slot-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #6366f1;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7);
    animation: pulse-ring 1.8s infinite;
}
@keyframes pulse-ring {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(99, 102, 241, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
}

.slot-empty-pill {
    color: var(--text-muted, #94a3b8);
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.4rem 0.65rem;
    border: 1px dashed var(--border, #cbd5e1);
    border-radius: 10px;
    width: 100%;
    background: var(--surface-2, #f8fafc);
}

.summary-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 0.38rem 0.75rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid transparent;
}
.summary-full { background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.25); color: #047857; }
.summary-partial { background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.25); color: #b45309; }
.summary-missed { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.25); color: #b91c1c; }

/* ── Mobile Staff Shift Cards (For Screens < 768px) ── */
.mobile-matrix-list {
    padding: 0.65rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.mobile-staff-card {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 0.9rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.mobile-staff-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    padding-bottom: 0.65rem;
    border-bottom: 1px solid var(--border, #f1f5f9);
}
.mobile-shifts-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}
.mobile-shift-col {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.mobile-shift-header-tag {
    font-size: 0.68rem;
    font-weight: 700;
    margin-bottom: 4px;
    text-align: center;
    color: var(--text-secondary, #64748b);
}

/* ═══════════════════════════════════════════════════════
   DETAILED SUBMISSIONS SECTION (DESKTOP & MOBILE)
═══════════════════════════════════════════════════════ */
.details-container-card {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: var(--track-radius);
    box-shadow: var(--track-shadow);
    overflow: hidden;
}

.details-toolbar {
    background: var(--surface, #ffffff);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border, #e2e8f0);
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}
@media (min-width: 992px) {
    .details-toolbar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.quick-search-box {
    position: relative;
    width: 100%;
    min-width: 240px;
}
.quick-search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted, #94a3b8);
    font-size: 0.95rem;
}
.quick-search-input {
    width: 100%;
    padding: 0.45rem 1rem 0.45rem 2.25rem;
    border-radius: 10px;
    border: 1px solid var(--border, #cbd5e1);
    background: var(--surface-2, #f8fafc);
    color: var(--text-primary, #0f172a);
    font-size: 0.88rem;
    transition: all 0.2s ease;
}
.quick-search-input:focus {
    outline: none;
    border-color: var(--track-primary);
    background: var(--surface, #ffffff);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

.filter-select-styled {
    border-radius: 10px;
    border: 1px solid var(--border, #cbd5e1);
    background-color: var(--surface-2, #f8fafc);
    color: var(--text-primary, #0f172a);
    font-size: 0.85rem;
    font-weight: 500;
    padding: 0.45rem 0.75rem;
}

/* Desktop Details Table */
.table-details {
    margin-bottom: 0;
    width: 100%;
}
.table-details thead th {
    background: var(--surface-2, #f8fafc);
    color: var(--text-secondary, #475569);
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--border, #e2e8f0);
}
.table-details tbody td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid var(--border, #e2e8f0);
    vertical-align: middle;
}
.table-details tbody tr:hover {
    background-color: rgba(79, 70, 229, 0.025);
}

/* Mobile Submissions Cards */
.mobile-submissions-list {
    padding: 0.65rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.mobile-sub-card {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 0.9rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    transition: all 0.2s ease;
}

.identifier-code-pill {
    background: rgba(79, 70, 229, 0.08);
    color: #4f46e5;
    border: 1px solid rgba(79, 70, 229, 0.2);
    font-family: var(--font-latin-modern), monospace !important;
    font-weight: 600;
    font-size: 0.78rem;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    display: inline-block;
}

.btn-view-report {
    background: rgba(79, 70, 229, 0.08);
    border: 1px solid rgba(79, 70, 229, 0.25);
    color: #4f46e5;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 0.38rem 0.85rem;
    border-radius: 10px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
.btn-view-report:hover {
    background: #4f46e5;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}

/* ═══════════════════════════════════════════════════════
   TELEGRAM MODAL PREVIEW (CLEAN KHMER FONT & MOBILE)
═══════════════════════════════════════════════════════ */
.tg-modal-content {
    border-radius: 20px;
    border: 1px solid var(--border, #e2e8f0);
    box-shadow: var(--track-shadow-lg);
    overflow: hidden;
}
.tg-modal-header {
    background: var(--surface, #ffffff);
    padding: 1.15rem 1.4rem;
    border-bottom: 1px solid var(--border, #e2e8f0);
}
.tg-user-card {
    background: var(--surface-2, #f8fafc);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 0.9rem 1.15rem;
}
.tg-message-bubble {
    background: var(--surface-2, #f8fafc);
    border: 1px solid var(--border, #cbd5e1);
    border-radius: 14px;
    padding: 1.15rem;
    font-family: var(--font-khmer-modern) !important;
    font-size: 0.95rem;
    line-height: 1.8;
    color: var(--text-primary, #0f172a);
    white-space: pre-wrap;
    word-break: break-word;
    max-height: 380px;
    overflow-y: auto;
}
.btn-copy-msg {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.35rem 0.8rem;
    border-radius: 8px;
    border: 1px solid var(--border, #cbd5e1);
    background: var(--surface, #ffffff);
    color: var(--text-secondary, #475569);
    transition: all 0.2s ease;
    cursor: pointer;
}
.btn-copy-msg:hover {
    background: var(--surface-2, #f1f5f9);
    color: var(--track-primary);
    border-color: var(--track-primary);
}
</style>

<div class="tracking-shell">

    <!-- ═══════════════════════════════════════════════════
         1. HERO HEADER & CONTROLS
    ════════════════════════════════════════════════════ -->
    <div class="tracking-hero-card">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
            <!-- Title & Info -->
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-badge">
                    <i class="bi bi-clipboard-data-fill"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h1 class="h4 fw-bold mb-0 text-primary-emphasis">
                            {{ __('reports.daily_tracking') }}
                        </h1>
                        @if($carbonDate->isToday())
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                <span class="slot-pulse-dot me-1" style="background:#10b981; box-shadow:0 0 0 0 rgba(16,185,129,0.7);"></span>
                                Live Tracking
                            </span>
                        @endif
                    </div>
                    <p class="text-muted mb-0 small mt-1">
                        {{ __('reports.daily_tracking_subtitle') }}
                    </p>
                </div>
            </div>

            <!-- Date Navigator & Actions -->
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                <!-- Date Capsule -->
                <form method="GET" action="{{ route('reports.tracking.index') }}" class="date-nav-capsule" id="dateNavForm">
                    @if(request('staff')) <input type="hidden" name="staff" value="{{ request('staff') }}"> @endif
                    @if(request('report_type')) <input type="hidden" name="report_type" value="{{ request('report_type') }}"> @endif
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

                    <a href="{{ route('reports.tracking.index', array_merge(request()->query(), ['date' => $carbonDate->copy()->subDay()->toDateString()])) }}"
                       class="date-nav-btn" title="{{ __('reports.prev_day') }}">
                        <i class="bi bi-chevron-left"></i>
                    </a>

                    <input type="date" name="date" class="date-input-styled font-number"
                           value="{{ $selectedDate }}" onchange="document.getElementById('dateNavForm').submit()">

                    <a href="{{ route('reports.tracking.index', array_merge(request()->query(), ['date' => $carbonDate->copy()->addDay()->toDateString()])) }}"
                       class="date-nav-btn" title="{{ __('reports.next_day') }}">
                        <i class="bi bi-chevron-right"></i>
                    </a>

                    <a href="{{ route('reports.tracking.index', array_merge(request()->query(), ['date' => now('Asia/Phnom_Penh')->toDateString()])) }}"
                       class="btn-today-pill {{ $carbonDate->isToday() ? 'is-active' : '' }}">
                        <i class="bi bi-calendar-event"></i>
                        <span>{{ __('reports.today') }}</span>
                    </a>
                </form>

                <!-- Actions Grid -->
                <div class="hero-actions-container d-flex align-items-center gap-2">
                    <form action="{{ route('reports.tracking.check-now') }}" method="POST" class="d-inline flex-fill">
                        @csrf
                        <button type="submit" class="action-pill-btn btn-alert-check w-100" title="{{ __('reports.check_alert_now') }}">
                            <i class="bi bi-bell-fill"></i>
                            <span class="d-desktop-only">{{ __('reports.check_alert_now') }}</span>
                            <span class="d-md-none">Alert</span>
                        </button>
                    </form>

                    <form action="{{ route('reports.tracking.send-summary') }}" method="POST" class="d-inline flex-fill">
                        @csrf
                        <input type="hidden" name="date" value="{{ $selectedDate }}">
                        <button type="submit" class="action-pill-btn btn-telegram-summary w-100" title="{{ __('reports.send_summary') }}">
                            <i class="bi bi-send-fill"></i>
                            <span class="d-desktop-only">{{ __('reports.send_summary') }}</span>
                            <span class="d-md-none">សង្ខេប</span>
                        </button>
                    </form>

                    <a href="{{ route('reports.requirements.index') }}" class="action-pill-btn btn-settings-pill flex-fill" title="{{ __('reports.settings') }}">
                        <i class="bi bi-sliders2"></i>
                        <span class="d-desktop-only">{{ __('reports.settings') }}</span>
                        <span class="d-md-none">កំណត់</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2 px-3 rounded-3 shadow-sm border-0 mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center py-2 px-3 rounded-3 shadow-sm border-0 mb-3" role="alert">
            <i class="bi bi-info-circle-fill me-2 fs-5 text-info"></i>
            <div class="fw-medium">{{ session('info') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center py-2 px-3 rounded-3 shadow-sm border-0 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-danger"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- ═══════════════════════════════════════════════════
         2. SaaS KPI SUMMARY CARDS (RESPONSIVE GRID)
    ════════════════════════════════════════════════════ -->
    @php
        $completionPct = $totalRequired > 0 ? round((($submittedCount + $lateCount) / $totalRequired) * 100) : 0;
        $onTimePct     = $totalRequired > 0 ? round(($submittedCount / $totalRequired) * 100) : 0;
        $latePct       = $totalRequired > 0 ? round(($lateCount / $totalRequired) * 100) : 0;
        $missedPct     = $totalRequired > 0 ? round(($missedCount / $totalRequired) * 100) : 0;
        $pendingPct    = $totalRequired > 0 ? round(($pendingCount / $totalRequired) * 100) : 0;
    @endphp

    <div class="kpi-cards-grid">
        <!-- Total Required -->
        <div class="kpi-card kpi-total kpi-total-card {{ !request('status') ? 'is-active-filter' : '' }}"
             onclick="applyStatusFilter('')" title="ចុចដើម្បីបង្ហាញទាំងអស់ (Show All)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">{{ __('reports.total_required') }}</div>
                    <div class="kpi-value text-primary font-number">{{ $totalRequired }}</div>
                </div>
                <div class="kpi-icon-wrap">
                    <i class="bi bi-card-checklist"></i>
                </div>
            </div>
            <div class="kpi-progress-bar">
                <div class="kpi-progress-fill" style="width: 100%; background: #4f46e5;"></div>
            </div>
            <div class="d-flex justify-content-between text-muted small mt-2" style="font-size: 0.76rem;">
                <span>បំពេញបាន</span>
                <span class="fw-bold text-primary font-number">{{ $completionPct }}%</span>
            </div>
        </div>

        <!-- On-Time -->
        <div class="kpi-card kpi-success {{ request('status') === 'submitted' ? 'is-active-filter' : '' }}"
             onclick="applyStatusFilter('submitted')" title="ចុចដើម្បីត្រងទាន់ពេល (Filter On-time)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">{{ __('reports.on_time') }}</div>
                    <div class="kpi-value text-success font-number">{{ $submittedCount }}</div>
                </div>
                <div class="kpi-icon-wrap">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
            <div class="kpi-progress-bar">
                <div class="kpi-progress-fill" style="width: {{ $onTimePct }}%; background: #10b981;"></div>
            </div>
            <div class="d-flex justify-content-between text-muted small mt-2" style="font-size: 0.76rem;">
                <span>សមាមាត្រ</span>
                <span class="fw-bold text-success font-number">{{ $onTimePct }}%</span>
            </div>
        </div>

        <!-- Late -->
        <div class="kpi-card kpi-warning {{ request('status') === 'late' ? 'is-active-filter' : '' }}"
             onclick="applyStatusFilter('late')" title="ចុចដើម្បីត្រងយឺត (Filter Late)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">{{ __('reports.late') }}</div>
                    <div class="kpi-value text-warning-emphasis font-number">{{ $lateCount }}</div>
                </div>
                <div class="kpi-icon-wrap">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="kpi-progress-bar">
                <div class="kpi-progress-fill" style="width: {{ $latePct }}%; background: #f59e0b;"></div>
            </div>
            <div class="d-flex justify-content-between text-muted small mt-2" style="font-size: 0.76rem;">
                <span>សមាមាត្រ</span>
                <span class="fw-bold text-warning-emphasis font-number">{{ $latePct }}%</span>
            </div>
        </div>

        <!-- Missed -->
        <div class="kpi-card kpi-danger {{ request('status') === 'missed' ? 'is-active-filter' : '' }}"
             onclick="applyStatusFilter('missed')" title="ចុចដើម្បីត្រងខកខាន (Filter Missed)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">{{ __('reports.missed') }}</div>
                    <div class="kpi-value text-danger font-number">{{ $missedCount }}</div>
                </div>
                <div class="kpi-icon-wrap">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
            <div class="kpi-progress-bar">
                <div class="kpi-progress-fill" style="width: {{ $missedPct }}%; background: #ef4444;"></div>
            </div>
            <div class="d-flex justify-content-between text-muted small mt-2" style="font-size: 0.76rem;">
                <span>សមាមាត្រ</span>
                <span class="fw-bold text-danger font-number">{{ $missedPct }}%</span>
            </div>
        </div>

        <!-- Pending -->
        <div class="kpi-card kpi-pending {{ request('status') === 'pending' ? 'is-active-filter' : '' }}"
             onclick="applyStatusFilter('pending')" title="ចុចដើម្បីត្រងកំពុងរង់ចាំ (Filter Pending)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small fw-semibold">{{ __('reports.pending') }}</div>
                    <div class="kpi-value text-secondary font-number">{{ $pendingCount }}</div>
                </div>
                <div class="kpi-icon-wrap">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="kpi-progress-bar">
                <div class="kpi-progress-fill" style="width: {{ $pendingPct }}%; background: #64748b;"></div>
            </div>
            <div class="d-flex justify-content-between text-muted small mt-2" style="font-size: 0.76rem;">
                <span>សមាមាត្រ</span>
                <span class="fw-bold text-secondary font-number">{{ $pendingPct }}%</span>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         3. 3-SLOT SHIFT OPERATIONS MATRIX (DESKTOP & MOBILE)
    ════════════════════════════════════════════════════ -->
    @php
        $avatarGradients = [
            'linear-gradient(135deg, #4f46e5, #7c3aed)',
            'linear-gradient(135deg, #0284c7, #06b6d4)',
            'linear-gradient(135deg, #059669, #10b981)',
            'linear-gradient(135deg, #d97706, #f59e0b)',
            'linear-gradient(135deg, #db2777, #f43f5e)',
            'linear-gradient(135deg, #7c2d12, #ea580c)',
            'linear-gradient(135deg, #4338ca, #3b82f6)',
        ];
    @endphp

    <div class="matrix-container-card">
        <div class="matrix-header">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                    <i class="bi bi-grid-3x3-gap-fill fs-6"></i>
                </span>
                <span class="fw-bold text-primary-emphasis fs-6">{{ __('reports.matrix_title') }}</span>
                <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill font-number">
                    <i class="bi bi-calendar3 me-1"></i>{{ $carbonDate->format('d/m/Y') }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('reports.requirements.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-plus-circle me-1"></i>{{ __('reports.create_new_requirement') }}
                </a>
            </div>
        </div>

        <!-- ── Desktop / Tablet Table View (Hidden on Mobile < 768px) ── -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-matrix align-middle">
                <thead>
                    <tr>
                        <th style="min-width: 240px;">{{ __('reports.staff_member') }}</th>
                        <th class="text-center" style="min-width: 170px;">
                            <div>{{ __('reports.morning_1') }}</div>
                            <div class="shift-header-pill shift-morning">
                                <i class="bi bi-sunrise-fill"></i>
                                <span class="font-number">07:00 AM</span>
                            </div>
                        </th>
                        <th class="text-center" style="min-width: 170px;">
                            <div>{{ __('reports.morning_2') }}</div>
                            <div class="shift-header-pill shift-afternoon">
                                <i class="bi bi-sun-fill"></i>
                                <span class="font-number">03:10 PM</span>
                            </div>
                        </th>
                        <th class="text-center" style="min-width: 170px;">
                            <div>{{ __('reports.evening_3') }}</div>
                            <div class="shift-header-pill shift-evening">
                                <i class="bi bi-moon-stars-fill"></i>
                                <span class="font-number">11:50 PM</span>
                            </div>
                        </th>
                        <th class="text-center" style="min-width: 140px;">{{ __('reports.summary') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffMatrix as $index => $staff)
                        @php
                            $slot1 = $staff['slots']['07:00'] ?? null;
                            $slot2 = $staff['slots']['15:10'] ?? null;
                            $slot3 = $staff['slots']['23:50'] ?? null;

                            if (!$slot1) {
                                foreach ($staff['slots'] as $time => $s) {
                                    if ($time < '12:00') { $slot1 = $s; break; }
                                }
                            }
                            if (!$slot2) {
                                foreach ($staff['slots'] as $time => $s) {
                                    if ($time >= '12:00' && $time < '18:00') { $slot2 = $s; break; }
                                }
                            }
                            if (!$slot3) {
                                foreach ($staff['slots'] as $time => $s) {
                                    if ($time >= '18:00') { $slot3 = $s; break; }
                                }
                            }

                            $totalSlots = count($staff['slots']);
                            $completed = 0;
                            foreach ($staff['slots'] as $s) {
                                if (in_array($s['status'], ['submitted', 'late'])) {
                                    $completed++;
                                }
                            }

                            $words = preg_split('/\s+/', trim($staff['staff_name']));
                            $initials = count($words) >= 2 ? mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1) : mb_substr($staff['staff_name'], 0, 2);
                            $initials = mb_strtoupper($initials);
                            $gradient = $avatarGradients[$index % count($avatarGradients)];
                        @endphp
                        <tr>
                            <td>
                                <div class="staff-avatar-wrap">
                                    <div class="staff-monogram" style="background: {{ $gradient }};">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="staff-meta-name">{{ $staff['staff_name'] }}</div>
                                        <div class="staff-meta-tg">
                                            <i class="bi bi-telegram text-primary"></i>
                                            <span>ID: {{ $staff['telegram_user_id'] }}</span>
                                            @if(!empty($staff['telegram_username']))
                                                <span class="text-secondary fw-semibold">({{ '@' . $staff['telegram_username'] }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Slot 1 -->
                            <td class="text-center">
                                @if($slot1)
                                    @include('reports.tracking._slot_cell', ['slot' => $slot1])
                                @else
                                    <span class="slot-empty-pill">—</span>
                                @endif
                            </td>

                            <!-- Slot 2 -->
                            <td class="text-center">
                                @if($slot2)
                                    @include('reports.tracking._slot_cell', ['slot' => $slot2])
                                @else
                                    <span class="slot-empty-pill">—</span>
                                @endif
                            </td>

                            <!-- Slot 3 -->
                            <td class="text-center">
                                @if($slot3)
                                    @include('reports.tracking._slot_cell', ['slot' => $slot3])
                                @else
                                    <span class="slot-empty-pill">—</span>
                                @endif
                            </td>

                            <!-- Summary -->
                            <td class="text-center">
                                @if($totalSlots > 0 && $completed === $totalSlots)
                                    <span class="summary-pill summary-full">
                                        <i class="bi bi-check-all fs-6"></i>
                                        <span class="font-number">{{ $completed }}/{{ $totalSlots }}</span>
                                        <span>{{ __('reports.full') }}</span>
                                    </span>
                                @elseif($completed > 0)
                                    <span class="summary-pill summary-partial">
                                        <i class="bi bi-exclamation-circle fs-6"></i>
                                        <span class="font-number">{{ $completed }}/{{ $totalSlots }}</span>
                                        <span>{{ __('reports.partial') }}</span>
                                    </span>
                                @else
                                    <span class="summary-pill summary-missed">
                                        <i class="bi bi-x-circle fs-6"></i>
                                        <span class="font-number">0/{{ $totalSlots }}</span>
                                        <span>{{ __('reports.missed') }}</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                                <h6 class="fw-bold mb-1">{{ __('reports.empty_schedule_for_date') }}</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ── Mobile Card View (Only visible on screens < 768px) ── -->
        <div class="mobile-matrix-list d-md-none">
            @forelse($staffMatrix as $index => $staff)
                @php
                    $slot1 = $staff['slots']['07:00'] ?? null;
                    $slot2 = $staff['slots']['15:10'] ?? null;
                    $slot3 = $staff['slots']['23:50'] ?? null;

                    if (!$slot1) {
                        foreach ($staff['slots'] as $time => $s) {
                            if ($time < '12:00') { $slot1 = $s; break; }
                        }
                    }
                    if (!$slot2) {
                        foreach ($staff['slots'] as $time => $s) {
                            if ($time >= '12:00' && $time < '18:00') { $slot2 = $s; break; }
                        }
                    }
                    if (!$slot3) {
                        foreach ($staff['slots'] as $time => $s) {
                            if ($time >= '18:00') { $slot3 = $s; break; }
                        }
                    }

                    $totalSlots = count($staff['slots']);
                    $completed = 0;
                    foreach ($staff['slots'] as $s) {
                        if (in_array($s['status'], ['submitted', 'late'])) {
                            $completed++;
                        }
                    }

                    $words = preg_split('/\s+/', trim($staff['staff_name']));
                    $initials = count($words) >= 2 ? mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1) : mb_substr($staff['staff_name'], 0, 2);
                    $initials = mb_strtoupper($initials);
                    $gradient = $avatarGradients[$index % count($avatarGradients)];
                @endphp
                <div class="mobile-staff-card">
                    <!-- Staff Header -->
                    <div class="mobile-staff-header">
                        <div class="staff-avatar-wrap">
                            <div class="staff-monogram" style="width: 36px; height: 36px; font-size: 0.82rem; background: {{ $gradient }};">
                                {{ $initials }}
                            </div>
                            <div>
                                <div class="staff-meta-name" style="font-size: 0.88rem;">{{ $staff['staff_name'] }}</div>
                                <div class="staff-meta-tg" style="font-size: 0.72rem;">
                                    <i class="bi bi-telegram text-primary"></i>
                                    <span>ID: {{ $staff['telegram_user_id'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($totalSlots > 0 && $completed === $totalSlots)
                                <span class="summary-pill summary-full py-1 px-2" style="font-size: 0.72rem;">
                                    <span class="font-number">{{ $completed }}/{{ $totalSlots }}</span> {{ __('reports.full') }}
                                </span>
                            @elseif($completed > 0)
                                <span class="summary-pill summary-partial py-1 px-2" style="font-size: 0.72rem;">
                                    <span class="font-number">{{ $completed }}/{{ $totalSlots }}</span> {{ __('reports.partial') }}
                                </span>
                            @else
                                <span class="summary-pill summary-missed py-1 px-2" style="font-size: 0.72rem;">
                                    <span class="font-number">0/{{ $totalSlots }}</span> {{ __('reports.missed') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- 3 Shift Columns Grid -->
                    <div class="mobile-shifts-grid">
                        <div class="mobile-shift-col">
                            <div class="mobile-shift-header-tag">🌅 07:00 AM</div>
                            @if($slot1)
                                @include('reports.tracking._slot_cell', ['slot' => $slot1])
                            @else
                                <span class="slot-empty-pill">—</span>
                            @endif
                        </div>
                        <div class="mobile-shift-col">
                            <div class="mobile-shift-header-tag">☀️ 03:10 PM</div>
                            @if($slot2)
                                @include('reports.tracking._slot_cell', ['slot' => $slot2])
                            @else
                                <span class="slot-empty-pill">—</span>
                            @endif
                        </div>
                        <div class="mobile-shift-col">
                            <div class="mobile-shift-header-tag">🌙 11:50 PM</div>
                            @if($slot3)
                                @include('reports.tracking._slot_cell', ['slot' => $slot3])
                            @else
                                <span class="slot-empty-pill">—</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary opacity-50"></i>
                    {{ __('reports.empty_schedule_for_date') }}
                </div>
            @endforelse
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         4. DETAILED SUBMISSIONS & INSTANT LIVE SEARCH
    ════════════════════════════════════════════════════ -->
    <div class="details-container-card">
        <div class="details-toolbar">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                    <i class="bi bi-list-check fs-6"></i>
                </span>
                <span class="fw-bold text-primary-emphasis fs-6">{{ __('reports.detailed_submissions') }}</span>
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill font-number" id="submissionsCounterBadge">
                    {{ __('reports.items_count', ['count' => $dueRequirements->count()]) }}
                </span>
            </div>

            <!-- Instant Search & Filters -->
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                <!-- Instant Real-Time Client Search -->
                <div class="quick-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="instantSearchInput" class="quick-search-input"
                           placeholder="ស្វែងរកតាមឈ្មោះ, ID, Tag..." oninput="handleInstantSearch(this.value)">
                </div>

                <!-- Server Dropdown Filters -->
                <form method="GET" action="{{ route('reports.tracking.index') }}" class="d-flex align-items-center gap-1 flex-wrap" id="serverFilterForm">
                    <input type="hidden" name="date" value="{{ $selectedDate }}">

                    <select name="staff" class="filter-select-styled flex-fill" onchange="this.form.submit()">
                        <option value="">-- {{ __('reports.all_staff') }} --</option>
                        @foreach($allStaff as $s)
                            <option value="{{ $s->telegram_user_id }}" {{ request('staff') == $s->telegram_user_id ? 'selected' : '' }}>
                                {{ $s->staff_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="report_type" class="filter-select-styled flex-fill" onchange="this.form.submit()">
                        <option value="">-- {{ __('reports.all_types') }} --</option>
                        @foreach($reportTypes as $rt)
                            <option value="{{ $rt->report_type }}" {{ request('report_type') == $rt->report_type ? 'selected' : '' }}>
                                {{ $rt->report_title }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" class="filter-select-styled flex-fill" id="selectStatusFilter" onchange="this.form.submit()">
                        <option value="">-- {{ __('reports.all_statuses') }} --</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>{{ __('reports.on_time') }}</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>{{ __('reports.late') }}</option>
                        <option value="missed" {{ request('status') == 'missed' ? 'selected' : '' }}>{{ __('reports.missed') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('reports.pending') }}</option>
                    </select>

                    @if(request()->hasAny(['staff', 'report_type', 'status']))
                        <a href="{{ route('reports.tracking.index', ['date' => $selectedDate]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Desktop Submissions Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-details align-middle" id="detailedSubmissionsTable">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">{{ __('reports.staff') }}</th>
                        <th style="min-width: 220px;">{{ __('reports.report') }}</th>
                        <th style="min-width: 140px;">{{ __('reports.identifier_tag') }}</th>
                        <th class="text-center" style="min-width: 120px;">{{ __('reports.deadline') }}</th>
                        <th class="text-center" style="min-width: 130px;">{{ __('reports.submitted') }}</th>
                        <th class="text-center" style="min-width: 120px;">{{ __('reports.status') }}</th>
                        <th class="text-end" style="min-width: 130px;">{{ __('reports.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="submissionsTableBody">
                    @forelse($dueRequirements as $req)
                        @php
                            $sub = $submissions->get($req->id);
                            $isFiltered = false;
                            $currentStatus = $sub ? $sub->status : ($carbonDate->isToday() && now('Asia/Phnom_Penh')->lt($req->calculateDeadlineForDate($carbonDate)) ? 'pending' : 'missed');
                            if (request('status') && $currentStatus !== request('status')) {
                                $isFiltered = true;
                            }

                            $words = preg_split('/\s+/', trim($req->staff_name));
                            $rInitials = count($words) >= 2 ? mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1) : mb_substr($req->staff_name, 0, 2);
                            $rInitials = mb_strtoupper($rInitials);
                        @endphp

                        @if(!$isFiltered)
                        <tr class="submission-row" data-search="{{ mb_strtolower($req->staff_name . ' ' . $req->telegram_user_id . ' ' . $req->report_title . ' ' . $req->identifier_tag . ' ' . $req->report_type) }}" data-status="{{ $currentStatus }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="staff-monogram" style="width: 32px; height: 32px; font-size: 0.8rem; background: linear-gradient(135deg, #4f46e5, #06b6d4);">
                                        {{ $rInitials }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $req->staff_name }}</div>
                                        <div class="small text-muted font-number" style="font-size: 0.75rem;">
                                            ID: {{ $req->telegram_user_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-primary-emphasis">{{ $req->report_title }}</div>
                                <span class="badge bg-light text-secondary border mt-1 font-mono-code" style="font-size: 0.72rem;">{{ $req->report_type }}</span>
                            </td>
                            <td>
                                <span class="identifier-code-pill font-mono-code">{{ $req->identifier_tag }}</span>
                            </td>
                            <td class="text-center font-number">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::createFromFormat('H:i', $req->deadline_time)->format('h:i A') }}
                                </span>
                            </td>
                            <td class="text-center font-number">
                                @if($sub && $sub->submitted_at)
                                    <div class="fw-semibold text-dark">{{ $sub->formatted_submitted_at }}</div>
                                    @if($sub->revision_count > 0)
                                        <span class="badge bg-info-subtle text-info small mt-1" style="font-size: 0.7rem;">
                                            {{ __('reports.revision', ['count' => $sub->revision_count]) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($sub)
                                    {!! $sub->status_badge !!}
                                @elseif($carbonDate->isToday() && now('Asia/Phnom_Penh')->lt($req->calculateDeadlineForDate($carbonDate)))
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i>{{ __('reports.pending') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle me-1"></i>{{ __('reports.missed') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sub && $sub->message_text)
                                    <button type="button" class="btn-view-report"
                                            onclick="showReportModal({{ $sub->id }})">
                                        <i class="bi bi-eye"></i>
                                        <span>{{ __('reports.view_report') }}</span>
                                    </button>
                                @else
                                    <span class="text-muted small" style="font-size: 0.78rem;">{{ __('reports.no_data_yet') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr id="emptyRowState">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                {{ __('reports.no_matching_reports') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Submissions Cards (Screens < 768px) -->
        <div class="mobile-submissions-list d-md-none" id="mobileSubmissionsList">
            @forelse($dueRequirements as $req)
                @php
                    $sub = $submissions->get($req->id);
                    $isFiltered = false;
                    $currentStatus = $sub ? $sub->status : ($carbonDate->isToday() && now('Asia/Phnom_Penh')->lt($req->calculateDeadlineForDate($carbonDate)) ? 'pending' : 'missed');
                    if (request('status') && $currentStatus !== request('status')) {
                        $isFiltered = true;
                    }

                    $words = preg_split('/\s+/', trim($req->staff_name));
                    $rInitials = count($words) >= 2 ? mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1) : mb_substr($req->staff_name, 0, 2);
                    $rInitials = mb_strtoupper($rInitials);
                @endphp

                @if(!$isFiltered)
                <div class="mobile-sub-card submission-row" data-search="{{ mb_strtolower($req->staff_name . ' ' . $req->telegram_user_id . ' ' . $req->report_title . ' ' . $req->identifier_tag . ' ' . $req->report_type) }}" data-status="{{ $currentStatus }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="staff-monogram" style="width: 30px; height: 30px; font-size: 0.75rem; background: linear-gradient(135deg, #4f46e5, #06b6d4);">
                                {{ $rInitials }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $req->staff_name }}</div>
                                <span class="identifier-code-pill" style="font-size: 0.7rem;">{{ $req->identifier_tag }}</span>
                            </div>
                        </div>
                        <div>
                            @if($sub)
                                {!! $sub->status_badge !!}
                            @elseif($carbonDate->isToday() && now('Asia/Phnom_Penh')->lt($req->calculateDeadlineForDate($carbonDate)))
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-hourglass-split me-1"></i>{{ __('reports.pending') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('reports.missed') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="fw-semibold text-primary-emphasis mb-2" style="font-size: 0.85rem;">
                        {{ $req->report_title }}
                    </div>

                    <div class="d-flex justify-content-between align-items-center text-muted small bg-light p-2 rounded-3 mb-2 font-number" style="font-size: 0.76rem;">
                        <div><i class="bi bi-clock me-1 text-secondary"></i>ម៉ោងកំណត់: {{ \Carbon\Carbon::createFromFormat('H:i', $req->deadline_time)->format('h:i A') }}</div>
                        <div>
                            <i class="bi bi-send me-1 text-primary"></i>
                            {{ $sub && $sub->submitted_at ? $sub->formatted_submitted_at : '—' }}
                        </div>
                    </div>

                    @if($sub && $sub->message_text)
                        <button type="button" class="btn-view-report w-100 py-2 rounded-3"
                                onclick="showReportModal({{ $sub->id }})">
                            <i class="bi bi-eye"></i>
                            <span>{{ __('reports.view_report') }}</span>
                        </button>
                    @endif
                </div>
                @endif
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                    {{ __('reports.no_matching_reports') }}
                </div>
            @endforelse
        </div>

        <div id="noSearchResultBox" class="p-4 text-center text-muted" style="display: none;">
            <i class="bi bi-search fs-3 d-block mb-1 text-secondary opacity-50"></i>
            មិនមានលទ្ធផលត្រូវគ្នានឹងការស្វែងរកឡើយ
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════
     5. TELEGRAM-STYLE REPORT SUBMISSION MODAL
════════════════════════════════════════════════════ -->
<div class="modal fade" id="reportViewModal" tabindex="-1" aria-labelledby="reportViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content tg-modal-content">
            <!-- Modal Header -->
            <div class="modal-header tg-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                        <i class="bi bi-telegram fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalReportTitle">
                            {{ __('reports.report_content') }}
                        </h5>
                        <div class="small text-muted">ការបញ្ជូនរបាយការណ៍ផលិតកម្មតាម Telegram</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-md-4">
                <!-- Sender Profile & Timing -->
                <div class="tg-user-card mb-3">
                    <div class="row g-2 g-md-3 align-items-center">
                        <div class="col-6 col-md-3">
                            <div class="small text-muted mb-1">{{ __('reports.sender') }}:</div>
                            <div class="fw-bold fs-6 text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-person-fill text-primary"></i>
                                <span id="modalStaffName">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted mb-1">{{ __('reports.deadline') }}:</div>
                            <div class="fw-semibold font-number text-secondary" id="modalDeadline">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted mb-1">{{ __('reports.actual_submitted') }}:</div>
                            <div class="fw-semibold font-number text-primary" id="modalSubmittedAt">—</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="small text-muted mb-1">{{ __('reports.status') }}:</div>
                            <div id="modalStatusBadge">—</div>
                        </div>
                    </div>
                </div>

                <!-- Message Content Header -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold small text-muted d-flex align-items-center gap-1">
                        <i class="bi bi-chat-text-fill text-primary"></i>
                        <span>{{ __('reports.report_message_content') }}</span>
                    </div>
                    <button type="button" class="btn-copy-msg" onclick="copyModalMessage()">
                        <i class="bi bi-clipboard" id="copyIcon"></i>
                        <span id="copyTextLabel">ចម្លងសារ (Copy)</span>
                    </button>
                </div>

                <!-- Clean Khmer Font Message Bubble -->
                <div class="tg-message-bubble" id="modalMessageText">
                    {{ __('reports.loading') }}
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body border-top py-2 px-3 px-md-4">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">
                    {{ __('reports.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const reportI18n = {
    loading: @json(__('reports.loading')),
    on_time: @json(__('reports.on_time')),
    late: @json(__('reports.late')),
    missed: @json(__('reports.missed')),
    pending: @json(__('reports.pending')),
    min_unit: @json(__('reports.minutes_unit')),
    empty_msg: @json(__('reports.empty_message')),
    fetch_error: @json(__('reports.fetch_error')),
};

/* ── KPI Quick Filter ── */
function applyStatusFilter(status) {
    const select = document.getElementById('selectStatusFilter');
    if (select) {
        select.value = status;
        document.getElementById('serverFilterForm').submit();
    }
}

/* ── Instant Real-Time Client Search ── */
function handleInstantSearch(term) {
    const query = term.toLowerCase().trim();
    const rows = document.querySelectorAll('.submission-row');
    const noResultBox = document.getElementById('noSearchResultBox');
    let visibleCount = 0;

    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        if (!query || searchData.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (noResultBox) {
        noResultBox.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }

    const badge = document.getElementById('submissionsCounterBadge');
    if (badge && query) {
        badge.innerText = visibleCount + ' រកឃើញ';
    }
}

/* ── Show Report Submission Modal ── */
function showReportModal(submissionId) {
    const modalEl = document.getElementById('reportViewModal');
    const modal = new bootstrap.Modal(modalEl);

    document.getElementById('modalMessageText').innerText = reportI18n.loading;
    document.getElementById('copyTextLabel').innerText = 'ចម្លងសារ (Copy)';
    document.getElementById('copyIcon').className = 'bi bi-clipboard';
    modal.show();

    fetch(`/reports/tracking/submission/${submissionId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalStaffName').innerText = data.staff_name;
            document.getElementById('modalReportTitle').innerText = data.report_title;
            document.getElementById('modalDeadline').innerText = data.deadline_at;
            document.getElementById('modalSubmittedAt').innerText = data.submitted_at;
            document.getElementById('modalMessageText').innerText = data.message_text || reportI18n.empty_msg;

            let statusHtml = '';
            if (data.status === 'submitted') {
                statusHtml = `<span class="badge bg-success">${reportI18n.on_time}</span>`;
            } else if (data.status === 'late') {
                statusHtml = `<span class="badge bg-warning text-dark font-number">${reportI18n.late} (+${data.late_minutes} ${reportI18n.min_unit})</span>`;
            } else if (data.status === 'missed') {
                statusHtml = `<span class="badge bg-danger">${reportI18n.missed}</span>`;
            } else {
                statusHtml = `<span class="badge bg-secondary">${reportI18n.pending}</span>`;
            }
            if (data.revision_count > 0) {
                statusHtml += ` <span class="badge bg-info font-number">Rev #${data.revision_count}</span>`;
            }
            document.getElementById('modalStatusBadge').innerHTML = statusHtml;
        })
        .catch(err => {
            document.getElementById('modalMessageText').innerText = reportI18n.fetch_error + ': ' + err;
        });
}

/* ── Copy Modal Message Text ── */
function copyModalMessage() {
    const text = document.getElementById('modalMessageText').innerText;
    if (!text || text === reportI18n.loading) return;

    navigator.clipboard.writeText(text).then(() => {
        const label = document.getElementById('copyTextLabel');
        const icon = document.getElementById('copyIcon');
        label.innerText = 'បានចម្លងរួចរាល់! (Copied)';
        icon.className = 'bi bi-check-lg text-success';
        setTimeout(() => {
            label.innerText = 'ចម្លងសារ (Copy)';
            icon.className = 'bi bi-clipboard';
        }, 2200);
    }).catch(err => {
        console.error('Failed to copy text', err);
    });
}
</script>
@endsection