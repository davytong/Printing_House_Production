@extends('layouts.app')
@section('title',      'Telegram Bot Setup')
@section('page-title', 'Telegram Bot')

@section('content')

{{-- ════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════ --}}
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
            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#6366f1);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0">
              🤖
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
          <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem">
            <div style="width:24px;height:24px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;font-family:var(--font-latin);flex-shrink:0">1</div>
            <span style="font-weight:600;font-size:.9rem">Add Bot to Group</span>
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
          <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem">
            <div style="width:24px;height:24px;border-radius:50%;background:#059669;color:#fff;display:flex;align-items:center;justify-content:circle;font-size:.72rem;font-weight:700;font-family:var(--font-latin);flex-shrink:0;align-items:center;justify-content:center">2A</div>
            <span style="font-weight:600;font-size:.9rem">Webhook <span class="badge badge-done" style="font-size:.68rem">Production</span></span>
          </div>
          <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">
            ប្រើ HTTPS URL (ngrok, production server) ។ Telegram នឹងផ្ញើ update ដោយស្វ័យប្រវត្តិ។
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
          <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.75rem">
            <div style="width:24px;height:24px;border-radius:50%;background:#d97706;color:#fff;display:flex;align-items:center;justify-content:circle;font-size:.72rem;font-weight:700;font-family:var(--font-latin);flex-shrink:0;align-items:center;justify-content:center">2B</div>
            <span style="font-weight:600;font-size:.9rem">Poll <span class="badge badge-progress" style="font-size:.68rem">Local Dev</span></span>
          </div>
          <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">
            ប្រើសម្រាប់ local dev ។ ចុច Poll ម្ដងៗ ឬ run command ដើម្បីទទួល update ។<br>
            <span style="font-family:var(--font-latin)">⚠️ Requires: no active webhook.</span>
          </p>
          <div class="d-flex gap-2 flex-wrap align-items-center">
            <form action="{{ route('telegram.poll') }}" method="POST">
              @csrf
              <button class="btn btn-warning" type="submit">
                <i class="bi bi-arrow-repeat"></i> Poll Now
              </button>
            </form>
            <div style="background:var(--surface-2);border-radius:var(--radius);padding:.45rem .9rem;font-family:var(--font-latin);font-size:.78rem;color:var(--text-muted)">
              <i class="bi bi-terminal me-1"></i> php artisan telegram:poll
            </div>
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
        <div class="col-md-4">
          <form action="{{ route('telegram.add-group') }}" method="POST" id="addGroupForm">
            @csrf
            <label class="form-label">Chat ID</label>
            <input type="text" name="chat_id" class="form-control"
                   placeholder="-100xxxxxxxxxx"
                   style="font-family:var(--font-latin)" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">ឈ្មោះក្រុម (backup)</label>
            <input type="text" name="group_name" class="form-control"
                   placeholder="ឈ្មោះក្រុម" required>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100" type="submit" form="addGroupForm">
              <i class="bi bi-plus-lg"></i> បន្ថែមក្រុម
            </button>
        </div>
          </form>
      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════
     REGISTERED GROUPS
════════════════════════════════════════════ --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-people-fill"></i></div>
      <span>ក្រុម Telegram ដែលបានចុះឈ្មោះ</span>
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
    <div class="tbl-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>ឈ្មោះក្រុម</th>
            <th>Chat ID</th>
            <th>ប្រភេទ</th>
            <th>ចុះឈ្មោះ</th>
            <th style="text-align:center">សកម្មភាព</th>
          </tr>
        </thead>
        <tbody>
          @foreach($groups as $group)
            <tr>
              <td style="font-weight:600">
                <i class="bi bi-telegram me-1" style="color:var(--primary)"></i>
                {{ $group->name }}
              </td>
              <td style="font-family:var(--font-latin);font-size:.82rem;color:var(--text-muted)">
                {{ $group->chat_id }}
              </td>
              <td>
                <span class="badge {{ $group->type === 'supergroup' ? 'badge-binding' : 'badge-staple' }}"
                      style="font-family:var(--font-latin)">
                  {{ $group->type ?? 'group' }}
                </span>
              </td>
              <td style="font-family:var(--font-latin);font-size:.82rem;color:var(--text-muted)">
                {{ $group->created_at?->format('d/m/Y H:i') ?? '—' }}
              </td>
              <td>
                <div class="d-flex gap-2 justify-content-center">
                  {{-- Test --}}
                  <form action="{{ route('telegram.test-group', $group) }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-primary btn-sm" type="submit"
                            title="Send test message">
                      <i class="bi bi-send"></i> Test
                    </button>
                  </form>
                  {{-- Remove --}}
                  <form action="{{ route('telegram.remove-group', $group) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm" type="submit"
                            style="background:#fee2e2;color:#b91c1c;border:none"
                            onclick="return confirm('Remove {{ addslashes($group->name) }}?')"
                            title="Remove group">
                      <i class="bi bi-trash3"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

@endsection

@push('scripts')
<script>
// Chevron animation for add-group collapse
const agPanel   = document.getElementById('addGroupPanel');
const agChevron = document.getElementById('addGroupChevron');
if (agPanel) {
  agPanel.addEventListener('show.bs.collapse', () => agChevron.style.transform = 'rotate(180deg)');
  agPanel.addEventListener('hide.bs.collapse', () => agChevron.style.transform = 'rotate(0deg)');
}
</script>
@endpush
