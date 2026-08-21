@extends('layouts.app')
@section('title',      'Telegram Bot Setup')
@section('page-title', 'Telegram Bot')

@section('content')

{{-- ════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════ --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap');
    
    .section-title {
        font-family: var(--font-khmer);
        font-weight: 800;
        font-size: 2rem !important;
        letter-spacing: -0.02em;
        background: linear-gradient(135deg, #0f172a, #334155);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .panel {
        background: rgba(255, 255, 255, 0.6) !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        border: 1px solid rgba(255, 255, 255, 0.8) !important;
        border-radius: 24px !important;
        box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05) !important;
        overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .panel:hover {
        transform: translateY(-4px);
        box-shadow: 0 30px 60px -15px rgba(0,0,0,0.1) !important;
    }

    .panel-header {
        background: rgba(255,255,255,0.4) !important;
        border-bottom: 1px solid rgba(0,0,0,0.03) !important;
        padding: 1.5rem !important;
    }

    .ph-title {
        font-family: var(--font-khmer);
        font-weight: 700;
        font-size: 1.15rem;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .ph-icon {
        width: 40px !important;
        height: 40px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 8px 16px -4px rgba(0,0,0,0.1) !important;
        font-size: 1.2rem;
    }

    .panel-body {
        padding: 1.75rem !important;
    }

    .btn {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.6rem 1.25rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: var(--font-khmer);
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0,0,0,0.15);
    }
    
    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: rgba(248, 250, 252, 0.8);
        padding: 0.6rem 1rem;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .step-badge {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        font-family: var(--font-khmer);
        color: white;
        box-shadow: 0 8px 16px -4px rgba(0,0,0,0.15);
        flex-shrink: 0;
    }
    .step-title {
        font-family: var(--font-khmer);
        font-weight: 700;
        font-size: 1.05rem;
        color: #0f172a;
    }
    /* Dark Mode Overrides for Telegram Setup */
    [data-theme="dark"] .panel { background: rgba(30, 41, 59, 0.6) !important; border-color: rgba(255, 255, 255, 0.05) !important; }
    [data-theme="dark"] .panel-header { background: rgba(15, 23, 42, 0.4) !important; border-bottom-color: rgba(255,255,255,0.05) !important; }
    [data-theme="dark"] .ph-title { color: var(--text-primary); }
    [data-theme="dark"] .form-control, [data-theme="dark"] .form-select { background: rgba(15, 23, 42, 0.6); border-color: rgba(255,255,255,0.1); color: var(--text-primary); }
    [data-theme="dark"] div[style*="background:#fffbeb"] { background: rgba(245, 158, 11, 0.1) !important; border-color: rgba(245, 158, 11, 0.2) !important; color: #fcd34d !important; }
    [data-theme="dark"] div[style*="background:#fee2e2"] { background: rgba(239, 68, 68, 0.1) !important; border-color: rgba(239, 68, 68, 0.2) !important; color: #fca5a5 !important; }
    [data-theme="dark"] div[style*="background:#eff6ff"] { background: rgba(59, 130, 246, 0.1) !important; border-color: rgba(59, 130, 246, 0.2) !important; color: #93c5fd !important; }
    [data-theme="dark"] div[style*="background:#dcfce7"] { background: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.2) !important; color: #6ee7b7 !important; }
    [data-theme="dark"] div[style*="background:#f8fafc"] { background: rgba(30, 41, 59, 0.6) !important; border-color: rgba(255,255,255,0.05) !important; }
    [data-theme="dark"] div[style*="background:#fef3c7"] { background: rgba(245, 158, 11, 0.15) !important; color: #fcd34d !important; }
    [data-theme="dark"] div[style*="background:#fef9c3"] { background: rgba(234, 179, 8, 0.15) !important; color: #fde047 !important; }
    [data-theme="dark"] .ph-icon[style*="background:#ede9fe"] { background: rgba(139, 92, 246, 0.15) !important; color: #c4b5fd !important; }
    [data-theme="dark"] .ph-icon[style*="background:#dbeafe"] { background: rgba(59, 130, 246, 0.15) !important; color: #93c5fd !important; }
    [data-theme="dark"] code { background: rgba(255,255,255,0.1) !important; color: var(--text-primary) !important; }
    [data-theme="dark"] .step-badge { box-shadow: 0 4px 10px rgba(0,0,0,0.3) !important; }
</style>
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">Telegram Bot Setup</h1>
    <p class="section-sub">ភ្ជាប់ Bot និងគ្រប់គ្រងក្រុម Telegram សម្រាប់ការផ្ញើរបាយការណ៍</p>
  </div>
</div>

{{-- ════════════════════════════════════════════
     BOT STATUS CARD
════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
  <div class="col-lg-5">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-robot"></i></div>
          <span>Bot Status</span>
        </div>
        @if($botInfo)
          <span class="badge badge-done" style="font-family:var(--font-latin)">
            <i class="bi bi-circle-fill" style="font-size:.45rem"></i> Connected
          </span>
        @else
          <span class="badge badge-pending" style="font-family:var(--font-latin)">
            <i class="bi bi-circle-fill" style="font-size:.45rem"></i> Disconnected
          </span>
        @endif
      </div>
      <div class="panel-body">

        @if(! $token)
          {{-- No token --}}
          <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:var(--radius);padding:1rem;margin-bottom:1rem">
            <div style="font-weight:700;color:#991b1b;margin-bottom:.4rem">
              <i class="bi bi-x-circle-fill me-1"></i> TELEGRAM_BOT_TOKEN not set
            </div>
            <p style="font-size:.83rem;color:#b91c1c;margin:0">
              Add your bot token to <code>.env</code>:<br>
              <code style="font-family:var(--font-latin)">TELEGRAM_BOT_TOKEN=your_token_here</code><br>
              Then run <code style="font-family:var(--font-latin)">php artisan config:clear</code>
            </p>
          </div>
        @elseif($botError)
          {{-- Token invalid --}}
          <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:var(--radius);padding:1rem;margin-bottom:1rem">
            <div style="font-weight:700;color:#991b1b;margin-bottom:.4rem">
              <i class="bi bi-x-circle-fill me-1"></i> Bot API Error
            </div>
            <p style="font-size:.83rem;color:#b91c1c;margin:0;font-family:var(--font-latin)">{{ $botError }}</p>
          </div>
        @else
          {{-- Bot info --}}
          <div style="display:flex;align-items:center;gap:1rem;padding:1rem;background:var(--surface-2);border-radius:var(--radius);margin-bottom:1.25rem">
            <div style="width:60px;height:60px;border-radius:18px;background:linear-gradient(135deg,#6366f1,#8b5cf6,#ec4899);background-size:200% 200%;animation:gradient 5s ease infinite;display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;box-shadow:0 10px 25px -5px rgba(139,92,246,0.5)">
              🤖
              <style>
                @keyframes gradient {
                  0% {background-position: 0% 50%;}
                  50% {background-position: 100% 50%;}
                  100% {background-position: 0% 50%;}
                }
              </style>
            </div>
            <div>
              <div style="font-weight:700;font-size:1rem">{{ $botInfo['first_name'] }}</div>
              <div style="font-family:var(--font-latin);font-size:.82rem;color:var(--text-muted)">
                @{{ $botInfo['username'] }}
              </div>
              <div style="font-family:var(--font-latin);font-size:.72rem;color:var(--text-muted);margin-top:.15rem">
                ID: {{ $botInfo['id'] }}
              </div>
            </div>
          </div>
        @endif

        {{-- Webhook status --}}
        @if($webhookInfo !== null)
          <div style="font-size:.82rem;font-weight:600;color:var(--text-secondary);margin-bottom:.6rem">
            Webhook Status
          </div>
          <div style="background:var(--surface-2);border-radius:var(--radius);padding:.85rem 1rem;font-family:var(--font-latin);font-size:.8rem">
            @if($webhookInfo['url'])
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-circle-fill" style="color:var(--success);font-size:.5rem"></i>
                <span style="font-weight:600;color:var(--success)">Active</span>
              </div>
              <div style="color:var(--text-muted);word-break:break-all;margin-bottom:.4rem">
                {{ $webhookInfo['url'] }}
              </div>
              @if($webhookInfo['last_error_message'] ?? '')
                <div style="color:var(--danger);margin-top:.5rem">
                  <i class="bi bi-exclamation-triangle me-1"></i>
                  Last error: {{ $webhookInfo['last_error_message'] }}
                  ({{ isset($webhookInfo['last_error_date']) ? date('d/m H:i', $webhookInfo['last_error_date']) : '' }})
                </div>
              @endif
              <div style="color:var(--text-muted);margin-top:.3rem">
                Pending updates: {{ $webhookInfo['pending_update_count'] ?? 0 }}
              </div>
            @else
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-circle-fill" style="color:var(--text-muted);font-size:.5rem"></i>
                <span style="color:var(--text-muted)">No webhook set — polling mode</span>
              </div>
            @endif
          </div>
        @endif

      </div>
    </div>
  </div>

  {{-- RIGHT: Connection methods --}}
  <div class="col-lg-7">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-plug-fill"></i></div>
          <span>ភ្ជាប់ Bot ទៅក្រុម</span>
        </div>
      </div>
      <div class="panel-body d-flex flex-column gap-4">

        {{-- STEP 1 --}}
        <div>
          <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
            <div class="step-badge" style="background:linear-gradient(135deg, #3b82f6, #2563eb)">1</div>
            <span class="step-title">Add Bot to Group</span>
          </div>
          <div style="background:var(--surface-2);border-radius:var(--radius);padding:.85rem 1rem;font-size:.83rem;color:var(--text-secondary);border-left:3px solid var(--primary)">
            ក្នុង Telegram: បើក Group Settings → Add Members → ស្វែងរក
            <strong style="font-family:var(--font-latin);color:var(--primary)">
              @{{ $botInfo['username'] ?? 'your_bot' }}
            </strong>
            → Add
          </div>
        </div>

        <div class="divider" style="margin:0"></div>

        {{-- STEP 2A: Webhook (production) --}}
        <div>
          <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
            <div class="step-badge" style="background:linear-gradient(135deg, #10b981, #059669)">2A</div>
            <span class="step-title">Webhook <span class="badge badge-done" style="font-size:.75rem">Production</span></span>
          </div>
          <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">
            ⚠️ Webhook ត្រូវការ <strong>HTTPS URL</strong> — ដូច្នេះ <code>http://localhost</code> <strong>មិនដំណើរការ</strong>។
            ត្រូវការ domain ជាមួយ SSL (ឧ. ngrok, production server)។
            <br><span style="color:#059669">💡 សម្រាប់ local server: ប្រើ <strong>Polling</strong> (2B) ខាងក្រោម</span>
          </p>
          <form action="{{ route('telegram.set-webhook') }}" method="POST" class="d-flex gap-2 flex-wrap">
            @csrf
            <input type="url" name="webhook_url"
                   class="form-control"
                   placeholder="https://your-domain.com  or  https://xxxx.ngrok.io"
                   value="{{ $appUrl }}"
                   style="flex:1;min-width:200px;font-family:var(--font-latin);font-size:.83rem"
                   required>
            <button class="btn btn-success" type="submit">
              <i class="bi bi-link-45deg"></i> Set Webhook
            </button>
          </form>

          {{-- Secret token status --}}
          @php $hasSecret = (bool) config('services.telegram.webhook_secret'); @endphp
          <div style="margin-top:.6rem;font-size:.75rem;color:var(--text-muted)">
            @if($hasSecret)
              <i class="bi bi-shield-fill-check" style="color:var(--success)"></i>
              Secret token active — webhook is secured
            @else
              <i class="bi bi-shield-exclamation" style="color:var(--warning)"></i>
              No secret token — add <code>TELEGRAM_WEBHOOK_SECRET=any_random_string</code> to .env for security
            @endif
          </div>

          {{-- Auto-topic tip --}}
          <div style="margin-top:.5rem;font-size:.75rem;color:var(--text-muted)">
            <i class="bi bi-info-circle"></i>
            Forum/Topic groups: send a message in each topic after setting webhook — topics auto-register below.
          </div>

          @if($webhookInfo && $webhookInfo['url'])
            <form action="{{ route('telegram.delete-webhook') }}" method="POST" class="mt-2">
              @csrf
              <button class="btn btn-outline-secondary btn-sm" type="submit"
                      onclick="return confirm('Remove webhook and switch to polling mode?')">
                <i class="bi bi-x-circle"></i> Remove Webhook
              </button>
            </form>
          @endif
        </div>

        <div class="divider" style="margin:0"></div>

        {{-- STEP 2B: Poll (local dev) --}}
        <div>
          <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
            <div class="step-badge" style="background:linear-gradient(135deg, #f59e0b, #d97706)">2B</div>
            <span class="step-title">
              Polling
              <span class="badge badge-done" style="font-size:.75rem">✅ ល្អបំផុតសម្រាប់ Local Server</span>
            </span>
          </div>

          <div style="background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);
                      padding:.85rem 1rem;font-size:.83rem;color:#14532d;margin-bottom:.75rem">
            <strong>💡 ណែនាំ:</strong> ដោយសារ App នេះដំណើរការលើ localhost ដូច្នេះ Polling ជាជម្រើសល្អជាង Webhook។
            Bot នឹងទទួល update ដោយស្វ័យប្រវត្តិ <strong>រៀងរាល់ 5 វិនាទី</strong>
            ហើយ Topic Group ក៏ register ដោយស្វ័យប្រវត្តិ ពេល Bot ទទួល message។
          </div>

          <div class="d-flex gap-2 flex-wrap align-items-center">
            {{-- Poll once --}}
            <form action="{{ route('telegram.poll') }}" method="POST">
              @csrf
              <button class="btn btn-warning" type="submit">
                <i class="bi bi-arrow-repeat"></i> Poll ម្ដង
              </button>
            </form>

            {{-- Start background watcher --}}
            <div style="background:var(--surface-2);border-radius:var(--radius);padding:.45rem .9rem;font-family:var(--font-latin);font-size:.78rem;color:var(--text-muted)">
              <i class="bi bi-terminal me-1"></i>
              php artisan telegram:poll --watch
            </div>
          </div>

          <div style="margin-top:.75rem;padding:.7rem .9rem;background:#fffbeb;border:1px solid #fde68a;
                      border-radius:var(--radius);font-size:.78rem;color:#78350f">
            <strong>Auto-start:</strong> Polling ត្រូវបានបន្ថែមទៅ <code>autostart-silent.vbs</code> រួចហើយ —
            វានឹង Start ដោយស្វ័យប្រវត្តិ រៀងរាល់ពេល Server boot។<br>
            ឬ run ដោយដៃ:
            <code style="font-family:var(--font-latin)">php artisan telegram:poll --watch --interval=5</code>
          </div>

          {{-- How to register topics via polling --}}
          <div style="margin-top:.75rem;padding:.7rem .9rem;background:#eff6ff;border:1px solid #bfdbfe;
                      border-radius:var(--radius);font-size:.78rem;color:#1e40af">
            <strong>📋 ចុះឈ្មោះ Topics ដោយស្វ័យប្រវត្តិ:</strong><br>
            1. Start polling (run command ខាងលើ)<br>
            2. ចូល Telegram → ទៅក្នុង <strong>Topic</strong> ណាមួយ (ឧ. Paper Stock)<br>
            3. ផ្ញើ message ចេញ (ឧ. "test") → Topic នឹង register ខាងក្រោម ភ្លាមៗ<br>
            4. ធ្វើបែបនេះ សម្រាប់ Topics ទាំងអស់ដែលចង់ប្រើ
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════
     MANUAL ADD GROUP
════════════════════════════════════════════ --}}
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-plus-circle-fill"></i></div>
      <span>បន្ថែមក្រុមដោយ Chat ID</span>
    </div>
    <button class="btn btn-ghost btn-icon" type="button"
            data-bs-toggle="collapse" data-bs-target="#addGroupPanel">
      <i class="bi bi-chevron-down" id="addGroupChevron" style="transition:transform .2s"></i>
    </button>
  </div>
  <div class="collapse" id="addGroupPanel">
    <div class="panel-body">
      <div class="row g-3 align-items-end">
        <div class="col-12">
          <div class="alert-info-soft" style="margin-bottom:1rem">
            <i class="bi bi-info-circle-fill" style="flex-shrink:0;font-size:1rem"></i>
            <div style="font-size:.82rem">
              ដើម្បីរកឃើញ Chat ID: បន្ថែម
              <strong>@userinfobot</strong> ក្នុងក្រុម ហើយ forward message ទៅ bot — 
              វានឹងបង្ហាញ <code style="font-family:var(--font-latin)">chat_id</code> ។
              ឬ Add <strong>@Book_Printed_bot</strong> ហើយ Poll ។
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <form action="{{ route('telegram.add-group') }}" method="POST" id="addGroupForm">
            @csrf
            <label class="form-label">Chat ID *</label>
            <input type="text" name="chat_id" class="form-control"
                   placeholder="-100xxxxxxxxxx"
                   style="font-family:var(--font-latin)" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">ឈ្មោះក្រុម (backup)</label>
            <input type="text" name="group_name" class="form-control"
                   placeholder="ឈ្មោះក្រុម" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">
              Topic ID
              <span style="font-size:.7rem;color:var(--text-muted)">(ស្រេចចិត្ត)</span>
            </label>
            <input type="number" name="message_thread_id" class="form-control"
                   placeholder="ឧ. 123"
                   style="font-family:var(--font-latin)">
        </div>
        <div class="col-md-3">
            <label class="form-label">
              ឈ្មោះ Topic
              <span style="font-size:.7rem;color:var(--text-muted)">(ស្រេចចិត្ត)</span>
            </label>
            <input type="text" name="topic_name" class="form-control"
                   placeholder="ឧ. Paper Stock">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit" form="addGroupForm">
              <i class="bi bi-plus-lg"></i> បន្ថែម
            </button>
            <span style="font-size:.75rem;color:var(--text-muted);margin-left:.75rem">
              💡 ដើម្បីរក Topic ID: ចូល Topic → Reply → ចុច reply info → ចំនួនលេខ #xxx គឺ thread ID
            </span>
        </div>
          </form>
      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════
     LOW-STOCK ALERT CONFIGURATION
════════════════════════════════════════════ --}}
@php
  $alertChatId  = $alertConfig['chat_id'];
  $alertThreadId= $alertConfig['thread_id'];
  $alertHours   = $alertConfig['cooldown'];
  $alertGroup   = $alertChatId
    ? $groupedChats->get($alertChatId)?->first()
    : null;
@endphp
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#fee2e2;color:#dc2626"><i class="bi bi-bell-fill"></i></div>
      <span>Low-Stock Alert</span>
      @if($alertChatId)
        <span class="badge badge-done" style="font-size:.65rem">✅ Configured</span>
      @else
        <span class="badge badge-pending" style="font-size:.65rem">⚠️ Not configured</span>
      @endif
    </div>
  </div>
  <div class="panel-body">

    @if($alertChatId)
      <div style="background:#dcfce7;border:1px solid #86efac;border-radius:12px;
                  padding:.85rem 1.25rem;font-size:.9rem;color:#14532d;margin-bottom:1rem;
                  display:flex;align-items:center;gap:0.75rem;">
        <i class="bi bi-check-circle-fill" style="font-size:1.2rem"></i>
        <div>
          Alert ត្រូវបានកំណត់ → ផ្ញើទៅ:
          <strong>{{ $alertGroup?->name ?? $alertChatId }}</strong>
          @if($alertThreadId) › Thread #{{ $alertThreadId }} @endif
          <span style="opacity:0.8;margin-left:0.5rem">· Cooldown: {{ $alertHours }}h</span>
        </div>
      </div>
    @endif

    <div style="font-size:.88rem;color:var(--text-secondary);margin-bottom:1.5rem;line-height:1.6">
      ជ្រើសរើសក្រុម/Topic គោលដៅ ដើម្បីទទួលសារប្រកាសអាសន្នពេល Stock ទាប ឬអស់។ 
      ប្រព័ន្ធនឹងផ្ញើសារជូនដំណឹងតែ <strong>១ ក្រុម/Topic</strong> ប៉ុណ្ណោះ ហើយនឹងរង់ចាំ <strong>{{ $alertHours }} ម៉ោង (Cooldown)</strong> មុននឹងផ្ញើម្ដងទៀត ដើម្បីការពារការរំខាន (Spam)។
    </div>

    <form action="{{ route('telegram.alert-config') }}" method="POST" style="background:var(--surface-2);border-radius:16px;padding:1.5rem;border:1px solid var(--border);">
      @csrf
      <div class="row g-3 align-items-end">
        <div class="col-md-6">
          <label class="form-label" style="font-family:var(--font-khmer);font-weight:700;color:var(--text-primary)">
            <i class="bi bi-send-fill me-1" style="color:var(--primary)"></i> ក្រុម/Topic គោលដៅ (Alert Target)
          </label>
          <select name="alert_target" class="form-select" style="font-family:var(--font-khmer);font-size:0.95rem;cursor:pointer;">
            <option value="">— មិនកំណត់ (Disabled) —</option>
            @foreach($groupedChats as $cId => $chatGrps)
              <optgroup label="{{ $chatGrps->first()->name }}">
                @foreach($chatGrps as $g)
                  @php 
                    $val = $g->chat_id . '|' . ($g->message_thread_id ?? '');
                    $isSelected = ($alertChatId == $g->chat_id && $alertThreadId == $g->message_thread_id);
                    $label = $g->name ?? $g->chat_id;
                    if ($g->message_thread_id) $label .= ' › ' . ($g->topic_name ?: 'Topic #'.$g->message_thread_id);
                  @endphp
                  <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" style="font-family:var(--font-khmer);font-weight:700;color:var(--text-primary)">
            <i class="bi bi-stopwatch me-1" style="color:#d97706"></i> Cooldown (ម៉ោង)
          </label>
          <div class="input-group" style="box-shadow:0 0 0 1px #e2e8f0;border-radius:12px;overflow:hidden;">
            <input type="number" name="alert_cooldown" class="form-control border-0" value="{{ $alertHours }}" min="1" max="168" required style="font-family:var(--font-khmer);font-size:0.95rem;font-weight:600;">
            <span class="input-group-text bg-light border-0" style="font-family:var(--font-khmer);font-weight:600;color:var(--text-muted)">ម៉ោង</span>
          </div>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100" style="height:42px;display:flex;align-items:center;justify-content:center;gap:0.5rem;">
            <i class="bi bi-save2"></i> រក្សាទុក
          </button>
        </div>
      </div>
    </form>

  </div>
</div>

{{-- ════════════════════════════════════════════
     DAILY STOCK USAGE REPORT CONFIGURATION
════════════════════════════════════════════ --}}
@php
  $usageChatId  = $dailyUsageConfig['chat_id'];
  $usageThreadId= $dailyUsageConfig['thread_id'];
  $usageGroup   = $usageChatId
    ? $groupedChats->get($usageChatId)?->first()
    : null;
@endphp
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#e0f2fe;color:#0369a1"><i class="bi bi-graph-down-arrow"></i></div>
      <span>Daily Stock Usage Target</span>
      @if($usageChatId)
        <span class="badge badge-done" style="font-size:.65rem">✅ Configured</span>
      @else
        <span class="badge badge-pending" style="font-size:.65rem">⚠️ Not configured</span>
      @endif
    </div>
  </div>
  <div class="panel-body">

    @if($usageChatId)
      <div style="background:#dcfce7;border:1px solid #86efac;border-radius:12px;
                  padding:.85rem 1.25rem;font-size:.9rem;color:#14532d;margin-bottom:1rem;
                  display:flex;align-items:center;gap:0.75rem;">
        <i class="bi bi-check-circle-fill" style="font-size:1.2rem"></i>
        <div>
          Daily Stock Usage ត្រូវបានកំណត់ → ផ្ញើទៅ:
          <strong>{{ $usageGroup?->name ?? $usageChatId }}</strong>
          @if($usageThreadId) › Thread #{{ $usageThreadId }} @endif
        </div>
      </div>
    @endif

    <div style="font-size:.88rem;color:var(--text-secondary);margin-bottom:1.5rem;line-height:1.6">
      ជ្រើសរើសក្រុម/Topic គោលដៅ ដើម្បីទទួលរបាយការណ៍បូកសរុបការប្រើប្រាស់ Stock ប្រចាំថ្ងៃ (ផ្ញើដោយស្វ័យប្រវត្តិរៀងរាល់ព្រឹក) និង ទទួលបានការជូនដំណឹងភ្លាមៗរាល់ពេលដែលមានការបញ្ចេញ Stock (Stock Out)។
    </div>

    <form action="{{ route('telegram.daily-usage-config') }}" method="POST" style="background:var(--surface-2);border-radius:16px;padding:1.5rem;border:1px solid var(--border);">
      @csrf
      <div class="row g-3 align-items-end">
        <div class="col-md-10">
          <label class="form-label" style="font-family:var(--font-khmer);font-weight:700;color:var(--text-primary)">
            <i class="bi bi-send-fill me-1" style="color:var(--primary)"></i> ក្រុម/Topic គោលដៅ (Usage Target)
          </label>
          <select name="daily_usage_target" class="form-select" style="font-family:var(--font-khmer);font-size:0.95rem;cursor:pointer;">
            <option value="">— មិនកំណត់ (Disabled) —</option>
            @foreach($groupedChats as $cId => $chatGrps)
              <optgroup label="{{ $chatGrps->first()->name }}">
                @foreach($chatGrps as $g)
                  @php 
                    $val = $g->chat_id . '|' . ($g->message_thread_id ?? '');
                    $isSelected = ($usageChatId == $g->chat_id && $usageThreadId == $g->message_thread_id);
                    $label = $g->name ?? $g->chat_id;
                    if ($g->message_thread_id) $label .= ' › ' . ($g->topic_name ?: 'Topic #'.$g->message_thread_id);
                  @endphp
                  <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100" style="height:42px;display:flex;align-items:center;justify-content:center;gap:0.5rem;">
            <i class="bi bi-save2"></i> រក្សាទុក
          </button>
        </div>
      </div>
    </form>

  </div>
</div>

{{-- ════════════════════════════════════════════
     LOW-STOCK ALERT CAPTION TEMPLATE (editable)
════════════════════════════════════════════ --}}
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#fef9c3;color:#ca8a04"><i class="bi bi-pencil-square"></i></div>
      <span>Template ការជូនដំណឹង Stock ទាប</span>
    </div>
    <button class="btn btn-ghost btn-icon" type="button"
            data-bs-toggle="collapse" data-bs-target="#alertTplPanel">
      <i class="bi bi-chevron-down" id="alertTplChevron" style="transition:transform .2s"></i>
    </button>
  </div>
  <div class="collapse show" id="alertTplPanel">
    <div class="panel-body">
      <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.7">
        កែសម្រួលសារ Telegram ដែលផ្ញើពេល Stock ទាប។ ប្រើ placeholder ខាងក្រោម —
        ពួកវានឹងត្រូវជំនួសដោយតម្លៃពិត។
      </p>

      <div class="row g-3">
        <div class="col-lg-7">
          <form action="{{ route('telegram.alert-template') }}" method="POST">
            @csrf
            <label class="form-label" style="font-weight:600;font-size:.82rem">Template</label>
            <textarea name="alert_template" id="alertTemplateInput" class="form-control"
                      rows="9" style="font-family:var(--font-latin);font-size:.85rem;line-height:1.6"
                      required>{{ $alertTemplate }}</textarea>
            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-save2"></i> រក្សាទុក Template
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="previewAlertTpl()">
                <i class="bi bi-eye"></i> មើលជាមុន
              </button>
            </div>
          </form>
          <div class="d-flex flex-wrap gap-2 mt-2">
            <form action="{{ route('telegram.alert-test') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-info btn-sm text-white" style="background:#0ea5e9;border:none;">
                <i class="bi bi-send-fill"></i> សាកល្បងផ្ញើសារ (Send Test Alert)
              </button>
            </form>
            <form action="{{ route('telegram.alert-template-reset') }}" method="POST"
                  onsubmit="return confirm('កំណត់ Template ត្រឡប់ទៅលំនាំដើម?')">
              @csrf
              <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-arrow-counterclockwise"></i> លំនាំដើម (Reset)
              </button>
            </form>
          </div>
        </div>

        <div class="col-lg-5">
          <label class="form-label" style="font-weight:600;font-size:.82rem">Placeholders</label>
          <div style="background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:.75rem;font-size:.74rem;line-height:1.9">
            <code>{status_emoji}</code> — ⚠️ ឬ 🔴<br>
            <code>{status}</code> — Stock ទាប / Stock អស់<br>
            <code>{name}</code> — ឈ្មោះ (English)<br>
            <code>{name_km}</code> — ឈ្មោះ (ខ្មែរ)<br>
            <code>{category}</code> — ប្រភេទ<br>
            <code>{sub_type}</code> — ប្រភេទរង<br>
            <code>{stock}</code> — ចំនួន Stock<br>
            <code>{unit}</code> — ឯកតា<br>
            <code>{min}</code> — Min Stock<br>
            <code>{date}</code> — 24/06/2026<br>
            <code>{datetime}</code> — 24/06/2026 14:30
          </div>
          <label class="form-label mt-3" style="font-weight:600;font-size:.82rem">មើលជាមុន (Preview)</label>
          <pre id="alertTplPreview" style="background:#0f172a;color:#e2e8f0;border-radius:8px;
               padding:.85rem;font-size:.78rem;white-space:pre-wrap;word-break:break-word;min-height:120px;margin:0"></pre>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════
     REGISTERED GROUPS & TOPICS
════════════════════════════════════════════ --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-people-fill"></i></div>
      <span>ក្រុម & Topic Telegram</span>
      <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ $groups->count() }}</span>
    </div>
  </div>

  @if($groups->isEmpty())
    <div class="panel-body">
      <div class="empty-state" style="padding:2.5rem 1rem">
        <div class="empty-icon"><i class="bi bi-people"></i></div>
        <p style="font-weight:600;margin:0">មិនទាន់មានក្រុម</p>
        <p class="text-sm text-muted" style="margin:0;text-align:center">
          Add the bot to a group, then Poll or set a Webhook to register groups automatically.
        </p>
      </div>
    </div>
  @else

    {{-- How to find Thread ID guide --}}
    <div class="panel-body" style="background:#fffbeb;border-bottom:1px solid var(--border);padding:.85rem 1.25rem">
      <div style="font-weight:700;font-size:.82rem;margin-bottom:.5rem;color:#92400e">
        <i class="bi bi-info-circle-fill me-1"></i> របៀបរក Topic Thread ID
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:1rem">
        <div style="font-size:.78rem;color:#78350f;flex:1;min-width:200px">
          <strong>Method 1 — Telegram Web:</strong><br>
          បើក web.telegram.org → ចូលក្រុម → ចុចលើ Topic ណាមួយ<br>
          → URL មើលទៅបែបនេះ: <code style="font-family:monospace;font-size:.72rem;background:#fef3c7;padding:.1em .3em;border-radius:3px">t.me/c/1234567890/<strong>42</strong></code><br>
          → លេខ <strong>42</strong> គឺ Thread ID
        </div>
        <div style="font-size:.78rem;color:#78350f;flex:1;min-width:200px">
          <strong>Method 2 — Forward message:</strong><br>
          ចូល Topic → Forward message ចេញ → ចូក <strong>@JsonDumpBot</strong><br>
          → រក <code style="font-family:monospace;font-size:.72rem;background:#fef3c7;padding:.1em .3em;border-radius:3px">"message_thread_id"</code>
        </div>
        <div style="font-size:.78rem;color:#78350f;flex:1;min-width:200px">
          <strong>Method 3 — Auto webhook:</strong><br>
          Set webhook → ចូលក្រុម → ផ្ញើ message ក្នុង Topic នីមួយៗ<br>
          → Topics នឹង appear ខាងក្រោម ដោយស្វ័យប្រវត្តិ
        </div>
      </div>
    </div>

    {{-- Groups grouped by chat_id --}}
    @foreach($groupedChats as $chatId => $chatGroups)
      @php
        $baseGroup = $chatGroups->firstWhere('message_thread_id', null) ?? $chatGroups->first();
        $topics    = $chatGroups->whereNotNull('message_thread_id')->sortBy('topic_name');
        $isForum   = (bool) $baseGroup->is_forum;
      @endphp

      <div style="border-bottom:2px solid var(--border)">

        {{-- Group header row --}}
        <div style="display:flex;align-items:center;gap:1rem;padding:.85rem 1.25rem;
                    background:var(--surface-2);flex-wrap:wrap">
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
              <i class="bi bi-telegram" style="color:var(--primary);font-size:1.1rem"></i>
              <span style="font-weight:700;font-size:.95rem">{{ $baseGroup->name }}</span>
              @if($isForum)
                <span class="badge badge-binding" style="font-size:.65rem">
                  <i class="bi bi-chat-square-dots-fill"></i> Forum / Topics
                </span>
              @endif
              <span class="badge {{ $baseGroup->type==='supergroup'?'badge-staple':'badge-done' }}"
                    style="font-family:var(--font-latin);font-size:.65rem">{{ $baseGroup->type }}</span>
            </div>
            <div style="font-family:var(--font-latin);font-size:.75rem;color:var(--text-muted);margin-top:.2rem">
              Chat ID: {{ $chatId }}
            </div>
          </div>

          <div class="d-flex gap-2">
            {{-- Purpose assignment --}}
            <form action="{{ route('telegram.update-purpose', $baseGroup) }}" method="POST" style="display:flex;align-items:center;gap:.3rem">
              @csrf
              <select name="purpose" class="form-select form-select-sm"
                      style="width:auto;font-size:.72rem;padding:.2rem .5rem;border-radius:6px"
                      onchange="this.form.submit()">
                <option value="" {{ !$baseGroup->purpose ? 'selected' : '' }}>— No purpose —</option>
                <option value="paper_stock" {{ $baseGroup->purpose==='paper_stock' ? 'selected' : '' }}>📄 Paper Stock</option>
                <option value="press_report" {{ $baseGroup->purpose==='press_report' ? 'selected' : '' }}>🖨️ Press Report</option>
                <option value="finishing_report" {{ $baseGroup->purpose==='finishing_report' ? 'selected' : '' }}>🎞️ Finishing</option>
                <option value="consumable_stock" {{ $baseGroup->purpose==='consumable_stock' ? 'selected' : '' }}>🧴 Consumable</option>
                <option value="procurement" {{ $baseGroup->purpose==='procurement' ? 'selected' : '' }}>📋 Procurement</option>
                <option value="production_briefing" {{ $baseGroup->purpose==='production_briefing' ? 'selected' : '' }}>🌅 Prod Briefing</option>
                <option value="stock_usage" {{ $baseGroup->purpose==='stock_usage' ? 'selected' : '' }}>📉 Stock Usage</option>
                <option value="general" {{ $baseGroup->purpose==='general' ? 'selected' : '' }}>📢 General</option>
              </select>
            </form>
            {{-- Test to General / no topic --}}
            <form action="{{ route('telegram.test-group', $baseGroup) }}" method="POST">
              @csrf
              <button class="btn btn-outline-primary btn-sm" type="submit" title="Test General">
                <i class="bi bi-send"></i> Test
              </button>
            </form>
            {{-- Add topic button --}}
            @if($isForum)
              <button class="btn btn-success btn-sm"
                      onclick="showAddTopic('{{ $chatId }}', '{{ addslashes($baseGroup->name) }}')"
                      title="Add topic">
                <i class="bi bi-plus-lg"></i> Add Topic
              </button>
            @endif
            {{-- Remove whole group --}}
            <form action="{{ route('telegram.remove-group', $baseGroup) }}" method="POST">
              @csrf @method('DELETE')
              <button class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none"
                      onclick="return confirm('Remove {{ addslashes($baseGroup->name) }} and all its topics?')"
                      title="Remove">
                <i class="bi bi-trash3"></i>
              </button>
            </form>
          </div>
        </div>

        {{-- Topics list --}}
        @if($isForum)
          @if($topics->isEmpty())
            <div style="padding:.6rem 1.5rem 1rem;font-size:.8rem;color:var(--text-muted)">
              <i class="bi bi-arrow-return-right me-1"></i>
              No topics registered yet —
              <button class="btn btn-sm btn-outline-secondary"
                      onclick="showAddTopic('{{ $chatId }}', '{{ addslashes($baseGroup->name) }}')"
                      style="font-size:.75rem;padding:.2rem .6rem;margin-left:.3rem">
                <i class="bi bi-plus-lg"></i> Add Topic
              </button>
              or send a message in each topic to auto-register via webhook.
            </div>
          @else
            <div style="padding:.4rem 0">
              @foreach($topics as $topic)
                <div style="display:flex;align-items:center;gap:.75rem;padding:.55rem 1.5rem;
                            border-top:1px solid var(--surface-2);flex-wrap:wrap"
                     id="topic-row-{{ $topic->id }}">
                  <i class="bi bi-arrow-return-right" style="color:var(--text-muted);font-size:.85rem;flex-shrink:0"></i>

                  {{-- Topic color dot (random from ID) --}}
                  @php $colors = ['#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#6366f1','#a855f7','#ec4899']; $col = $colors[$topic->id % count($colors)]; @endphp
                  <div style="width:10px;height:10px;border-radius:50%;background:{{ $col }};flex-shrink:0"></div>

                  <div style="flex:1;min-width:120px">
                    <div style="font-weight:700;font-size:.88rem">
                      {{ $topic->topic_name ?: 'Topic #'.$topic->message_thread_id }}
                    </div>
                    <div style="font-family:var(--font-latin);font-size:.7rem;color:var(--text-muted)">
                      Thread ID: {{ $topic->message_thread_id }}
                    </div>
                  </div>

                  <div class="d-flex gap-2">
                    {{-- Purpose --}}
                    <form action="{{ route('telegram.update-purpose', $topic) }}" method="POST" style="display:flex;align-items:center">
                      @csrf
                      <select name="purpose" class="form-select form-select-sm"
                              style="width:auto;font-size:.7rem;padding:.15rem .4rem;border-radius:5px"
                              onchange="this.form.submit()">
                        <option value="" {{ !$topic->purpose ? 'selected' : '' }}>—</option>
                        <option value="paper_stock" {{ $topic->purpose==='paper_stock' ? 'selected' : '' }}>📄 Paper</option>
                        <option value="press_report" {{ $topic->purpose==='press_report' ? 'selected' : '' }}>🖨️ Press</option>
                        <option value="finishing_report" {{ $topic->purpose==='finishing_report' ? 'selected' : '' }}>🎞️ Finishing</option>
                        <option value="consumable_stock" {{ $topic->purpose==='consumable_stock' ? 'selected' : '' }}>🧴 Consumable</option>
                        <option value="procurement" {{ $topic->purpose==='procurement' ? 'selected' : '' }}>📋 Procurement</option>
                        <option value="production_briefing" {{ $topic->purpose==='production_briefing' ? 'selected' : '' }}>🌅 Prod Briefing</option>
                        <option value="stock_usage" {{ $topic->purpose==='stock_usage' ? 'selected' : '' }}>📉 Stock Usage</option>
                        <option value="general" {{ $topic->purpose==='general' ? 'selected' : '' }}>📢 General</option>
                      </select>
                    </form>
                    {{-- Test topic --}}
                    <form action="{{ route('telegram.test-group', $topic) }}" method="POST">
                      @csrf
                      <button class="btn btn-outline-primary btn-sm" title="Test this topic" style="font-size:.75rem;padding:.25rem .65rem">
                        <i class="bi bi-send"></i> Test
                      </button>
                    </form>
                    {{-- Remove topic --}}
                    <form action="{{ route('telegram.remove-group', $topic) }}" method="POST">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;font-size:.75rem;padding:.25rem .55rem"
                              onclick="return confirm('Remove topic {{ addslashes($topic->topic_name ?? 'Topic #'.$topic->message_thread_id) }}?')">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        @endif

      </div>
    @endforeach

  @endif
</div>

{{-- ── Add Topic Modal ────────────────────────────────── --}}
<div id="addTopicModal" style="display:none;position:fixed;inset:0;z-index:9999;
     background:rgba(0,0,0,.5);align-items:center;justify-content:center;padding:1rem">
  <div style="background:var(--surface);border-radius:var(--radius-lg);padding:1.5rem;
              max-width:480px;width:100%;box-shadow:0 24px 64px rgba(0,0,0,.25)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
      <h3 style="font-size:1rem;font-weight:700;margin:0">
        <i class="bi bi-chat-square-dots-fill me-1" style="color:var(--primary)"></i>
        បន្ថែម Topic
      </h3>
      <button onclick="hideAddTopic()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-muted)">✕</button>
    </div>

    <form action="{{ route('telegram.add-group') }}" method="POST">
      @csrf
      <input type="hidden" name="chat_id" id="modalChatId">
      <input type="hidden" name="group_name" id="modalGroupName">

      <div class="mb-3">
        <label class="form-label">ក្រុម</label>
        <div id="modalGroupDisplay" style="padding:.55rem .85rem;background:var(--surface-2);
             border-radius:var(--radius);font-size:.88rem;border:1px solid var(--border)"></div>
      </div>

      <div class="mb-3">
        <label class="form-label">Topic Name *</label>
        <input type="text" name="topic_name" class="form-control" required
               placeholder="ឧ. Paper Stock, Press Report, Book Stock..."
               autofocus>
      </div>

      <div class="mb-4">
        <label class="form-label">Thread ID * <span style="font-size:.72rem;color:var(--text-muted)">(message_thread_id)</span></label>
        <input type="number" name="message_thread_id" class="form-control" required
               placeholder="ឧ. 42, 156, 203..."
               style="font-family:var(--font-latin);font-weight:700;font-size:1.1rem">
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem">
          💡 Telegram Web → ចូល Topic → URL: t.me/c/xxxxxxx/<strong style="color:var(--primary)">42</strong> → លេខចុងក្រោយ = Thread ID
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-1">
          <i class="bi bi-plus-lg"></i> បន្ថែម Topic
        </button>
        <button type="button" onclick="hideAddTopic()" class="btn btn-outline-secondary">
          បោះបង់
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
// Chevron animation
const agPanel   = document.getElementById('addGroupPanel');
const agChevron = document.getElementById('addGroupChevron');
if (agPanel) {
  agPanel.addEventListener('show.bs.collapse', () => agChevron.style.transform = 'rotate(180deg)');
  agPanel.addEventListener('hide.bs.collapse', () => agChevron.style.transform = 'rotate(0deg)');
}

// Add Topic modal
function showAddTopic(chatId, groupName) {
  document.getElementById('modalChatId').value      = chatId;
  document.getElementById('modalGroupName').value   = groupName;
  document.getElementById('modalGroupDisplay').textContent = groupName;
  const modal = document.getElementById('addTopicModal');
  modal.style.display = 'flex';
  modal.querySelector('input[name=topic_name]').focus();
}

function hideAddTopic() {
  document.getElementById('addTopicModal').style.display = 'none';
}

// Close on backdrop click
document.getElementById('addTopicModal').addEventListener('click', function(e) {
  if (e.target === this) hideAddTopic();
});

// ── Low-stock alert template preview ──────────────────────────
const alertTplChevron = document.getElementById('alertTplChevron');
const alertTplPanel   = document.getElementById('alertTplPanel');
if (alertTplPanel && alertTplChevron) {
  alertTplChevron.style.transform = 'rotate(180deg)';
  alertTplPanel.addEventListener('show.bs.collapse', () => alertTplChevron.style.transform = 'rotate(180deg)');
  alertTplPanel.addEventListener('hide.bs.collapse', () => alertTplChevron.style.transform = 'rotate(0deg)');
}

function previewAlertTpl() {
  const tpl = document.getElementById('alertTemplateInput').value;
  const now = new Date();
  const dd = String(now.getDate()).padStart(2,'0');
  const mm = String(now.getMonth()+1).padStart(2,'0');
  const yyyy = now.getFullYear();
  const hh = String(now.getHours()).padStart(2,'0');
  const mi = String(now.getMinutes()).padStart(2,'0');

  // Sample material values
  const sample = {
    '{status_emoji}': '⚠️',
    '{status}': 'Stock ទាប',
    '{name}': 'Plate Cleaner',
    '{name_km}': 'សាបូជូតផ្លាក',
    '{category}': 'Cleaning',
    '{sub_type}': 'Cleaning',
    '{stock}': '3',
    '{unit}': 'bottle',
    '{min}': '5',
    '{date}': `${dd}/${mm}/${yyyy}`,
    '{datetime}': `${dd}/${mm}/${yyyy} ${hh}:${mi}`,
  };
  let out = tpl;
  for (const [k,v] of Object.entries(sample)) out = out.split(k).join(v);
  out = out.replace(/\(\s*\)/g, '').replace(/[ \t]+\n/g, '\n').replace(/[ \t]{2,}/g,' ').trim();
  document.getElementById('alertTplPreview').textContent = out;
}

// Auto-preview on load + as you type
document.addEventListener('DOMContentLoaded', () => {
  const inp = document.getElementById('alertTemplateInput');
  if (inp) { previewAlertTpl(); inp.addEventListener('input', previewAlertTpl); }
});
</script>
@endpush
