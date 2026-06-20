<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Printing Tracking System</title>
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
  padding: 1rem;
}
.entry-card {
  background: #fff;
  border-radius: 20px;
  padding: 2.5rem 2rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 24px 80px rgba(0,0,0,.3);
  text-align: center;
}
.entry-logo {
  width: 64px; height: 64px;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem;
  margin: 0 auto 1.25rem;
  box-shadow: 0 8px 24px rgba(79,70,229,.4);
}
.entry-title {
  font-size: 1.4rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: .3rem;
}
.entry-sub {
  font-size: .85rem;
  color: #64748b;
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
  color: #475569;
  margin-bottom: .4rem;
}
.form-group input,
.form-group select {
  width: 100%;
  padding: .75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  font-family: inherit;
  transition: border-color .2s, box-shadow .2s;
  background: #f8fafc;
}
.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #4f46e5;
  box-shadow: 0 0 0 4px rgba(79,70,229,.12);
  background: #fff;
}
.form-group input::placeholder { color: #94a3b8; }
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
  transition: transform .1s, box-shadow .2s;
  box-shadow: 0 4px 16px rgba(79,70,229,.3);
  margin-top: .5rem;
}
.btn-enter:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(79,70,229,.4); }
.btn-enter:active { transform: scale(.98); }
.btn-enter:disabled { opacity: .6; cursor: not-allowed; }
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
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  background: #f8fafc;
  cursor: pointer;
  transition: border-color .2s, background .2s, transform .1s;
  font-size: .78rem;
  font-weight: 600;
  color: #475569;
}
.pos-btn i { font-size: 1.4rem; color: #64748b; transition: color .2s; }
.pos-btn:hover { border-color: #a5b4fc; background: #eef2ff; }
.pos-btn.selected {
  border-color: #4f46e5;
  background: #eef2ff;
  color: #4f46e5;
}
.pos-btn.selected i { color: #4f46e5; }
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
</style>
</head>
<body>

<div class="entry-card">
  <div class="entry-logo">🖨️</div>
  <h1 class="entry-title">Printing Tracking System</h1>
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

// Save on submit
form.addEventListener('submit', () => {
  localStorage.setItem('pt_name', nameInput.value.trim());
  localStorage.setItem('pt_position', posInput.value);
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
