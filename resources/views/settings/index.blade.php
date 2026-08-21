@extends('layouts.app')
@section('title', app()->getLocale() == 'km' ? 'ការកំណត់' : 'Settings')
@section('page-title', app()->getLocale() == 'km' ? 'ការកំណត់' : 'Settings')

@section('content')
<style>
/* SaaS Settings Layout */
.settings-container {
    max-width: 900px;
    margin: 0 auto;
}
.settings-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}
.settings-section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1.5rem;
}
.settings-section-title i {
    color: var(--primary);
    background: rgba(99, 102, 241, 0.1);
    padding: 0.4rem;
    border-radius: 8px;
    font-size: 1.1rem;
}

/* Selectable Cards */
.opt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.opt-card {
    border: 1.5px solid var(--border-dark);
    border-radius: 12px;
    padding: 1rem;
    background: rgba(255,255,255,0.02);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s ease;
    color: var(--text-secondary);
    position: relative;
    overflow: hidden;
}
.opt-card:hover {
    background: var(--surface-2);
    transform: translateY(-2px);
}
.opt-card.active {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
    color: var(--primary);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
}
.opt-card.active::after {
    content: '\F26A'; /* Bootstrap icon check */
    font-family: 'bootstrap-icons';
    position: absolute;
    right: 1rem;
    color: var(--primary);
    font-size: 1.2rem;
}

.opt-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--surface-2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: var(--text-primary);
    transition: all 0.2s ease;
}
.opt-card.active .opt-icon-box {
    background: var(--primary);
    color: white;
}
.opt-label {
    font-weight: 700;
    font-size: 0.95rem;
}

[data-theme="dark"] .opt-card {
    background: rgba(0,0,0,0.15);
}
[data-theme="dark"] .opt-card:hover {
    background: rgba(255,255,255,0.05);
}
</style>

<div class="settings-container">
    
    <!-- Theme Preferences -->
    <div class="settings-section">
        <div class="settings-section-title">
            <i class="bi bi-palette"></i> {{ app()->getLocale() == 'km' ? 'រូបរាង' : 'Appearance' }}
        </div>
        
        <div class="opt-grid">
            <div class="opt-card" id="theme-light" onclick="setThemeMode('light')">
                <div class="opt-icon-box"><i class="bi bi-sun"></i></div>
                <div class="opt-label">{{ app()->getLocale() == 'km' ? 'ភ្លឺ' : 'Light' }}</div>
            </div>
            
            <div class="opt-card" id="theme-dark" onclick="setThemeMode('dark')">
                <div class="opt-icon-box"><i class="bi bi-moon"></i></div>
                <div class="opt-label">{{ app()->getLocale() == 'km' ? 'ងងឹត' : 'Dark' }}</div>
            </div>
            
            <div class="opt-card" id="theme-system" onclick="setThemeMode('system')">
                <div class="opt-icon-box"><i class="bi bi-display"></i></div>
                <div class="opt-label">{{ app()->getLocale() == 'km' ? 'តាមប្រព័ន្ធ' : 'System' }}</div>
            </div>
        </div>
    </div>

    <!-- Language Preferences -->
    <div class="settings-section">
        <div class="settings-section-title">
            <i class="bi bi-translate"></i> {{ app()->getLocale() == 'km' ? 'ភាសា' : 'Language' }}
        </div>
        
        <div class="opt-grid">
            <div class="opt-card {{ app()->getLocale() === 'km' ? 'active' : '' }}" onclick="window.location='{{ route('lang.switch', 'km') }}'">
                <div class="opt-icon-box" style="font-family:var(--font-latin);font-weight:800;font-size:.9rem">KH</div>
                <div class="opt-label">ខ្មែរ</div>
            </div>
            
            <div class="opt-card {{ app()->getLocale() === 'en' ? 'active' : '' }}" onclick="window.location='{{ route('lang.switch', 'en') }}'">
                <div class="opt-icon-box" style="font-family:var(--font-latin);font-weight:800;font-size:.9rem">EN</div>
                <div class="opt-label">អង់គ្លេស</div>
            </div>
        </div>
    </div>

    <!-- Account & Profile -->
    <div class="settings-section">
        <div class="settings-section-title">
            <i class="bi bi-person-badge"></i> {{ app()->getLocale() == 'km' ? 'គណនីរបស់អ្នក' : 'Your Account' }}
        </div>
        
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;font-weight:800;box-shadow:0 4px 15px var(--primary-glow)">
                {{ strtoupper(substr($userName, 0, 1)) }}
            </div>
            <div>
                <h4 style="font-weight:700;margin-bottom:.25rem;color:var(--text-primary)">{{ $userName }}</h4>
                <div class="badge bg-primary-glow text-primary" style="font-size:.8rem;padding:.35rem .85rem;border-radius:999px;">
                    {{ ucfirst(str_replace('_', ' ', $userPosition)) }}
                </div>
            </div>
        </div>
        
        <div style="margin-top:2rem;display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;border-top:1px solid var(--border);padding-top:1.5rem">
            <div>
                <div style="font-size:.75rem;color:var(--text-secondary);font-weight:600;margin-bottom:.25rem">{{ app()->getLocale() == 'km' ? 'តួនាទី' : 'Role' }}</div>
                <div style="font-weight:700;font-family:var(--font-latin);color:var(--text-primary)">{{ strtoupper($userRole) }}</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--text-secondary);font-weight:600;margin-bottom:.25rem">{{ app()->getLocale() == 'km' ? 'ថ្ងៃចូលប្រើ' : 'Logged In' }}</div>
                <div style="font-weight:700;font-family:var(--font-latin);color:var(--text-primary)">{{ session('logged_in_at') ?? 'Just now' }}</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--text-secondary);font-weight:600;margin-bottom:.25rem">{{ app()->getLocale() == 'km' ? 'អាសយដ្ឋាន IP' : 'IP Address' }}</div>
                <div style="font-weight:700;font-family:var(--font-latin);color:var(--text-primary)">{{ request()->ip() }}</div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
  function setThemeMode(mode) {
      if (mode === 'system') {
          const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
          document.documentElement.setAttribute('data-theme', sysDark ? 'dark' : 'light');
      } else {
          document.documentElement.setAttribute('data-theme', mode);
      }
      localStorage.setItem('pt_theme', mode);
      highlightActiveThemeButtons();
  }

  function highlightActiveThemeButtons() {
      const theme = localStorage.getItem('pt_theme') || 'light';
      
      document.getElementById('theme-light').classList.remove('active');
      document.getElementById('theme-dark').classList.remove('active');
      document.getElementById('theme-system').classList.remove('active');
      
      if (theme === 'dark') {
          document.getElementById('theme-dark').classList.add('active');
      } else if (theme === 'system') {
          document.getElementById('theme-system').classList.add('active');
      } else {
          document.getElementById('theme-light').classList.add('active');
      }
  }

  document.addEventListener('DOMContentLoaded', highlightActiveThemeButtons);
</script>
@endpush
@endsection
