<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<script>
  (function() {
    const theme = localStorage.getItem('pt_theme') || 'light';
    if (theme === 'system') {
        const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', sysDark ? 'dark' : 'light');
    } else {
        document.documentElement.setAttribute('data-theme', theme);
    }
  })();
</script>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0f172a">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="icon" href="{{ asset('images/logo.jpg') }}" type="image/jpeg">
<link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'ប្រព័ន្ធគ្រប់គ្រងការបោះពុម្ព')</title>

<!-- Google Fonts: Outfit (Latin) + Kantumruy Pro (Khmer) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<!-- Font Awesome 6 (for material icons) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<!-- NProgress (Top Loading Bar) -->
<link href="https://unpkg.com/nprogress@0.2.0/nprogress.css" rel="stylesheet">
<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

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
  --sidebar-bg:     #0b1121;
  --sidebar-hover:  rgba(255,255,255,.05);
  --sidebar-active: rgba(99,102,241,.15);
  --sidebar-border: rgba(255,255,255,.05);

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
  --font-latin:  'Outfit', sans-serif;
  --font-khmer:  'Outfit', 'Kantumruy Pro', sans-serif;

  /* Transitions */
  --ease: .2s ease;
}

/* ═══════════════════════════════════════════════════════
   GAMIFICATION ANIMATIONS
═══════════════════════════════════════════════════════ */
@keyframes bell-bounce {
  0% { transform: rotate(0); }
  15% { transform: rotate(15deg) scale(1.1); }
  30% { transform: rotate(-15deg) scale(1.1); }
  45% { transform: rotate(10deg); }
  60% { transform: rotate(-10deg); }
  75% { transform: rotate(5deg); }
  100% { transform: rotate(0); }
}

.bell-bouncing {
  animation: bell-bounce 1s ease-in-out infinite;
  transform-origin: top center;
  color: var(--warning) !important;
}

/* Ripple Effect */
.btn-ripple {
  position: relative;
  overflow: hidden;
  transform: translate3d(0, 0, 0);
}
.btn-ripple::after {
  content: "";
  display: block;
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  pointer-events: none;
  background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
  background-repeat: no-repeat;
  background-position: 50%;
  transform: scale(10, 10);
  opacity: 0;
  transition: transform .5s, opacity 1s;
}
.btn-ripple:active::after {
  transform: scale(0, 0);
  opacity: .3;
  transition: 0s;
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
  height: 100vh;
  background: var(--sidebar-bg);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0; top: 0;
  z-index: 1000;
  border-right: 1px solid var(--sidebar-border);
  transition: transform var(--ease);
  /* ── SCROLLABLE ── */
  overflow-y: auto;
  overflow-x: hidden;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}

/* Custom scrollbars - Global */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.6); }

.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 2px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.3); }

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
  background: #fff;
  border-radius: var(--radius);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0,0,0,.25);
  overflow: hidden;
  padding: 2px;
}
.sidebar-logo .logo-icon img {
  width: 100%; height: 100%;
  object-fit: contain;
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
  align-items: flex-start;
  gap: .75rem;
  padding: .62rem .85rem;
  border-radius: var(--radius);
  font-size: .88rem;
  font-weight: 500;
  line-height: 1.35;
  color: #94a3b8;
  text-decoration: none;
  transition: all .25s cubic-bezier(.4, 0, .2, 1);
  position: relative;
}
/* Label takes remaining width; badges stay aligned to the first line */
.sidebar-nav a > span:not(.sb-badge) {
  flex: 1;
  min-width: 0;
}

.sidebar-nav a:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(6px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.sidebar-nav a.active {
  background: rgba(79, 70, 229, 0.15);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(79, 70, 229, 0.3);
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
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
  line-height: 1.35;   /* align icon with the first line of the label */
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid var(--sidebar-border);
  margin-top: 1rem;  /* flows naturally inside scroll — no auto */
  flex-shrink: 0;
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
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  display: flex;
  align-items: center;
  padding: 0 2rem;
  gap: 1rem;
  position: sticky;
  top: 0;
  z-index: 900;
  box-shadow: 0 1px 3px rgba(0,0,0,.03);
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
  background: #ffffff;
  border-radius: 20px;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: .5rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(226, 232, 240, 0.8);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
  z-index: 1;
}
.kpi-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
  border-color: #cbd5e1;
}
.kpi-card::before {
  content: '';
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  opacity: 0.04;
  background-image: radial-gradient(circle at top right, currentColor 0%, transparent 60%);
  z-index: -1;
}
.kpi-card .kpi-icon {
  width: 48px; height: 48px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem;
  margin-bottom: 0.5rem;
  transition: transform .3s ease;
}
.kpi-card:hover .kpi-icon {
  transform: scale(1.1) rotate(5deg);
}
.kpi-card .kpi-value {
  font-family: var(--font-latin) !important;
  font-size: 2.2rem;
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.04em;
  color: #0f172a;
}
.kpi-card .kpi-label {
  font-size: .85rem;
  font-weight: 700;
  color: #475569;
}
.kpi-card .kpi-sub {
  font-size: .75rem;
  font-weight: 500;
  color: #94a3b8;
  font-family: var(--font-latin);
}

.kpi-blue { color: #3b82f6; }
.kpi-blue .kpi-icon { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.kpi-green { color: #10b981; }
.kpi-green .kpi-icon { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.kpi-amber { color: #f59e0b; }
.kpi-amber .kpi-icon { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.kpi-purple { color: #8b5cf6; }
.kpi-purple .kpi-icon { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
.kpi-rose { color: #f43f5e; }
.kpi-rose .kpi-icon { background: rgba(244, 63, 94, 0.12); color: #f43f5e; }

/* ═══════════════════════════════════════════════════════
   PANELS / CARDS
═══════════════════════════════════════════════════════ */
.panel {
  background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.8);
  box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05), 0 4px 6px -1px rgba(0, 0, 0, 0.03);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.panel:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.08), 0 8px 12px -1px rgba(0, 0, 0, 0.04);
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
@keyframes progLoad {
  from { width: 0 !important; }
}
@keyframes progShimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
@keyframes progStripes {
  from { background-position: 1.5rem 0; }
  to { background-position: 0 0; }
}
.prog-fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, #4f46e5, #818cf8);
  transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  box-shadow: 0 0 10px rgba(79, 70, 229, 0.3);
  animation: progLoad 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  overflow: hidden;
}
.prog-fill::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.2) 25%,
    transparent 25%,
    transparent 50%,
    rgba(255, 255, 255, 0.2) 50%,
    rgba(255, 255, 255, 0.2) 75%,
    transparent 75%,
    transparent
  );
  background-size: 1.5rem 1.5rem;
  animation: progStripes 1s linear infinite;
  z-index: 1;
}
.prog-fill::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  animation: progShimmer 2.5s infinite linear;
  z-index: 2;
}
.prog-fill.green  { background: linear-gradient(90deg, #059669, #34d399); box-shadow: 0 0 10px rgba(5, 150, 105, 0.3); }
.prog-fill.amber  { background: linear-gradient(90deg, #d97706, #fbbf24); box-shadow: 0 0 10px rgba(217, 119, 6, 0.3); }
.prog-fill.danger { background: linear-gradient(90deg, #dc2626, #f87171); box-shadow: 0 0 10px rgba(220, 38, 38, 0.3); }

/* ═══════════════════════════════════════════════════════
   TABLE
═══════════════════════════════════════════════════════ */
.tbl-wrap { 
  overflow-x: auto; 
  max-height: 550px; 
  overflow-y: auto;
  border-radius: 16px;
  border: 1px solid rgba(226, 232, 240, 0.8);
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
  background: var(--surface);
}

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

/* Bilingual column header */
.data-table thead th {
  position: sticky;
  top: 0;
  background: rgba(248, 250, 252, 0.95);
  backdrop-filter: blur(8px);
  border-bottom: 2px solid var(--border);
  padding: 1rem 1rem;
  white-space: nowrap;
  z-index: 2;
  vertical-align: bottom;
}

[data-theme="dark"] .data-table thead th {
  background: rgba(30, 41, 59, 0.95);
}

.data-table thead th .th-km {
  display: block;
  font-family: var(--font-khmer);
  font-size: .85rem;
  font-weight: 800;
  color: var(--text-primary);
  line-height: 1.3;
  letter-spacing: 0;
  text-transform: none;
}

.data-table thead th .th-en {
  display: block;
  font-family: var(--font-latin);
  font-size: .68rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: .05em;
  margin-top: .15rem;
}

html[lang="en"] .th-km { display: none !important; }
html[lang="km"] .th-en { display: none !important; }

html[lang="en"] .data-table thead th .th-en {
  font-size: .8rem;
  color: var(--text-primary);
  margin-top: 0;
}

/* Column alignment helpers */
.data-table thead th.col-right { text-align: right; }
.data-table thead th.col-center { text-align: center; }

.data-table tbody td {
  padding: 1rem 1rem;
  border-bottom: 1px solid var(--border);
  font-size: .9rem;
  vertical-align: middle;
}

.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr { transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1); }
.data-table tbody tr:hover { 
  background: var(--surface-2) !important;
  transform: scale(1.006) translateY(-2px);
  box-shadow: 0 12px 24px -10px rgba(0,0,0,0.08), inset 4px 0 0 var(--primary);
  position: relative;
  z-index: 10;
}
.data-table tbody tr.row-select { cursor: pointer; }
.data-table tbody tr.row-selected td { background: #eef2ff !important; }
.data-table tbody tr.row-selected td:first-child {
  border-left: 3px solid var(--primary);
}

/* Zebra stripe */
.data-table tbody tr:nth-child(even) td { background: rgba(250, 251, 253, 0.5); }
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
   EMPTY STATES
═══════════════════════════════════════════════════════ */
.empty-box {
  padding: 3rem 1rem;
  text-align: center;
  background: rgba(255, 255, 255, 0.4);
  border-radius: 12px;
  border: 1px dashed var(--border);
  margin: 1.5rem;
}
[data-theme="dark"] .empty-box {
  background: rgba(30, 41, 59, 0.5);
  border-color: rgba(255, 255, 255, 0.1);
}
.empty-box i {
  font-size: 2.5rem;
  color: var(--text-muted);
  opacity: 0.3;
  display: block;
  margin-bottom: 0.8rem;
}
.empty-box .empty-text {
  color: var(--text-muted);
  font-size: .9rem;
  font-weight: 600;
}

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
  font-size: .92rem;
  border-radius: 12px;
  border: 2px solid var(--border);
  padding: .65rem 1rem;
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  color: var(--text-primary);
  transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
  width: 100%;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary);
  background: var(--surface);
  box-shadow: 0 0 0 4px var(--primary-glow), 0 8px 16px -8px rgba(99, 102, 241, 0.25);
  outline: none;
  transform: translateY(-1px);
}

.form-control::placeholder { color: var(--text-muted); }

.form-control.is-invalid, .form-select.is-invalid {
  border-color: var(--danger);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc2626'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc2626' stroke='none'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(.375em + .1875rem) center;
  background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}
.form-control.is-invalid:focus, .form-select.is-invalid:focus {
  border-color: var(--danger);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
}
.invalid-feedback {
  color: var(--danger);
  font-size: .75rem;
  margin-top: .35rem;
  font-weight: 600;
  display: block;
  animation: lo-pop 0.2s ease-out;
}
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
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  color: #fff;
  box-shadow: 0 4px 10px rgba(79,70,229,.25);
  border: none;
}
.btn-primary:hover  { 
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
  box-shadow: 0 6px 15px rgba(79,70,229,.4); 
  transform: translateY(-2px);
  color: #fff; 
}

.btn-success {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  color: #fff;
  box-shadow: 0 4px 10px rgba(16,185,129,.25);
  border: none;
}
.btn-success:hover { 
  background: linear-gradient(135deg, #047857 0%, #059669 100%);
  box-shadow: 0 6px 15px rgba(16,185,129,.4);
  transform: translateY(-2px);
  color: #fff; 
}

.btn-warning {
  background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
  color: #fff;
  box-shadow: 0 4px 10px rgba(245,158,11,.25);
  border: none;
}
.btn-warning:hover { 
  background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
  box-shadow: 0 6px 15px rgba(245,158,11,.4);
  transform: translateY(-2px);
  color: #fff; 
}

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

/* ── Skeleton Loaders ── */
.skeleton {
  background: linear-gradient(90deg, var(--surface-2) 25%, var(--border-dark) 50%, var(--surface-2) 75%);
  background-size: 400% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
  border-radius: var(--radius-sm);
  color: transparent !important;
  pointer-events: none;
  user-select: none;
}
.skeleton * { visibility: hidden; }
@keyframes skeleton-loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── NProgress (Top Loading Bar) ── */
#nprogress .bar {
  background: var(--primary) !important;
  height: 3px !important;
}
#nprogress .peg {
  box-shadow: 0 0 10px var(--primary), 0 0 5px var(--primary) !important;
}
#nprogress .spinner-icon {
  border-top-color: var(--primary) !important;
  border-left-color: var(--primary) !important;
}

/* ── Breadcrumbs ── */
.breadcrumbs {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .85rem;
  color: var(--text-muted);
  margin-bottom: 1rem;
}
.breadcrumbs a {
  color: var(--text-muted);
  text-decoration: none;
  font-weight: 600;
  transition: color .2s;
}
.breadcrumbs a:hover {
  color: var(--primary);
}
.breadcrumbs .bc-sep {
  color: var(--border-dark);
  font-size: .7rem;
}
.breadcrumbs .bc-active {
  color: var(--text-primary);
  font-weight: 700;
}

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
  background: rgba(15,23,42,.45);
  backdrop-filter: blur(7px);
  -webkit-backdrop-filter: blur(7px);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  flex-direction: column;
}
#loading-overlay.show {
  display: flex;
  animation: lo-fade .25s ease;
}

/* White card holding the spinner + text */
#loading-overlay .lo-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.1rem;
  padding: 2rem 2.5rem;
  background: rgba(255,255,255,.98);
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(15,23,42,.35);
  animation: lo-pop .3s cubic-bezier(.34,1.56,.64,1);
}

/* Dual-ring spinner */
#loading-overlay .spinner {
  position: relative;
  width: 56px;
  height: 56px;
}
#loading-overlay .spinner::before,
#loading-overlay .spinner::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 4px solid transparent;
}
#loading-overlay .spinner::before {
  border-top-color: var(--primary, #4f46e5);
  border-right-color: var(--primary, #4f46e5);
  animation: spin .8s linear infinite;
}
#loading-overlay .spinner::after {
  border-bottom-color: #c7d2fe;
  border-left-color: #c7d2fe;
  animation: spin 1.2s linear infinite reverse;
}

#loading-overlay p {
  color: #1e293b;
  font-size: .92rem;
  font-weight: 600;
  margin: 0;
  text-align: center;
}
/* Animated trailing dots */
#loading-overlay p::after {
  content: "";
  animation: lo-dots 1.4s steps(4,end) infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes lo-fade { from { opacity: 0; } to { opacity: 1; } }
@keyframes lo-pop {
  from { opacity: 0; transform: scale(.85) translateY(10px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes lo-dots {
  0%   { content: ""; }
  25%  { content: "."; }
  50%  { content: ".."; }
  75%  { content: "..."; }
  100% { content: ""; }
}

/* ═══════════════════════════════════════════════════════
   MOBILE
═══════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
    /* On mobile: limit height to viewport so it scrolls properly */
    height: 100dvh;           /* dvh = dynamic viewport height (accounts for browser chrome) */
    height: 100vh;            /* fallback */
    max-height: -webkit-fill-available;
    border-radius: 0 24px 24px 0;
    box-shadow: 10px 0 45px rgba(0, 0, 0, 0.18);
    transition: transform .35s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .sidebar.open { transform: translateX(0); }
  .main-wrap { margin-left: 0; }
  
  .topbar {
    padding: 0 1rem;
    background: rgba(255, 255, 255, 0.85) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(0,0,0,.04) !important;
    box-shadow: 0 4px 20px rgba(0,0,0,.02);
  }
  [data-theme="dark"] .topbar {
    background: rgba(15, 23, 42, 0.85) !important;
    border-bottom: 1px solid rgba(255,255,255,.05) !important;
    box-shadow: 0 4px 20px rgba(0,0,0,.2);
  }

  .page-content {
    padding: 1.25rem 1rem;
    /* Leave room for floating mobile bottom nav */
    padding-bottom: calc(1.25rem + 80px);
  }
  #sidebar-overlay { display: block; }
  
  /* Hide search on mobile to save space */
  .topbar-search { display: none; }
  
  /* Make topbar title smaller on mobile */
  .topbar-title { font-size: .9rem; font-weight: 700; color: var(--text-primary); }
}

@media (max-width: 640px) {
  .kpi-card .kpi-value { font-size: 1.7rem; }
  .data-table thead th, .data-table tbody td { font-size: .78rem; padding: .55rem .7rem; }
  
  /* Hide time badge on very small screens */
  .topbar-badge { display: none !important; }
  
  /* Make topbar more compact */
  .topbar { padding: 0 .75rem; gap: .5rem; }
  .topbar-title { font-size: .85rem; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
}

#sidebar-overlay {
  display: block;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.4);
  z-index: 999;
  backdrop-filter: blur(3px);
  -webkit-backdrop-filter: blur(3px);
  opacity: 0;
  pointer-events: none;
  transition: opacity .3s ease;
}
#sidebar-overlay.show-overlay {
  opacity: 1;
  pointer-events: auto;
}

.hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 1.4rem;
  color: var(--text-secondary);
  cursor: pointer;
  padding: .3rem;
  display: none;
  align-items: center;
  justify-content: center;
}

@media (max-width: 1024px) {
  .hamburger { display: flex; }
}

/* ═══════════════════════════════════════════════════════
   MOBILE BOTTOM NAV — Floating Glassmorphic Pill
═══════════════════════════════════════════════════════ */
.mobile-bottom-nav {
  display: none;
  position: fixed;
  bottom: 12px; 
  left: 12px; 
  right: 12px;
  height: 60px;
  background: rgba(255, 255, 255, 0.88);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(15, 23, 42, 0.08);
  z-index: 998;
  justify-content: space-around;
  align-items: center;
  padding: 0 .4rem;
}

@media (max-width: 1024px) {
  .mobile-bottom-nav { display: flex; }
}

.mob-nav-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: .1rem;
  text-decoration: none;
  color: #64748b;
  font-size: .6rem;
  font-weight: 600;
  padding: .35rem .1rem;
  border-radius: 12px;
  transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 0;
  position: relative;
}

.mob-nav-item i {
  font-size: 1.25rem;
  line-height: 1;
  transition: transform .2s ease;
}

.mob-nav-item:active i {
  transform: scale(0.85);
}

.mob-nav-item span {
  font-size: .58rem;
  text-align: center;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 58px;
}

.mob-nav-item:hover,
.mob-nav-item.active {
  color: var(--primary);
}
.mob-nav-item.active {
  background: rgba(79, 70, 229, 0.06);
}

[data-theme="dark"] .mobile-bottom-nav {
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
}
[data-theme="dark"] .mob-nav-item:hover,
[data-theme="dark"] .mob-nav-item.active {
  color: #818cf8;
}
[data-theme="dark"] .mob-nav-item.active {
  background: rgba(129, 140, 248, 0.12);
}

.mob-nav-badge {
  position: absolute;
  top: 2px; right: calc(50% - 22px);
  min-width: 15px; height: 15px;
  background: var(--danger);
  color: #fff;
  border-radius: 999px;
  font-size: .55rem;
  font-family: var(--font-latin);
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  padding: 0 3px;
  border: 2px solid rgba(255, 255, 255, 0.9);
}
[data-theme="dark"] .mob-nav-badge {
  border: 2px solid #1e293b;
}

/* ── Dark Mode Overrides ── */
[data-theme="dark"] {
  --bg:             #0f172a;
  --surface:        #1e293b;
  --surface-2:      #0f172a;
  --border:         #334155;
  --border-dark:    #475569;

  --text-primary:   #f1f5f9;
  --text-secondary: #cbd5e1;
  --text-muted:     #64748b;

  --sidebar-bg:     #020617;
  --sidebar-hover:  rgba(255,255,255,.04);
  --sidebar-active: rgba(99,102,241,.2);
  --sidebar-border: rgba(255,255,255,.04);
}

[data-theme="dark"] body {
  background: var(--bg);
  color: var(--text-primary);
}

[data-theme="dark"] .topbar {
  background: rgba(30, 41, 59, 0.85);
  border-bottom: 1px solid var(--border);
}

[data-theme="dark"] .kpi-card {
  background: var(--surface);
  border-color: var(--border);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}
[data-theme="dark"] .kpi-card:hover {
  border-color: var(--border-dark);
}
[data-theme="dark"] .kpi-card .kpi-value {
  color: var(--text-primary);
}
[data-theme="dark"] .kpi-card .kpi-label {
  color: var(--text-secondary);
}

[data-theme="dark"] .panel {
  background: var(--surface);
  border-color: var(--border);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

[data-theme="dark"] .data-table thead th {
  background: #1e293b;
  border-bottom: 2px solid var(--border);
}

[data-theme="dark"] .data-table tbody td {
  border-bottom: 1px solid #334155;
}

[data-theme="dark"] .data-table tbody tr:nth-child(even) td {
  background: #1b2436;
}

[data-theme="dark"] .data-table tbody tr:nth-child(even):hover td,
[data-theme="dark"] .data-table tbody tr:hover td {
  background: #252f44 !important;
}

[data-theme="dark"] .data-table tbody tr.row-selected td {
  background: #262c53 !important;
}

[data-theme="dark"] .input-group-text,
[data-theme="dark"] .form-control,
[data-theme="dark"] .form-select {
  background: #0f172a;
  color: #fff;
  border-color: #334155;
}

[data-theme="dark"] .app-toast {
  background: #1e293b;
  color: #fff;
}
[data-theme="dark"] .app-toast .toast-title {
  color: #fff;
}
[data-theme="dark"] .app-toast .toast-msg {
  color: #cbd5e1;
}
[data-theme="dark"] .swal2-popup {
  background: #1e293b !important;
  color: #f1f5f9 !important;
}
[data-theme="dark"] .swal2-title,
[data-theme="dark"] .swal2-html-container {
  color: #f1f5f9 !important;
}
[data-theme="dark"] .swal2-cancel {
  background-color: #475569 !important;
}
[data-theme="dark"] .divider {
  background: var(--border);
}
[data-theme="dark"] .panel-header {
  border-bottom: 1px solid var(--border);
}
[data-theme="dark"] .panel-header .ph-title {
  color: var(--text-primary);
}
[data-theme="dark"] .prog-track {
  background: #334155;
}
[data-theme="dark"] .data-table thead th:first-child {
  background: var(--bg) !important;
}
[data-theme="dark"] .data-table tbody td:first-child {
  background: var(--surface) !important;
}
[data-theme="dark"] .data-table tbody tr:nth-child(even) td:first-child {
  background: #1b2436 !important;
}
[data-theme="dark"] #loading-overlay .lo-card {
  background: rgba(30, 41, 59, 0.98) !important;
}
[data-theme="dark"] #loading-overlay p {
  color: #f1f5f9 !important;
}
/* ── Global Animations & Micro-Interactions ── */
.btn {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.btn:active:not(:disabled) {
  transform: translateY(0);
}
.glass-card, .panel, .summary-card, .kpi-card {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.glass-card:hover, .panel:hover, .summary-card:hover, .kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1), 0 4px 8px -4px rgba(0,0,0,0.06);
}
tr[class*="row-"]:hover, tbody tr:hover {
  background-color: rgba(0,0,0,0.015) !important;
}
</style>

@stack('head')
</head>
<body>

<!-- ═══════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════ -->
@php
  $_unread    = \App\Models\SystemNotification::where('is_read', false)->count();
  $_tgGroups  = \App\Models\TelegramGroup::count();
  $_lowStock  = \App\Models\InventoryItem::where('status','active')->whereColumn('quantity_in_stock','<=','minimum_stock')->count();
  $_breakdown = \App\Models\Machine::where('status','breakdown')->count();
@endphp
<aside class="sidebar" id="sidebar">
  <a href="{{ route('dashboard') }}" class="sidebar-logo">
    <div class="logo-icon"><img src="{{ asset('images/logo.jpg') }}" alt="BELTEI"></div>
    <div class="logo-text">
      <strong>BELTEI University Press</strong>
      <small>ប្រព័ន្ធគ្រប់គ្រង</small>
    </div>
  </a>

  {{-- OVERVIEW --}}
  @if(\App\Services\RoleService::can('view_dashboard'))
  <div class="sidebar-section">
    <p class="sidebar-section-label">{{ strtoupper(trans_only('overview')) }}</p>
    <ul class="sidebar-nav">
      <li>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="bi bi-grid-1x2-fill nav-icon"></i>
          <span>{{ t('dashboard') }}</span>
          @if($_unread > 0)
            <span class="sb-badge sb-badge-danger">{{ $_unread }}</span>
          @endif
        </a>
      </li>
      @if(\App\Services\RoleService::can('view_analytics'))
      <li>
        <a href="{{ route('analytics.index') }}" class="{{ request()->routeIs('analytics.*') ? 'active' : '' }}">
          <i class="bi bi-pie-chart-fill nav-icon"></i>
          <span>{{ t('analytics') }}</span>
        </a>
      </li>
      @endif
    </ul>
  </div>
  @endif

  {{-- PRODUCTION (Admin/Manager only) --}}
  @if(\App\Services\RoleService::can('view_dashboard'))
  <div class="sidebar-section">
    <p class="sidebar-section-label">{{ strtoupper(trans_only('production')) }}</p>
    <ul class="sidebar-nav">
      <li>
        <a href="{{ route('printing.index') }}" class="{{ request()->routeIs('printing.index') ? 'active' : '' }}">
          <i class="bi bi-printer-fill nav-icon"></i>
          <span>{{ t('printing') }}</span>
        </a>
      </li>
      <li>
        <a href="{{ route('printing.report') }}" class="{{ request()->routeIs('printing.report') ? 'active' : '' }}">
          <i class="bi bi-bar-chart-line-fill nav-icon"></i>
          <span>{{ t('printing_report') }}</span>
        </a>
      </li>
      <li>
        <a href="{{ route('requests.index') }}" class="{{ request()->routeIs('requests.*') ? 'active' : '' }}">
          <i class="bi bi-file-earmark-plus-fill nav-icon"></i>
          <span>{{ t('print_requests') }}</span>
          @php $pReqs = \App\Models\PrintRequest::where('status','pending')->count(); @endphp
          @if($pReqs > 0)
            <span class="sb-badge sb-badge-warning">{{ $pReqs }}</span>
          @endif
        </a>
      </li>
      <li>
        <a href="{{ route('schedule.index') }}" class="{{ request()->routeIs('schedule.*') ? 'active' : '' }}">
          <i class="bi bi-calendar-range-fill nav-icon"></i>
          <span>{{ t('production_schedule') }}</span>
        </a>
      </li>
    </ul>
  </div>
  @endif

  {{-- PROCUREMENT --}}
  @if(\App\Services\RoleService::can('manage_procurement'))
  <div class="sidebar-section">
    <p class="sidebar-section-label">{{ strtoupper(trans_only('procurement')) }}</p>
    <ul class="sidebar-nav">
      <li>
        <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
          <i class="bi bi-receipt nav-icon"></i>
          <span>{{ t('purchase_orders') }}</span>
          @php $poPending = \App\Models\PurchaseOrder::where('status','pending_approval')->count(); @endphp
          @if($poPending > 0)
            <span class="sb-badge sb-badge-warning">{{ $poPending }}</span>
          @endif
        </a>
      </li>
      @if(\App\Services\RoleService::can('manage_suppliers'))
      <li>
        <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
          <i class="bi bi-buildings-fill nav-icon"></i>
          <span>{{ t('suppliers') }}</span>
        </a>
      </li>
      @endif
    </ul>
  </div>
  @endif

  {{-- STOCK / WAREHOUSE --}}
  @if(\App\Services\RoleService::can('daily_reports') || \App\Services\RoleService::can('manage_stock'))
  <div class="sidebar-section">
    <p class="sidebar-section-label">{{ strtoupper(trans_only('stock')) }}</p>
    <ul class="sidebar-nav">

      {{-- Daily Reports - Always show for reporters --}}
      @if(\App\Services\RoleService::can('daily_reports'))
      <li>
        <a href="{{ route('stock.movements.daily') }}?category=paper"
           class="{{ request()->routeIs('stock.movements.daily') && request('category')==='paper' ? 'active' : '' }}">
          <i class="bi bi-pencil-square nav-icon"></i>
          <span>{{ t('paper_report') }}</span>
        </a>
      </li>
      <li>
        <a href="{{ route('stock.movements.daily') }}?category=film"
           class="{{ request()->routeIs('stock.movements.daily') && request('category')==='film' ? 'active' : '' }}">
          <i class="bi bi-pencil-square nav-icon"></i>
          <span>{{ t('film_report') }}</span>
        </a>
      </li>
      <li>
        <a href="{{ route('stock.movements.daily') }}?category=consumable"
           class="{{ request()->routeIs('stock.movements.daily') && request('category')==='consumable' ? 'active' : '' }}">
          <i class="bi bi-pencil-square nav-icon"></i>
          <span>{{ t('consumable_report') }}</span>
        </a>
      </li>
      @endif

      {{-- Stock Management - Only for managers/admin --}}
      @if(\App\Services\RoleService::can('manage_materials'))
      <li style="border-top:1px solid var(--surface-2);margin-top:.3rem;padding-top:.3rem">
        <a href="{{ route('stock.materials.index') }}" class="{{ request()->routeIs('stock.materials.*') ? 'active' : '' }}">
          <i class="bi bi-box-seam-fill nav-icon"></i>
          <span>{{ t('materials_list') }}</span>
          @php $lowMat = app(\App\Services\StockService::class)->getLowStockMaterials()->count(); @endphp
          @if($lowMat > 0)
            <span class="sb-badge sb-badge-warning">{{ $lowMat }}</span>
          @endif
        </a>
      </li>
      @endif

      @if(\App\Services\RoleService::can('view_all_reports'))
      <li>
        <a href="{{ route('stock.movements.index') }}" class="{{ request()->routeIs('stock.movements.index') ? 'active' : '' }}">
          <i class="bi bi-arrow-left-right nav-icon"></i>
          <span>{{ t('stock_movements') }}</span>
        </a>
      </li>
      <li>
        <a href="{{ route('stock.reports.index') }}" class="{{ request()->routeIs('stock.reports.*') ? 'active' : '' }}">
          <i class="bi bi-graph-up nav-icon"></i>
          <span>{{ t('stock_reports') }}</span>
        </a>
      </li>
      @endif
    </ul>
  </div>
  @endif

  {{-- EQUIPMENT (Admin/Manager only) --}}
  @if(\App\Services\RoleService::can('manage_machines'))
  <div class="sidebar-section">
    <p class="sidebar-section-label">{{ strtoupper(trans_only('equipment')) }}</p>
    <ul class="sidebar-nav">
      <li>
        <a href="{{ route('machines.index') }}" class="{{ request()->routeIs('machines.*') ? 'active' : '' }}">
          <i class="bi bi-tools nav-icon"></i>
          <span>{{ t('machines_maintenance') }}</span>
          @if($_breakdown > 0)
            <span class="sb-badge sb-badge-danger">{{ $_breakdown }}</span>
          @endif
        </a>
      </li>
    </ul>
  </div>
  @endif

  {{-- SYSTEM --}}
  <div class="sidebar-section">
    <p class="sidebar-section-label">{{ strtoupper(trans_only('system')) }}</p>
    <ul class="sidebar-nav">
      <li>
        <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
          <i class="bi bi-bell-fill nav-icon"></i>
          <span>{{ t('notifications') }}</span>
          @if($_unread > 0)
            <span class="sb-badge sb-badge-danger">{{ $_unread }}</span>
          @endif
        </a>
      </li>
      <li>
        <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
          <i class="bi bi-gear-fill nav-icon"></i>
          <span>{{ t('settings') }}</span>
        </a>
      </li>
      @php
        $role = session('user_role');
        $isAdmin = \App\Services\RoleService::isAdmin();
      @endphp
      @if($isAdmin)
      <li>
        <a href="{{ route('telegram.setup') }}" class="{{ request()->routeIs('telegram.*') ? 'active' : '' }}">
          <i class="bi bi-telegram nav-icon"></i>
          <span>{{ t('telegram_bot') }}</span>
          @if($_tgGroups > 0)
            <span class="sb-badge" style="background:rgba(16,185,129,.3);color:#6ee7b7">{{ $_tgGroups }}</span>
          @else
            <span class="sb-badge sb-badge-danger">!</span>
          @endif
        </a>
      </li>
      @endif
    </ul>
  </div>

  <div class="sidebar-footer">
    @if(session('user_name'))
      <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.6rem;padding-bottom:.6rem;border-bottom:1px solid var(--sidebar-border)">
        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#6366f1);display:flex;align-items:center;justify-content:center;font-size:.75rem;color:#fff;font-weight:700;flex-shrink:0">
          {{ strtoupper(substr(session('user_name'), 0, 1)) }}
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:.78rem;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ session('user_name') }}</div>
          <div style="font-size:.65rem;color:#64748b">
            @php
              $role = session('user_role');
              $roleLabel = $role ? \App\Services\RoleService::getRoleLabel($role) : \App\Http\Controllers\EntryController::positionLabel(session('user_position'));
            @endphp
            {{ $roleLabel }}
          </div>
        </div>
        <form action="{{ route('entry.logout') }}" method="POST" style="margin:0">
          @csrf
          <button type="submit" title="Logout" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:.85rem;padding:4px">
            <i class="fa-solid fa-right-from-bracket"></i>
          </button>
        </form>
      </div>
    @endif
    <div class="sidebar-date">
      <i class="fa-solid fa-calendar-days"></i>
      <span>{{ now()->format('d M Y') }}</span>
    </div>
  </div>
</aside>

<style>
.sb-badge {
  margin-left: auto;
  font-size: .62rem;
  font-family: var(--font-latin);
  font-weight: 700;
  padding: .1em .5em;
  border-radius: 999px;
  min-width: 18px;
  text-align: center;
  background: rgba(255,255,255,.15);
  color: #fff;
  flex-shrink: 0;
}
.sb-badge-danger  { background: rgba(239,68,68,.35);  color: #fca5a5; }
.sb-badge-warning { background: rgba(245,158,11,.35); color: #fde68a; }

/* ═══════════════════════════════════════════════════════
   MOBILE OPTIMISATIONS (phone-first polish)
═══════════════════════════════════════════════════════ */
html {
  -webkit-text-size-adjust: 100%;   /* stop iOS auto-resizing text in landscape */
  text-size-adjust: 100%;
}
* { -webkit-tap-highlight-color: rgba(79,70,229,.15); } /* subtle tap feedback, no grey box */

@media (max-width: 1024px) {
  /* Smooth momentum scrolling for tables, with a visible scrollbar */
  .tbl-wrap {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }
  /* Keep the first column visible while scrolling wide tables sideways */
  .data-table thead th:first-child,
  .data-table tbody td:first-child {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
  }
  .data-table thead th:first-child { z-index: 3; background: #f1f5f9; }
  .data-table tbody tr:nth-child(even) td:first-child { background: #fafbff; }

  /* Comfortable touch targets (min ~44px tall) */
  .btn, .btn-sm, .form-select, .form-control,
  .pos-btn, .mob-nav-item, .dropdown-item {
    min-height: 42px;
  }
  .btn-icon { min-width: 42px; }

  /* Tap-friendly spacing for action buttons in rows */
  .data-table .btn { padding-inline: .7rem; }
}

/* Prevent iOS zoom-on-focus: inputs must be ≥16px on phones */
@media (max-width: 640px) {
  input, select, textarea,
  .form-control, .form-select,
  .form-control-sm, .form-select-sm {
    font-size: 16px !important;
  }
  /* Stack header action rows so buttons don't overflow */
  .d-flex.justify-content-between { gap: .6rem; }
}

/* Honour reduced-motion globally */
@media (prefers-reduced-motion: reduce) {
  * { animation-duration: .001ms !important; transition-duration: .001ms !important; }
}

/* ═══════════════════════════════════════════════════════
   MOBILE RESPONSIVENESS (Phase 6)
═══════════════════════════════════════════════════════ */
@media (max-width: 991px) {
  /* Hide sidebar off canvas */
  .sidebar {
    transform: translateX(-100%);
  }
  .sidebar.open {
    transform: translateX(0);
  }
  
  /* Reset main layout offset */
  .main-wrap {
    margin-left: 0 !important;
  }
  
  /* Maximize usable screen real-estate and clear bottom nav */
  .page-content {
    padding: 1rem;
    padding-bottom: 80px; /* Make room for mobile bottom nav */
  }
  
  /* Make KPI Cards stack beautifully */
  .row.mb-4.g-4 {
    display: flex;
    flex-wrap: wrap;
  }
  .row.mb-4.g-4 > div {
    flex: 0 0 50%;
    max-width: 50%;
  }
}

@media (max-width: 575px) {
  /* On very small phones, stack KPIs vertically */
  .row.mb-4.g-4 > div {
    flex: 0 0 100%;
    max-width: 100%;
  }
  
  /* Hide search bar to make room for hamburger and title */
  .topbar-search {
    display: none !important;
  }
}
</style>

<!-- Sidebar overlay for mobile -->
<div id="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Mobile Bottom Nav -->
<nav class="mobile-bottom-nav">
  <a href="{{ route('dashboard') }}"
     class="mob-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i>
    <span>Dashboard</span>
    @if($_unread > 0)<span class="mob-nav-badge">{{ $_unread }}</span>@endif
  </a>
  <a href="{{ route('printing.index') }}"
     class="mob-nav-item {{ request()->routeIs('printing.index') ? 'active' : '' }}">
    <i class="bi bi-printer"></i>
    <span>បោះពុម្ព</span>
  </a>
  <a href="{{ route('stock.movements.daily') }}?category=paper"
     class="mob-nav-item {{ request()->routeIs('stock.movements.daily') ? 'active' : '' }}">
    <i class="bi bi-pencil-square"></i>
    <span>Stock</span>
  </a>
  <a href="{{ route('printing.report') }}"
     class="mob-nav-item {{ request()->routeIs('printing.report') ? 'active' : '' }}">
    <i class="bi bi-bar-chart-line"></i>
    <span>Report</span>
  </a>
  <button class="mob-nav-item" onclick="toggleSidebar()" style="background:none;border:none;cursor:pointer;color:#64748b">
    <i class="bi bi-list"></i>
    <span>Menu</span>
  </button>
</nav>

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
      {{-- Global Search --}}
      <form action="{{ route('search') }}" method="GET" style="margin:0" class="topbar-search">
        <div style="position:relative">
          <input type="text" 
                 name="q" 
                 value="{{ request('q') }}"
                 placeholder="ស្វែងរក..."
                 id="global-search-input"
                 style="width:280px;padding:.4rem .75rem .4rem 2.2rem;border:1px solid var(--border);
                        border-radius:999px;font-size:.82rem;background:var(--surface-2);
                        transition:all .2s"
                 autocomplete="off">
          <i class="bi bi-search" 
             style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);
                    color:var(--text-muted);font-size:.9rem;pointer-events:none"></i>
        </div>
      </form>

      <div class="topbar-badge">
        <i class="bi bi-clock" style="color:var(--primary)"></i>
        <span id="live-time">--:--</span>
      </div>
      <a href="{{ route('notifications.index') }}"
         id="notif-bell"
         style="position:relative;display:flex;align-items:center;gap:.4rem;padding:.3rem .75rem;background:var(--surface-2);border:1px solid var(--border);border-radius:999px;font-size:.78rem;color:var(--text-secondary);text-decoration:none">
        <i class="bi bi-bell" style="color:var(--primary)"></i>
        @if(isset($_unread) && $_unread > 0)
          <span id="notif-count"
                style="position:absolute;top:-4px;right:-4px;min-width:17px;height:17px;
                       background:var(--danger);color:#fff;border-radius:999px;
                       font-size:.6rem;font-family:var(--font-latin);font-weight:700;
                       display:flex;align-items:center;justify-content:center;padding:0 3px;
                       border:2px solid var(--surface)">
            {{ $_unread > 99 ? '99+' : $_unread }}
          </span>
        @endif
      </a>

      {{-- Language Switcher --}}
      <div style="display:flex;gap:.3rem;background:var(--surface-2);border:1px solid var(--border);border-radius:999px;padding:.2rem">
        @php $currentLocale = app()->getLocale(); @endphp
        <a href="{{ route('lang.switch', 'km') }}" 
           class="lang-btn {{ $currentLocale === 'km' ? 'active' : '' }}"
           title="ភាសាខ្មែរ">
          🇰🇭 <span class="lang-text">ខ្មែរ</span>
        </a>
        <a href="{{ route('lang.switch', 'en') }}" 
           class="lang-btn {{ $currentLocale === 'en' ? 'active' : '' }}"
           title="English">
          🇬🇧 <span class="lang-text">EN</span>
        </a>
      </div>

      {{-- Theme Switcher --}}
      <button onclick="toggleTheme()" 
              style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;background:var(--surface-2);border:1px solid var(--border);border-radius:50%;cursor:pointer;color:var(--text-secondary);font-size:1.1rem;transition:all .2s"
              id="theme-toggle-btn"
              title="Toggle Light/Dark Theme">
        <i class="bi bi-moon" id="theme-icon"></i>
      </button>
    </div>
  </header>

<style>
.lang-btn {
  display: flex;
  align-items: center;
  gap: .3rem;
  padding: .35rem .7rem;
  border-radius: 999px;
  font-size: .75rem;
  font-weight: 600;
  text-decoration: none;
  color: var(--text-muted);
  transition: all .2s;
}
.lang-btn:hover {
  background: rgba(79,70,229,.1);
  color: var(--primary);
}
.lang-btn.active {
  background: var(--primary);
  color: #fff;
}
@media (max-width: 575px) {
  .lang-text { display: none; }
  .lang-btn { padding: .35rem; }
}
.lang-btn .lang-text {
  font-family: var(--font-latin);
}
@media (max-width: 640px) {
  .lang-btn .lang-text { display: none; }
}
</style>

  <!-- Loading overlay -->
  <div id="loading-overlay">
    <div class="lo-card">
      <div class="spinner"></div>
      <p id="loading-overlay-text">កំពុងដំណើរការ... / Processing...</p>
    </div>
  </div>

  <!-- Page content -->
  <main class="page-content">
    @yield('breadcrumbs')
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
  if (open) {
    ov.classList.add('show-overlay');
  } else {
    ov.classList.remove('show-overlay');
  }
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
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: duration || 2500,
      timerProgressBar: true,
      background: isDark ? '#1e293b' : '#ffffff',
      color: isDark ? '#f1f5f9' : '#0f172a',
      didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer;
          toast.onmouseleave = Swal.resumeTimer;
      }
  });
  Toast.fire({ icon: type, title: message });
}

/* showAlert — keeps backward compat; routes to toast for info/warning/success,
   uses Swal only for explicit confirm() flows */
function showAlert(type, message) {
  showToast(type, message);
  // return a resolved promise so existing await showAlert(...) chains don't break
  return Promise.resolve();
}

/* ── Live notification badge poll ─── */
(function pollNotifications() {
  const badge = document.getElementById('notif-count');
  if (!badge) return;

  function refresh() {
    fetch('{{ route("notifications.count") }}', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
      if (!data) return;
      const n = data.unread || 0;
      badge.textContent = n > 99 ? '99+' : n;
      badge.style.display = n > 0 ? 'flex' : 'none';
    })
    .catch(() => {});
  }

  refresh();
  setInterval(refresh, 30000);
})();

function showLoading(show = true, text = 'កំពុងដំណើរការ... / Processing...') {
  const el = document.getElementById('loading-overlay-text');
  if (el && show) {
    el.textContent = text;
  }
  document.getElementById('loading-overlay').classList.toggle('show', show);
}

/* ── Flash messages (server-side) ────────── */

</script>

{{-- Global Search Keyboard Shortcut (Ctrl+K) --}}
<script>
document.addEventListener('keydown', (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    const searchInput = document.getElementById('global-search-input');
    if (searchInput) {
      searchInput.focus();
      searchInput.select();
    }
  }
});

// Search input focus styling
const searchInput = document.getElementById('global-search-input');
if (searchInput) {
  searchInput.addEventListener('focus', () => {
    searchInput.style.background = 'var(--surface)';
    searchInput.style.boxShadow = '0 0 0 3px var(--primary-glow)';
    searchInput.style.borderColor = 'var(--primary)';
    searchInput.style.width = '320px';
  });
  searchInput.addEventListener('blur', () => {
    searchInput.style.background = 'var(--surface-2)';
    searchInput.style.boxShadow = 'none';
    searchInput.style.borderColor = 'var(--border)';
    searchInput.style.width = '280px';
  });
}

/* ── Theme Switcher Functions ── */
function toggleTheme() {
    let ptTheme = localStorage.getItem('pt_theme') || 'light';
    let currentEffectiveTheme = ptTheme;
    if (ptTheme === 'system') {
        currentEffectiveTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    
    const newTheme = currentEffectiveTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('pt_theme', newTheme);
    updateThemeIcon();
    
    if (typeof highlightActiveThemeButtons === 'function') {
        highlightActiveThemeButtons();
    }
}

function updateThemeIcon() {
    let ptTheme = localStorage.getItem('pt_theme') || 'light';
    let currentEffectiveTheme = ptTheme;
    if (ptTheme === 'system') {
        currentEffectiveTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        if (currentEffectiveTheme === 'dark') {
            themeIcon.className = 'bi bi-moon-fill';
            document.getElementById('theme-toggle-btn').title = 'Switch to Light Mode';
        } else {
            themeIcon.className = 'bi bi-brightness-high-fill';
            document.getElementById('theme-toggle-btn').title = 'Switch to Dark Mode';
        }
    }
}
document.addEventListener('DOMContentLoaded', updateThemeIcon);

/* ── SweetAlert2 Form Interceptor & Global Loader ── */
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.hasAttribute('data-confirm')) {
        e.preventDefault();
        const msg = form.getAttribute('data-confirm');
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        
        Swal.fire({
            title: 'បញ្ជាក់ / Confirmation',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'យល់ព្រម / Yes',
            cancelButtonText: 'បោះបង់ / Cancel',
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                form.removeAttribute('data-confirm');
                showLoading(true);
                form.submit();
            }
        });
    } else {
        if (form.checkValidity && !form.checkValidity()) {
            return;
        }
        NProgress.start();
        showLoading(true);
    }
});

/* ── Global Link Click Transition Loader ── */
document.addEventListener('click', function(e) {
    const anchor = e.target.closest('a');
    if (anchor && 
        anchor.href && 
        !anchor.href.startsWith('javascript:') && 
        !anchor.href.startsWith('#') &&
        !anchor.getAttribute('href').startsWith('#') &&
        anchor.target !== '_blank' && 
        !e.ctrlKey && 
        !e.metaKey &&
        !anchor.classList.contains('no-loader')) {
        
        const currentUrl = window.location.href.split('#')[0];
        const targetUrl = anchor.href.split('#')[0];
        if (targetUrl !== '' && targetUrl !== currentUrl && targetUrl !== currentUrl + '#') {
            NProgress.start();
        }
    }
});
window.addEventListener('pageshow', () => NProgress.done());

/* ── Command Palette (Ctrl+K) ── */
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const cmdModal = new bootstrap.Modal(document.getElementById('cmdPaletteModal'));
        cmdModal.show();
        setTimeout(() => document.getElementById('cmdSearchInput').focus(), 150);
    }
});
</script>

<!-- Command Palette Modal -->
<div class="modal fade" id="cmdPaletteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--surface); border:1px solid var(--border-dark); border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
      <div class="modal-body p-0">
        <form action="{{ route('search') }}" method="GET" class="d-flex align-items-center m-0 px-3 py-2">
          <i class="bi bi-search text-muted me-2" style="font-size:1.2rem;"></i>
          <input type="text" name="q" id="cmdSearchInput" class="form-control border-0 shadow-none" placeholder="ស្វែងរកអ្វីៗ (Search anything...) — ចុច Enter" style="font-size:1.1rem; padding:1rem 0; background:transparent;" autocomplete="off">
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>
<!-- Gamification: Canvas Confetti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js').catch(err => {
        console.log('SW Registration Failed: ', err);
      });
    });
  }
</script>

{{-- ── Global SweetAlert2 Toasts for Flash Messages ── --}}
@if(session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session()->has('success'))
    showToast('success', @json(session('success')));
    @endif
    @if(session()->has('error'))
    showToast('error', @json(session('error')));
    @endif
    @if(session()->has('warning'))
    showToast('warning', @json(session('warning')));
    @endif
    @if(session()->has('info'))
    showToast('info', @json(session('info')));
    @endif
});
</script>
@endif

{{-- ── Personalized Welcome Toast (Once per session) ── --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('pt_welcomed')) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const userName = "{{ Auth::user()->name ?? 'Admin' }}";
        
        setTimeout(() => {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'info',
                title: 'Welcome Back, ' + userName + '! 👋',
                text: 'Let\'s get some printing done today.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: isDark ? '#1e293b' : '#ffffff',
                color: isDark ? '#f1f5f9' : '#0f172a',
                customClass: {
                    popup: 'animated slideInUp'
                }
            });
            
            // Add a little bounce to the bell to draw attention
            const bell = document.querySelector('.bi-bell');
            if(bell) {
                bell.classList.add('bell-bouncing');
                setTimeout(() => bell.classList.remove('bell-bouncing'), 3000);
            }
            
            sessionStorage.setItem('pt_welcomed', 'true');
        }, 1000); // Wait 1 second after load to be polite
    }
});
</script>

@stack('scripts')
</body>
</html>
