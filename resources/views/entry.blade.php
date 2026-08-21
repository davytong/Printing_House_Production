<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0f172a">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>BELTEI University Press</title>
<link rel="icon" href="{{ asset('images/logo.jpg') }}" type="image/jpeg">
<link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Hanuman:wght@400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Poppins', 'Hanuman', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
  padding: max(1rem, env(safe-area-inset-top)) max(1rem, env(safe-area-inset-right)) max(1rem, env(safe-area-inset-bottom)) max(1rem, env(safe-area-inset-left));
  position: relative;
  overflow: hidden;
}

/* ── Radar sweep background ── */
.bg-circles {
  position: fixed;
  inset: 0;
  z-index: 0;
  overflow: hidden;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
}
/* concentric radar rings */
.bg-circles .r-ring {
  position: absolute;
  border-radius: 50%;
  border: 1px solid color-mix(in srgb, var(--accent) 22%, transparent);
}
.bg-circles .r-ring:nth-of-type(1){ width: 38vmax; height: 38vmax; }
.bg-circles .r-ring:nth-of-type(2){ width: 66vmax; height: 66vmax; }
.bg-circles .r-ring:nth-of-type(3){ width: 96vmax; height: 96vmax; }
/* faint cross lines */
.bg-circles .r-cross {
  position: absolute;
  background: color-mix(in srgb, var(--accent) 16%, transparent);
}
.bg-circles .r-cross.h { width: 96vmax; height: 1px; }
.bg-circles .r-cross.v { width: 1px; height: 96vmax; }
/* rotating sweep beam */
.bg-circles .r-sweep {
  position: absolute;
  width: 96vmax;
  height: 96vmax;
  border-radius: 50%;
  background: conic-gradient(
    from var(--bd-angle),
    color-mix(in srgb, var(--accent) 55%, transparent) 0deg,
    color-mix(in srgb, var(--accent) 18%, transparent) 35deg,
    transparent 70deg,
    transparent 360deg
  );
  opacity: .7;
  animation: spinBorder 7s linear infinite;
}
/* glowing center dot */
.bg-circles .r-core {
  position: absolute;
  width: 9px; height: 9px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 20px 5px var(--accent);
  opacity: .9;
}

@media (prefers-reduced-motion: reduce) {
  .bg-circles .r-sweep { animation: none; }
}
@property --bd-angle {
  syntax: "<angle>";
  initial-value: 0deg;
  inherits: false;
}
:root { --accent: #6366f1; }
.entry-card {
  background: rgba(17,24,39,.72);
  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);
  border: 1px solid rgba(255,255,255,.10);
  border-radius: 20px;
  padding: 2.5rem 2rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 30px 90px rgba(0,0,0,.55);
  text-align: center;
  position: relative;
  z-index: 1;
  animation: cardIn .7s cubic-bezier(.22,1,.36,1) both;
}
@keyframes cardIn {
  from { opacity: 0; transform: translateY(24px) scale(.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
/* Staggered reveal of inner elements */
.entry-logo,
.entry-title,
.entry-sub,
.form-group,
.btn-enter {
  animation: itemIn .55s cubic-bezier(.22,1,.36,1) both;
}
.entry-logo  { animation-delay: .15s; }
.entry-title { animation-delay: .22s; }
.entry-sub   { animation-delay: .29s; }
.form-group:nth-of-type(1) { animation-delay: .36s; }
.form-group:nth-of-type(2) { animation-delay: .43s; }
.btn-enter   { animation-delay: .50s; }
@keyframes itemIn {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}
/* Single elegant accent light running around the card edge (true ring) */
.entry-card::before,
.entry-card::after {
  content: "";
  position: absolute;
  inset: -1px;
  border-radius: 21px;
  z-index: -1;
  padding: 1.5px;
  background: conic-gradient(
    from var(--bd-angle),
    transparent 0deg,
    transparent 190deg,
    color-mix(in srgb, var(--accent) 35%, transparent) 280deg,
    var(--accent) 350deg,
    transparent 360deg
  );
  /* mask keeps only the border ring, hollow centre */
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
          mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
          mask-composite: exclude;
  animation: spinBorder 7s linear infinite;
}
/* Soft accent glow hugging the border ring */
.entry-card::after {
  z-index: -2;
  filter: blur(14px);
  opacity: .65;
}
@keyframes spinBorder {
  to { --bd-angle: 360deg; }
}
@media (prefers-reduced-motion: reduce) {
  .entry-card::before,
  .entry-card::after { animation: none; }
}

.entry-logo {
  width: 84px; height: 84px;
  background: #fff;
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.25rem;
  box-shadow: 0 8px 24px rgba(15,23,42,.25);
  overflow: hidden;
  padding: 4px;
  transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease;
}
.entry-logo:hover {
  transform: translateY(-2px) scale(1.04);
  box-shadow: 0 12px 32px rgba(15,23,42,.35);
}
.entry-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.entry-title {
  font-size: 1.4rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: .3rem;
}
.entry-sub {
  font-size: .85rem;
  color: rgba(255,255,255,.7);
  margin-bottom: 2rem;
}
.form-group {
  text-align: left;
  margin-bottom: 1.25rem;
}
.form-group label {
  display: block;
  font-size: .82rem;
  font-weight: 600;
  color: rgba(255,255,255,.85);
  margin-bottom: .4rem;
}
.form-group input,
.form-group select {
  width: 100%;
  padding: .75rem 1rem;
  border: 2px solid rgba(255,255,255,.18);
  border-radius: 12px;
  font-size: 1rem;
  font-family: inherit;
  transition: border-color .2s, box-shadow .2s, background .2s;
  background: rgba(255,255,255,.08);
  color: #fff;
}
.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent) 22%, transparent);
  background: rgba(255,255,255,.14);
}
.form-group input::placeholder { color: rgba(255,255,255,.45); }
.btn-enter {
  width: 100%;
  padding: .9rem;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform .15s cubic-bezier(.22,1,.36,1), box-shadow .25s ease, opacity .2s ease;
  box-shadow: 0 4px 16px rgba(79,70,229,.3);
  margin-top: .5rem;
}
.btn-enter:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(79,70,229,.45); }
.btn-enter:active:not(:disabled) { transform: scale(.98); }
.btn-enter:disabled { opacity: .55; cursor: not-allowed; }
.btn-enter .spin {
  display: inline-block;
  width: 15px; height: 15px;
  border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff;
  border-radius: 50%;
  vertical-align: -2px;
  animation: btnspin .6s linear infinite;
}
@keyframes btnspin { to { transform: rotate(360deg); } }
.position-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .6rem;
  margin-top: .5rem;
}
.pos-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .3rem;
  padding: .85rem .5rem;
  border: 2px solid rgba(255,255,255,.15);
  border-radius: 12px;
  background: rgba(255,255,255,.06);
  cursor: pointer;
  transition: border-color .25s ease, background .25s ease, transform .2s cubic-bezier(.22,1,.36,1), box-shadow .25s ease;
  font-size: .78rem;
  font-weight: 600;
  color: rgba(255,255,255,.8);
}
.pos-btn i { font-size: 1.4rem; color: rgba(255,255,255,.6); transition: color .25s ease, transform .25s ease; }
.pos-btn:hover {
  border-color: color-mix(in srgb, var(--accent) 60%, transparent);
  background: rgba(255,255,255,.12);
  transform: translateY(-2px);
}
.pos-btn:hover i { transform: scale(1.12); }
.pos-btn:active { transform: translateY(0) scale(.97); }
.pos-btn.selected {
  border-color: var(--accent);
  background: color-mix(in srgb, var(--accent) 18%, transparent);
  color: #fff;
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
}
.pos-btn.selected i { color: var(--accent); }
.error-msg {
  background: #fef2f2;
  border: 1px solid #fca5a5;
  color: #dc2626;
  border-radius: 10px;
  padding: .65rem 1rem;
  font-size: .82rem;
  margin-bottom: 1rem;
  text-align: left;
}
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: .001ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: .001ms !important;
  }
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
#loading-overlay .lo-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.1rem;
  padding: 2rem 2.5rem;
  background: rgba(30, 41, 59, 0.98);
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(15,23,42,.35);
  animation: lo-pop .3s cubic-bezier(.34,1.56,.64,1);
  border: 1px solid rgba(255,255,255,.08);
}
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
  border-top-color: var(--accent, #6366f1);
  border-right-color: var(--accent, #6366f1);
  animation: spin .8s linear infinite;
}
#loading-overlay .spinner::after {
  border-bottom-color: rgba(99, 102, 241, 0.2);
  border-left-color: rgba(99, 102, 241, 0.2);
  animation: spin 1.2s linear infinite reverse;
}
#loading-overlay p {
  color: #f1f5f9;
  font-size: .92rem;
  font-weight: 600;
  margin: 0;
  text-align: center;
}
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
</style>
</head>
<body>

<!-- Loading overlay -->
<div id="loading-overlay">
  <div class="lo-card">
    <div class="spinner"></div>
    <p>កំពុងដំណើរការ... / Processing...</p>
  </div>
</div>

<!-- Radar sweep background -->
<div class="bg-circles">
  <span class="r-ring"></span>
  <span class="r-ring"></span>
  <span class="r-ring"></span>
  <span class="r-cross h"></span>
  <span class="r-cross v"></span>
  <span class="r-sweep"></span>
  <span class="r-core"></span>
</div>

<div class="entry-card">
  <div class="entry-logo"><img src="{{ asset('images/logo.jpg') }}" alt="BELTEI University Press"></div>
  <h1 class="entry-title">BELTEI University Press</h1>
  <p class="entry-sub">Enter your name and select your position to continue</p>

  @if($errors->any())
    <div class="error-msg">
      <i class="bi bi-exclamation-circle me-1"></i>
      {{ $errors->first() }}
    </div>
  @endif

  <form action="{{ route('entry.login') }}" method="POST" id="entryForm">
    @csrf

    <div class="form-group">
      <label><i class="bi bi-person-fill"></i> Full Name</label>
      <input type="text" name="full_name" value="{{ old('full_name', session('user_name')) }}"
             placeholder="Enter your name..." required autofocus>
    </div>

    <div class="form-group">
      <label><i class="bi bi-briefcase-fill"></i> Position</label>
      <input type="hidden" name="position" id="positionInput" value="{{ old('position') }}" required>
      <div class="position-grid">
        <div class="pos-btn" data-pos="paper_report" onclick="selectPos(this)">
          <i class="bi bi-file-earmark-text"></i>
          <span>Paper Report</span>
        </div>
        <div class="pos-btn" data-pos="press_report" onclick="selectPos(this)">
          <i class="bi bi-printer"></i>
          <span>Press Report</span>
        </div>
        <div class="pos-btn" data-pos="finishing_report" onclick="selectPos(this)">
          <i class="bi bi-scissors"></i>
          <span>Finishing Report</span>
        </div>
        <div class="pos-btn" data-pos="procurement" onclick="selectPos(this)">
          <i class="bi bi-cart3"></i>
          <span>Procurement</span>
        </div>
        <div class="pos-btn" data-pos="store" onclick="selectPos(this)">
          <i class="bi bi-box-seam"></i>
          <span>Store</span>
        </div>
        <div class="pos-btn" data-pos="admin" onclick="selectPos(this)">
          <i class="bi bi-shield-lock"></i>
          <span>Admin</span>
        </div>
      </div>
    </div>

    <button type="submit" class="btn-enter" id="enterBtn" disabled>
      <i class="bi bi-arrow-right-circle"></i> Continue
    </button>
  </form>
</div>

<script>
function selectPos(el) {
  document.querySelectorAll('.pos-btn').forEach(b => b.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('positionInput').value = el.dataset.pos;
  document.getElementById('enterBtn').disabled = false;
}

// ── Remember Me: save to localStorage on submit, restore on load ──
const nameInput = document.querySelector('input[name="full_name"]');
const posInput  = document.getElementById('positionInput');
const form      = document.getElementById('entryForm');

// Save on submit + show smooth loading state
form.addEventListener('submit', () => {
  localStorage.setItem('pt_name', nameInput.value.trim());
  localStorage.setItem('pt_position', posInput.value);

  const btn = document.getElementById('enterBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spin"></span> Entering...';

  // Show global full-screen spinner overlay
  document.getElementById('loading-overlay').classList.add('show');
});

// Restore on load
const savedName = localStorage.getItem('pt_name');
const savedPos  = localStorage.getItem('pt_position');

if (savedName && !nameInput.value) {
  nameInput.value = savedName;
}
if (savedPos && !posInput.value) {
  const btn = document.querySelector(`[data-pos="${savedPos}"]`);
  if (btn) selectPos(btn);
}

// Also restore from old() if present
const oldPos = posInput.value;
if (oldPos) {
  const btn = document.querySelector(`[data-pos="${oldPos}"]`);
  if (btn) selectPos(btn);
}
</script>
</body>
</html>
