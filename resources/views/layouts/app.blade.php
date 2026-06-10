<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'ប្រព័ន្ធគ្រប់គ្រងការបោះពុម្ព')</title>

<!-- Google Fonts: Poppins (Latin/Numbers) + Hanuman (Khmer — tight, clean for UI) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Hanuman:wght@400;700;900&display=swap" rel="stylesheet">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════
   DESIGN TOKENS
═══════════════════════════════════════════════════════ */
:root {
  /* Brand */
  --primary:        #4f46e5;
  --primary-light:  #6366f1;
  --primary-dark:   #3730a3;
  --primary-glow:   rgba(79,70,229,.18);
  --success:        #10b981;
  --success-light:  #d1fae5;
  --success-dark:   #065f46;
  --warning:        #f59e0b;
  --warning-light:  #fef3c7;
  --warning-dark:   #92400e;
  --danger:         #ef4444;
  --danger-light:   #fee2e2;
  --danger-dark:    #991b1b;
  --purple:         #8b5cf6;
  --purple-light:   #ede9fe;

  /* Sidebar */
  --sidebar-w:      260px;
  --sidebar-bg:     #0f172a;
  --sidebar-hover:  rgba(255,255,255,.06);
  --sidebar-active: rgba(99,102,241,.25);
  --sidebar-border: rgba(255,255,255,.07);

  /* Surface */
  --bg:             #f1f5f9;
  --surface:        #ffffff;
  --surface-2:      #f8fafc;
  --border:         #e2e8f0;
  --border-dark:    #cbd5e1;

  /* Text */
  --text-primary:   #0f172a;
  --text-secondary: #475569;
  --text-muted:     #94a3b8;

  /* Shape */
  --radius-sm:  8px;
  --radius:     12px;
  --radius-lg:  16px;
  --radius-xl:  20px;

  /* Typography */
  --font-latin:  'Poppins', sans-serif;
  --font-khmer:  'Hanuman', 'Poppins', sans-serif;

  /* Transitions */
  --ease: .2s ease;
}

/* ═══════════════════════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { scroll-behavior: smooth; }

body {
  font-family: var(--font-khmer);
  background: var(--bg);
  color: var(--text-primary);
  min-height: 100vh;
  display: flex;
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}

/* Numbers & Latin chars use Poppins automatically */
span, td, th, p, h1, h2, h3, h4, button, label, input, select {
  font-family: var(--font-khmer);
}
.latin, code, .badge-num, td.num, .stat-value {
  font-family: var(--font-latin) !important;
}

/* ═══════════════════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════════════════ */
.sidebar {
  width: var(--sidebar-w);
  min-height: 100vh;
  background: var(--sidebar-bg);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0; top: 0;
  z-index: 1000;
  border-right: 1px solid var(--sidebar-border);
  transition: transform var(--ease);
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: 1.5rem 1.25rem 1rem;
  border-bottom: 1px solid var(--sidebar-border);
  text-decoration: none;
}

.sidebar-logo .logo-icon {
  width: 40px; height: 40px;
  background: linear-gradient(135deg, var(--primary), var(--primary-light));
  border-radius: var(--radius);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(79,70,229,.4);
}

.sidebar-logo .logo-text {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}
.sidebar-logo .logo-text strong {
  font-family: var(--font-latin);
  font-size: .9rem;
  font-weight: 700;
  color: #f1f5f9;
  letter-spacing: -.01em;
}
.sidebar-logo .logo-text small {
  font-size: .7rem;
  color: var(--text-muted);
  font-family: var(--font-khmer);
}

.sidebar-section {
  padding: 1rem .75rem .25rem;
}
.sidebar-section-label {
  font-size: .65rem;
  font-weight: 600;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--text-muted);
  padding: 0 .5rem;
  margin-bottom: .5rem;
  font-family: var(--font-latin);
}

.sidebar-nav {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: .15rem;
}

.sidebar-nav a {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .62rem .85rem;
  border-radius: var(--radius);
  font-size: .88rem;
  font-weight: 500;
  color: #94a3b8;
  text-decoration: none;
  transition: background var(--ease), color var(--ease);
  position: relative;
}

.sidebar-nav a:hover {
  background: var(--sidebar-hover);
  color: #e2e8f0;
}

.sidebar-nav a.active {
  background: var(--sidebar-active);
  color: #fff;
}

.sidebar-nav a.active::before {
  content: '';
  position: absolute;
  left: 0; top: 20%; bottom: 20%;
  width: 3px;
  background: var(--primary-light);
  border-radius: 0 3px 3px 0;
}

.sidebar-nav a .nav-icon {
  font-size: 1rem;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
}

.sidebar-footer {
  margin-top: auto;
  padding: 1rem;
  border-top: 1px solid var(--sidebar-border);
}

.sidebar-date {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .75rem;
  color: var(--text-muted);
  font-family: var(--font-latin);
}

/* ═══════════════════════════════════════════════════════
   MAIN LAYOUT
═══════════════════════════════════════════════════════ */
.main-wrap {
  margin-left: var(--sidebar-w);
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

/* ── Topbar ── */
.topbar {
  height: 64px;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 2rem;
  gap: 1rem;
  position: sticky;
  top: 0;
  z-index: 900;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
}

.topbar-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  flex: 1;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: .75rem;
}

.topbar-badge {
  display: flex;
  align-items: center;
  gap: .4rem;
  padding: .3rem .75rem;
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: 999px;
  font-size: .78rem;
  color: var(--text-secondary);
  font-family: var(--font-latin);
}

/* ── Page ── */
.page-content {
  padding: 2rem;
  flex: 1;
  max-width: 1280px;
  width: 100%;
}

/* ═══════════════════════════════════════════════════════
   STAT CARDS
═══════════════════════════════════════════════════════ */
.kpi-card {
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  color: #fff;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: .75rem;
}

.kpi-card::after {
  content: '';
  position: absolute;
  right: -20px; top: -20px;
  width: 110px; height: 110px;
  border-radius: 50%;
  background: rgba(255,255,255,.08);
}

.kpi-card .kpi-icon {
  width: 44px; height: 44px;
  background: rgba(255,255,255,.18);
  border-radius: var(--radius);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem;
  backdrop-filter: blur(4px);
}

.kpi-card .kpi-value {
  font-family: var(--font-latin) !important;
  font-size: 2.1rem;
  font-weight: 800;
  line-height: 1;
  letter-spacing: -.03em;
}

.kpi-card .kpi-label {
  font-size: .82rem;
  opacity: .9;
  margin-top: -.3rem;
}

.kpi-card .kpi-sub {
  font-size: .72rem;
  opacity: .7;
  font-family: var(--font-latin);
}

.kpi-blue   { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); box-shadow: 0 8px 24px rgba(79,70,229,.3); }
.kpi-green  { background: linear-gradient(135deg, #059669 0%, #10b981 100%); box-shadow: 0 8px 24px rgba(16,185,129,.3); }
.kpi-amber  { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); box-shadow: 0 8px 24px rgba(245,158,11,.3); }
.kpi-purple { background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%); box-shadow: 0 8px 24px rgba(139,92,246,.3); }
.kpi-rose   { background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%); box-shadow: 0 8px 24px rgba(244,63,94,.3); }

/* ═══════════════════════════════════════════════════════
   PANELS / CARDS
═══════════════════════════════════════════════════════ */
.panel {
  background: var(--surface);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
  overflow: hidden;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .75rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
}

.panel-header .ph-title {
  display: flex;
  align-items: center;
  gap: .6rem;
  font-weight: 600;
  font-size: .95rem;
  color: var(--text-primary);
}

.panel-header .ph-title .ph-icon {
  width: 32px; height: 32px;
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  font-size: .9rem;
}

.panel-body {
  padding: 1.5rem;
}

/* ═══════════════════════════════════════════════════════
   PROGRESS BARS
═══════════════════════════════════════════════════════ */
.prog-track {
  height: 8px;
  border-radius: 999px;
  background: #e2e8f0;
  overflow: hidden;
  position: relative;
}
.prog-fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, var(--primary), var(--primary-light));
  transition: width .6s cubic-bezier(.4,0,.2,1);
  position: relative;
}
.prog-fill::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent 50%, rgba(255,255,255,.2) 100%);
}
.prog-fill.green  { background: linear-gradient(90deg, #059669, #10b981); }
.prog-fill.amber  { background: linear-gradient(90deg, #d97706, #f59e0b); }
.prog-fill.danger { background: linear-gradient(90deg, #dc2626, #ef4444); }

/* ═══════════════════════════════════════════════════════
   TABLE
═══════════════════════════════════════════════════════ */
.tbl-wrap { overflow-x: auto; max-height: 520px; overflow-y: auto; }

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

/* Bilingual column header */
.data-table thead th {
  position: sticky;
  top: 0;
  background: #f8fafc;
  border-bottom: 2px solid var(--border);
  padding: .65rem 1rem;
  white-space: nowrap;
  z-index: 2;
  vertical-align: bottom;
}

.data-table thead th .th-km {
  display: block;
  font-family: var(--font-khmer);
  font-size: .82rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.3;
  letter-spacing: 0;
  text-transform: none;
}

.data-table thead th .th-en {
  display: block;
  font-family: var(--font-latin);
  font-size: .65rem;
  font-weight: 500;
  color: var(--text-muted);
  letter-spacing: .06em;
  text-transform: uppercase;
  margin-top: .1rem;
}

/* Column alignment helpers */
.data-table thead th.col-right { text-align: right; }
.data-table thead th.col-center { text-align: center; }

.data-table tbody td {
  padding: .75rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: .88rem;
  vertical-align: middle;
}

.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr { transition: background var(--ease); }
.data-table tbody tr:hover { background: #f5f7ff; }
.data-table tbody tr.row-select { cursor: pointer; }
.data-table tbody tr.row-selected td { background: #eef2ff; }
.data-table tbody tr.row-selected td:first-child {
  border-left: 3px solid var(--primary);
}

/* Zebra stripe */
.data-table tbody tr:nth-child(even) td { background: #fafbff; }
.data-table tbody tr:nth-child(even):hover td { background: #f0f3ff; }
.data-table tbody tr.row-selected td,
.data-table tbody tr.row-selected:nth-child(even) td { background: #eef2ff !important; }

/* Level / urgency column — REMOVED (now grade-badge below) */

/* Grade badge — book education grade */
.grade-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: .25em .7em;
  border-radius: 6px;
  font-family: var(--font-khmer);
  font-size: .8rem;
  font-weight: 700;
  white-space: nowrap;
  background: #f1f5f9;
  color: var(--text-secondary);
  border: 1px solid var(--border-dark);
  min-width: 36px;
}
.grade-badge.grade-primary   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.grade-badge.grade-secondary { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
.grade-badge.grade-num       { font-family: var(--font-latin) !important; background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }

/* Inline progress cell */
.prog-cell {
  display: flex;
  align-items: center;
  gap: .6rem;
  min-width: 140px;
}
.prog-cell .prog-track { flex: 1; height: 8px; }
.prog-cell .prog-num {
  font-family: var(--font-latin);
  font-size: .75rem;
  font-weight: 700;
  color: var(--text-secondary);
  min-width: 34px;
  text-align: right;
}

/* ═══════════════════════════════════════════════════════
   BADGES
═══════════════════════════════════════════════════════ */
.badge {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .28em .7em;
  border-radius: 999px;
  font-size: .75rem;
  font-weight: 600;
  white-space: nowrap;
  font-family: var(--font-khmer);
}

.badge-done    { background: var(--success-light); color: var(--success-dark); }
.badge-progress{ background: var(--warning-light); color: var(--warning-dark); }
.badge-pending { background: var(--danger-light);  color: var(--danger-dark);  }
.badge-binding { background: #dbeafe; color: #1e40af; }
.badge-staple  { background: var(--purple-light);  color: #5b21b6; }

/* ═══════════════════════════════════════════════════════
   FORMS
═══════════════════════════════════════════════════════ */
.form-label {
  font-size: .8rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: .4rem;
  display: block;
  font-family: var(--font-khmer);
}

.form-control, .form-select {
  font-family: var(--font-khmer);
  font-size: .88rem;
  border-radius: var(--radius);
  border: 1.5px solid var(--border-dark);
  padding: .55rem .85rem;
  background: var(--surface);
  color: var(--text-primary);
  transition: border-color var(--ease), box-shadow var(--ease);
  width: 100%;
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-glow);
  outline: none;
}

.form-control::placeholder { color: var(--text-muted); }

.input-group-text {
  background: var(--surface-2);
  border: 1.5px solid var(--border-dark);
  border-right: none;
  color: var(--text-muted);
  border-radius: var(--radius) 0 0 var(--radius);
  font-size: .9rem;
  padding: .55rem .85rem;
}

/* ═══════════════════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════════════════ */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .4rem;
  font-family: var(--font-khmer);
  font-size: .85rem;
  font-weight: 600;
  border-radius: var(--radius);
  padding: .55rem 1.1rem;
  cursor: pointer;
  border: none;
  transition: all var(--ease);
  white-space: nowrap;
  text-decoration: none;
  line-height: 1.4;
}

.btn-primary {
  background: var(--primary);
  color: #fff;
  box-shadow: 0 2px 8px rgba(79,70,229,.3);
}
.btn-primary:hover  { background: var(--primary-dark); box-shadow: 0 4px 14px rgba(79,70,229,.4); color: #fff; }

.btn-success {
  background: var(--success);
  color: #fff;
  box-shadow: 0 2px 8px rgba(16,185,129,.3);
}
.btn-success:hover { background: #059669; color: #fff; }

.btn-warning {
  background: var(--warning);
  color: #fff;
  box-shadow: 0 2px 8px rgba(245,158,11,.3);
}
.btn-warning:hover { background: #d97706; color: #fff; }

.btn-outline-primary {
  background: transparent;
  color: var(--primary);
  border: 1.5px solid var(--primary);
}
.btn-outline-primary:hover { background: var(--primary); color: #fff; }

.btn-outline-secondary {
  background: transparent;
  color: var(--text-secondary);
  border: 1.5px solid var(--border-dark);
}
.btn-outline-secondary:hover { background: var(--surface-2); color: var(--text-primary); }

.btn-ghost {
  background: transparent;
  color: var(--text-secondary);
  border: none;
}
.btn-ghost:hover { background: var(--surface-2); color: var(--text-primary); }

.btn:active { transform: scale(.97); }
.btn:disabled { opacity: .5; cursor: not-allowed; pointer-events: none; }

.btn-sm { font-size: .78rem; padding: .35rem .8rem; border-radius: var(--radius-sm); }
.btn-lg { font-size: .95rem; padding: .75rem 1.5rem; }
.btn-icon {
  width: 36px; height: 36px;
  padding: 0;
  border-radius: var(--radius-sm);
  font-size: .95rem;
}

/* ═══════════════════════════════════════════════════════
   MISC
═══════════════════════════════════════════════════════ */
.divider {
  height: 1px;
  background: var(--border);
  margin: 1.25rem 0;
}

.text-muted  { color: var(--text-muted) !important; }
.text-sm     { font-size: .8rem; }
.text-xs     { font-size: .72rem; }
.fw-600      { font-weight: 600; }
.fw-700      { font-weight: 700; }

/* ── Empty state ── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3.5rem 1rem;
  color: var(--text-muted);
  gap: .75rem;
}

.empty-state .empty-icon {
  width: 64px; height: 64px;
  background: var(--surface-2);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem;
}

/* ── Section heading ── */
.section-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.3;
}
.section-sub {
  font-size: .85rem;
  color: var(--text-muted);
  margin-top: .2rem;
}

/* ── Alert ── */
.alert-info-soft {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: var(--radius);
  padding: .85rem 1rem;
  font-size: .85rem;
  color: #1d4ed8;
  display: flex;
  align-items: flex-start;
  gap: .6rem;
}

/* ═══════════════════════════════════════════════════════
   LOADING OVERLAY
═══════════════════════════════════════════════════════ */
#loading-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,.55);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 1.25rem;
}
#loading-overlay.show { display: flex; }
#loading-overlay .spinner {
  width: 48px; height: 48px;
  border: 3px solid rgba(255,255,255,.2);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin .7s linear infinite;
}
#loading-overlay p {
  color: #fff;
  font-size: .9rem;
  font-weight: 500;
  margin: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ═══════════════════════════════════════════════════════
   MOBILE
═══════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  .sidebar { transform: translateX(-100%); }
  .sidebar.open { transform: translateX(0); }
  .main-wrap { margin-left: 0; }
  .topbar { padding: 0 1rem; }
  .page-content { padding: 1.25rem 1rem; }
  #sidebar-overlay { display: block; }
}

#sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.4);
  z-index: 999;
}

.hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 1.3rem;
  color: var(--text-secondary);
  cursor: pointer;
  padding: .3rem;
}

@media (max-width: 1024px) {
  .hamburger { display: flex; align-items: center; }
}

@media (max-width: 640px) {
  .kpi-card .kpi-value { font-size: 1.7rem; }
  .data-table thead th, .data-table tbody td { font-size: .78rem; padding: .55rem .7rem; }
}
</style>

@stack('head')
</head>
<body>

<!-- ═══════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">
  <a href="{{ route('printing.index') }}" class="sidebar-logo">
    <div class="logo-icon">🖨️</div>
    <div class="logo-text">
      <strong>PrintTracker</strong>
      <small>ប្រព័ន្ធគ្រប់គ្រង</small>
    </div>
  </a>

  <div class="sidebar-section">
    <p class="sidebar-section-label">MAIN MENU</p>
    <ul class="sidebar-nav">
      <li>
        <a href="{{ route('printing.index') }}"
           class="{{ request()->routeIs('printing.index') ? 'active' : '' }}">
          <i class="bi bi-speedometer2 nav-icon"></i>
          <span>ទំព័រគ្រប់គ្រង</span>
        </a>
      </li>
      <li>
        <a href="{{ route('printing.report') }}"
           class="{{ request()->routeIs('printing.report') ? 'active' : '' }}">
          <i class="bi bi-bar-chart-line nav-icon"></i>
          <span>របាយការណ៍</span>
        </a>
      </li>
    </ul>
  </div>

  <div class="sidebar-section">
    <p class="sidebar-section-label">INTEGRATIONS</p>
    <ul class="sidebar-nav">
      <li>
        <a href="{{ route('telegram.setup') }}"
           class="{{ request()->routeIs('telegram.*') ? 'active' : '' }}">
          <i class="bi bi-telegram nav-icon"></i>
          <span>Telegram Bot</span>
          @php $groupCount = \App\Models\TelegramGroup::count(); @endphp
          @if($groupCount > 0)
            <span style="margin-left:auto;background:rgba(16,185,129,.25);color:#6ee7b7;font-size:.65rem;font-family:var(--font-latin);font-weight:700;padding:.1em .55em;border-radius:999px">{{ $groupCount }}</span>
          @else
            <span style="margin-left:auto;background:rgba(239,68,68,.2);color:#fca5a5;font-size:.65rem;font-family:var(--font-latin);font-weight:700;padding:.1em .55em;border-radius:999px">!</span>
          @endif
        </a>
      </li>
    </ul>
  </div>

  <div class="sidebar-footer">
    <div class="sidebar-date">
      <i class="bi bi-calendar3"></i>
      <span>{{ now()->format('d M Y') }}</span>
    </div>
  </div>
</aside>

<!-- Sidebar overlay for mobile -->
<div id="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- ═══════════════════════════════════════════════
     MAIN
════════════════════════════════════════════════ -->
<div class="main-wrap">

  <!-- Topbar -->
  <header class="topbar">
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle sidebar">
      <i class="bi bi-list"></i>
    </button>
    <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
    <div class="topbar-actions">
      <div class="topbar-badge">
        <i class="bi bi-clock" style="color:var(--primary)"></i>
        <span id="live-time">--:--</span>
      </div>
      <div class="topbar-badge">
        <i class="bi bi-circle-fill" style="color:var(--success);font-size:.45rem"></i>
        <span>Online</span>
      </div>
    </div>
  </header>

  <!-- Loading overlay -->
  <div id="loading-overlay">
    <div class="spinner"></div>
    <p>@yield('loading-text', 'Please wait...')</p>
  </div>

  <!-- Page content -->
  <main class="page-content">
    @yield('content')
  </main>

</div><!-- /main-wrap -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 — used only for destructive confirmations -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<style>
/* ── Inline Toast notification ──────────────────── */
#toast-container {
  position: fixed;
  top: 76px;
  right: 1.25rem;
  z-index: 9000;
  display: flex;
  flex-direction: column;
  gap: .5rem;
  pointer-events: none;
}
.app-toast {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  background: #fff;
  border-radius: var(--radius);
  box-shadow: 0 4px 24px rgba(0,0,0,.13), 0 1px 4px rgba(0,0,0,.06);
  padding: .85rem 1rem;
  min-width: 280px;
  max-width: 360px;
  pointer-events: all;
  border-left: 4px solid var(--primary);
  animation: toastIn .25s cubic-bezier(.4,0,.2,1);
  font-family: var(--font-khmer);
}
.app-toast.toast-success { border-color: var(--success); }
.app-toast.toast-error   { border-color: var(--danger);  }
.app-toast.toast-warning { border-color: var(--warning); }
.app-toast.toast-info    { border-color: var(--primary); }
.app-toast.toast-out     { animation: toastOut .2s ease forwards; }

.app-toast .toast-icon {
  font-size: 1.05rem;
  flex-shrink: 0;
  margin-top: .1rem;
}
.app-toast.toast-success .toast-icon { color: var(--success); }
.app-toast.toast-error   .toast-icon { color: var(--danger);  }
.app-toast.toast-warning .toast-icon { color: var(--warning); }
.app-toast.toast-info    .toast-icon { color: var(--primary); }

.app-toast .toast-body { flex: 1; min-width: 0; }
.app-toast .toast-title {
  font-size: .82rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: .15rem;
  line-height: 1.3;
}
.app-toast .toast-msg {
  font-size: .8rem;
  color: var(--text-secondary);
  line-height: 1.5;
  word-break: break-word;
}
.app-toast .toast-close {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
  font-size: .9rem;
  padding: 0;
  flex-shrink: 0;
  line-height: 1;
}
.app-toast .toast-close:hover { color: var(--text-primary); }

@keyframes toastIn {
  from { opacity: 0; transform: translateX(20px); }
  to   { opacity: 1; transform: translateX(0); }
}
@keyframes toastOut {
  to   { opacity: 0; transform: translateX(20px); }
}

/* SweetAlert2 — minimal, only for confirm dialogs */
.swal2-popup {
  font-family: var(--font-khmer) !important;
  border-radius: var(--radius-lg) !important;
  padding: 2rem !important;
}
.swal2-title   { font-size: 1rem !important; font-weight: 700 !important; }
.swal2-html-container { font-size: .88rem !important; color: var(--text-secondary) !important; }
.swal2-confirm { font-family: var(--font-khmer) !important; border-radius: var(--radius-sm) !important; font-size: .85rem !important; }
.swal2-cancel  { font-family: var(--font-khmer) !important; border-radius: var(--radius-sm) !important; font-size: .85rem !important; }
</style>

<script>
/* ── Live clock ──────────────────────────── */
(function ticker() {
  const el = document.getElementById('live-time');
  function tick() {
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-GB', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
  }
  tick();
  setInterval(tick, 1000);
})();

/* ── Sidebar toggle ──────────────────────── */
function toggleSidebar() {
  const sb   = document.getElementById('sidebar');
  const ov   = document.getElementById('sidebar-overlay');
  const open = sb.classList.toggle('open');
  ov.style.display = open ? 'block' : 'none';
}

/* ══════════════════════════════════════════
   TOAST SYSTEM  — replaces SweetAlert2
   for all non-destructive notifications
══════════════════════════════════════════ */
const _toastIcons = {
  success: 'bi-check-circle-fill',
  error:   'bi-x-circle-fill',
  warning: 'bi-exclamation-triangle-fill',
  info:    'bi-info-circle-fill',
};
const _toastTitles = {
  success: 'ជោគជ័យ',
  error:   'មានបញ្ហា',
  warning: 'ចំណាំ',
  info:    'ព័ត៌មាន',
};

function showToast(type, message, duration) {
  duration = duration ?? (type === 'error' ? 6000 : 3500);

  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `app-toast toast-${type}`;
  toast.innerHTML = `
    <i class="bi ${_toastIcons[type] || _toastIcons.info} toast-icon"></i>
    <div class="toast-body">
      <div class="toast-title">${_toastTitles[type] || 'ព័ត៌មាន'}</div>
      <div class="toast-msg">${message}</div>
    </div>
    <button class="toast-close" aria-label="close"><i class="bi bi-x-lg"></i></button>
  `;

  const close = () => {
    toast.classList.add('toast-out');
    setTimeout(() => toast.remove(), 200);
  };

  toast.querySelector('.toast-close').addEventListener('click', close);
  container.appendChild(toast);
  setTimeout(close, duration);
}

/* showAlert — keeps backward compat; routes to toast for info/warning/success,
   uses Swal only for explicit confirm() flows */
function showAlert(type, message) {
  showToast(type, message);
  // return a resolved promise so existing await showAlert(...) chains don't break
  return Promise.resolve();
}

function showLoading(show = true) {
  document.getElementById('loading-overlay').classList.toggle('show', show);
}

/* ── Flash messages (server-side) ────────── */
@if(session('success'))
  document.addEventListener('DOMContentLoaded', () => showToast('success', @json(session('success'))));
@endif
@if(session('error'))
  document.addEventListener('DOMContentLoaded', () => showToast('error', @json(session('error'))));
@endif
@if(session('warning'))
  document.addEventListener('DOMContentLoaded', () => showToast('warning', @json(session('warning'))));
@endif
</script>

@stack('scripts')
</body>
</html>
