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

    <!-- Daily Report Tracking Maintenance & Health Check -->
    <div class="settings-section">
        <div class="settings-section-title d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-primary"></i>
                <span>{{ app()->getLocale() == 'km' ? 'ការតាមដានរបាយការណ៍ និងថែទាំប្រព័ន្ធ' : 'Daily Report Tracking & Maintenance' }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.tracking.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-grid-3x3-gap me-1"></i>{{ app()->getLocale() == 'km' ? 'ផ្ទាំងតាមដាន' : 'Tracking Matrix' }}
                </a>
                <a href="{{ route('reports.requirements.index') }}" class="btn btn-sm btn-dark">
                    <i class="bi bi-people me-1"></i>{{ app()->getLocale() == 'km' ? 'កាលវិភាគបុគ្គលិក' : 'Staff Requirements' }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center py-2 mb-3" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Live Health & Status Cards -->
        <div class="p-3 rounded-3 bg-light border mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-broadcast me-1"></i>Active / Polling
                    </span>
                    <span class="small text-muted font-monospace">
                        <i class="bi bi-clock me-1"></i>{{ $trackingStats['local_time'] ?? now('Asia/Phnom_Penh')->format('d/m/Y h:i A') }} (Asia/Phnom_Penh)
                    </span>
                </div>
                <div class="small fw-semibold text-secondary">
                    កាលវិភាគសកម្ម: <span class="text-primary">{{ $trackingStats['active_requirements'] ?? 0 }}</span> / {{ $trackingStats['total_requirements'] ?? 0 }}
                </div>
            </div>

            <!-- Today's Metric Badges -->
            <div class="row g-2 text-center">
                <div class="col-6 col-sm-3 col-md">
                    <div class="p-2 bg-white rounded-2 border">
                        <div class="small text-muted" style="font-size:0.75rem">បានផ្ញើ (On-time)</div>
                        <div class="fs-5 fw-bold text-success">{{ $trackingStats['today_submitted'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3 col-md">
                    <div class="p-2 bg-white rounded-2 border">
                        <div class="small text-muted" style="font-size:0.75rem">យឺត (Late)</div>
                        <div class="fs-5 fw-bold text-warning-emphasis">{{ $trackingStats['today_late'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3 col-md">
                    <div class="p-2 bg-white rounded-2 border">
                        <div class="small text-muted" style="font-size:0.75rem">មិនបានផ្ញើ (Missed)</div>
                        <div class="fs-5 fw-bold text-danger">{{ $trackingStats['today_missed'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3 col-md">
                    <div class="p-2 bg-white rounded-2 border">
                        <div class="small text-muted" style="font-size:0.75rem">រង់ចាំ (Pending)</div>
                        <div class="fs-5 fw-bold text-secondary">{{ $trackingStats['today_pending'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md">
                    <div class="p-2 bg-white rounded-2 border">
                        <div class="small text-muted" style="font-size:0.75rem">Alerts ផ្ញើរួច</div>
                        <div class="fs-5 fw-bold text-primary">{{ $trackingStats['today_alerts_sent'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Action Buttons -->
        <div class="mb-4">
            <div class="small fw-bold text-uppercase text-muted mb-2">
                <i class="bi bi-tools me-1"></i>ឧបករណ៍ថែទាំ និងត្រួតពិនិត្យ (Maintenance & Check Tools)
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('settings.report-tracking.check') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm" title="ពិនិត្យ និងផ្ញើ Alert ឥឡូវនេះ">
                        <i class="bi bi-bell-fill me-1"></i>Check Deadlines & Alert Now
                    </button>
                </form>

                <form action="{{ route('settings.report-tracking.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm" title="ធ្វើសមកាលកម្មបង្កើតកំណត់ត្រាថ្ងៃនេះ">
                        <i class="bi bi-arrow-repeat me-1"></i>Sync Today's Records
                    </button>
                </form>

                <form action="{{ route('settings.report-tracking.fetch-users') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm" title="ទាញយកគណនី Telegram ទាំងអស់ពីក្រុមស្វ័យប្រវត្តិ">
                        <i class="bi bi-cloud-arrow-down me-1"></i>Fetch Users from Telegram ({{ \App\Models\TelegramUser::count() }})
                    </button>
                </form>

                <form action="{{ route('reports.tracking.send-summary') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-info btn-sm" title="ផ្ញើសេចក្តីសង្ខេបទៅ Telegram ឥឡូវនេះ">
                        <i class="bi bi-telegram me-1"></i>Send Summary to Telegram
                    </button>
                </form>

                <form action="{{ route('settings.report-tracking.reset-alerts') }}" method="POST" class="d-inline"
                      onsubmit="return confirm('តើអ្នកចង់កំណត់ Alert ឡើងវិញសម្រាប់ការធ្វើតេស្តមែនទេ? (Reset today alerts for testing?)');">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm" title="កំណត់ Alert ឡើងវិញដើម្បីអាច Test Alert ម្តងទៀតបាន">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Alerts (Test Mode)
                    </button>
                </form>
            </div>
            <div class="form-text mt-1" style="font-size:0.8rem">
                ជំនួយ: ចុច "Reset Alerts" ដើម្បីលុបស្ថានភាព Alert ថ្ងៃនេះ ដើម្បីអាចធ្វើតេស្តបាញ់ Alert ឡើងវិញបាន។
            </div>
        </div>

        <!-- Configuration Settings Form -->
        <form action="{{ route('settings.report-tracking.save') }}" method="POST" class="border-top pt-3">
            @csrf
            <div class="small fw-bold text-uppercase text-muted mb-3">
                <i class="bi bi-sliders me-1"></i>ការកំណត់ប្រព័ន្ធទូទៅ (Global Configuration)
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">គោលការណ៍សារស្ទួន (Duplicate Report Policy)</label>
                    <select name="report_duplicate_policy" class="form-select form-select-sm">
                        <option value="first_valid" {{ ($reportSettings['duplicate_policy'] ?? 'first_valid') === 'first_valid' ? 'selected' : '' }}>
                            First Valid Report (ណែនាំ: រក្សាទុកពេលផ្ញើលើកដំបូង និងកត់ត្រាការកែប្រែជា Revision)
                        </option>
                        <option value="latest_valid" {{ ($reportSettings['duplicate_policy'] ?? '') === 'latest_valid' ? 'selected' : '' }}>
                            Latest Valid Report (ប្រើប្រាស់ពេលផ្ញើចុងក្រោយជាផ្លូវការ)
                        </option>
                    </select>
                </div>

                @php
                    $curChatId   = (string)($reportSettings['alert_chat_id'] ?? '');
                    $curThreadId = (string)($reportSettings['alert_thread_id'] ?? '');
                    $matchedKnown = false;
                    foreach ($telegramGroups as $tg) {
                        if ($curChatId === (string)$tg->chat_id && $curThreadId === (string)($tg->message_thread_id ?? '')) {
                            $matchedKnown = true;
                            break;
                        }
                    }
                    $isCustomTarget = (!empty($curChatId) && !$matchedKnown);
                @endphp

                <!-- Group Selector Dropdown -->
                <div class="col-md-12">
                    <label class="form-label fw-semibold small d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-telegram text-primary me-1"></i>
                            ជ្រើសរើសក្រុម/Topic ទទួល Alert (Choose Telegram Group / Topic)
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            ស្វ័យប្រវត្តិតាម Telegram
                        </span>
                    </label>

                    <select name="report_alert_target" id="report_alert_target" class="form-select form-select-sm" onchange="onAlertTargetChange(this)" style="font-size:0.92rem;">
                        <option value="" {{ empty($curChatId) ? 'selected' : '' }}>
                            — ប្រើប្រាស់ Default ក្នុង .env ({{ config('services.telegram.alert_chat_id') ?: 'Not configured' }}) —
                        </option>

                        @if(isset($groupedChats))
                            @foreach($groupedChats as $cId => $chatGrps)
                                @php $baseName = $chatGrps->first()->name ?? 'Group '.$cId; @endphp
                                <optgroup label="📢 {{ $baseName }} (ID: {{ $cId }})">
                                    @foreach($chatGrps as $g)
                                        @php
                                            $val = $g->chat_id . '|' . ($g->message_thread_id ?? '');
                                            $isSelected = ($curChatId === (string)$g->chat_id && $curThreadId === (string)($g->message_thread_id ?? ''));
                                            
                                            if ($g->message_thread_id) {
                                                $disp = ($g->topic_name ?: 'Topic #'.$g->message_thread_id) . ' (#'.$g->message_thread_id.')';
                                            } else {
                                                $disp = $g->name . ' — (General / Main Channel)';
                                            }
                                        @endphp
                                        <option value="{{ $val }}" data-chat="{{ $g->chat_id }}" data-thread="{{ $g->message_thread_id ?? '' }}" {{ $isSelected ? 'selected' : '' }}>
                                            {{ $g->message_thread_id ? '↳ 📌 ' : '👥 ' }}{{ $disp }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @endif

                        <option value="custom" {{ $isCustomTarget ? 'selected' : '' }}>
                            ✏️ បញ្ចូលលេខសម្គាល់ដោយផ្ទាល់ (Custom Chat ID / Thread ID)
                        </option>
                    </select>
                    <div class="form-text mt-1 text-muted" style="font-size:0.8rem">
                        ជ្រើសរើសក្រុម ឬ Forum Topic ណាមួយដែល bot បានចូលរួម ដើម្បីទទួលការជូនដំណឹងពេលបុគ្គលិកមិនទាន់បានផ្ញើរបាយការណ៍។
                    </div>
                </div>

                <!-- Custom Chat ID & Thread ID (Toggled or shown if Custom selected) -->
                <div id="customAlertTargetWrapper" class="col-12" style="{{ $isCustomTarget ? '' : 'display: none;' }}">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="small fw-bold text-secondary mb-2">
                            <i class="bi bi-pencil-square me-1"></i>កំណត់លេខសម្គាល់ដោយផ្ទាល់ (Manual Telegram IDs)
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Custom Telegram Chat ID</label>
                                <input type="text" name="report_alert_chat_id" id="report_alert_chat_id"
                                       class="form-control form-control-sm font-monospace"
                                       value="{{ $curChatId }}" placeholder="-100... or -464...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Custom Topic Thread ID (Optional)</label>
                                <input type="number" name="report_alert_thread_id" id="report_alert_thread_id"
                                       class="form-control form-control-sm font-monospace"
                                       value="{{ $curThreadId }}" placeholder="e.g. 881">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="report_send_ack" value="1" id="setting_send_ack"
                               {{ ($reportSettings['send_ack'] ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small" for="setting_send_ack">
                            ផ្ញើវិក្កយបត្រទទួលពេលផ្ញើយឺត (Send Late Receipt on Telegram)
                        </label>
                    </div>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-save me-1"></i>រក្សាទុកការកំណត់ (Save Settings)
                    </button>
                </div>
            </div>
        </form>
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
