@extends('layouts.app')
@section('title','Executive Dashboard')
@section('page-title','Executive Dashboard')

@section('content')

<style>
/* Premium KPI Cards */
.kpi-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
}
.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--color-1), var(--color-2));
}

.kpi-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
    background: linear-gradient(135deg, var(--color-1), var(--color-2));
    box-shadow: 0 8px 16px -4px rgba(var(--color-rgb), 0.3);
    flex-shrink: 0;
}

.kpi-val {
    font-size: 1.75rem;
    font-weight: 800;
    font-family: var(--font-latin);
    line-height: 1.1;
    color: #0f172a;
    margin-bottom: 0.2rem;
}
.kpi-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-secondary);
}
.kpi-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-family: var(--font-latin);
    margin-top: 0.2rem;
}

/* Premium Animated Progress Bar */
.premium-prog-bg {
    height: 6px;
    background: rgba(0,0,0,0.04);
    border-radius: 999px;
    overflow: hidden;
    margin-top: auto;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
}
.premium-prog-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--color-1), var(--color-2));
    border-radius: 999px;
    position: relative;
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
}
.premium-prog-fill::after {
    content: "";
    position: absolute;
    top: 0; left: 0; bottom: 0; right: 0;
    background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
    animation: shimmer 2s infinite;
}
@keyframes shimmer {
    100% { transform: translateX(100%); }
}

@media (prefers-reduced-motion: reduce) {
    .kpi-card, .tg-widget { transition: none; }
    .kpi-card:hover, .tg-widget:hover { transform: none; }
    .premium-prog-fill::after, .tg-status-dot.active { animation: none; }
}

/* Telegram Widget */
.tg-widget {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 99px;
    padding: 0.4rem 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    text-decoration: none;
    color: #1e293b;
    transition: all 0.2s;
}
.tg-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}
.tg-status-dot {
    width: 8px; height: 8px; border-radius: 50%;
}
.tg-status-dot.active { background: #10b981; box-shadow: 0 0 8px #10b981; animation: pulse 2s infinite; }
.tg-status-dot.pending { background: #f59e0b; }
.tg-status-dot.disconnected { background: #ef4444; }
/* Dark mode overrides for dashboard KPIs */
[data-theme="dark"] .kpi-val { color: var(--text-primary); }
[data-theme="dark"] .premium-prog-bg { background: rgba(255,255,255,0.1); }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h1 class="page-title mb-0" style="font-size:1.75rem">Production Command Center</h1>
            <span class="badge" style="background: rgba(16,185,129,0.15); color: #059669; border: 1px solid rgba(16,185,129,0.3); font-size: 0.75rem; padding: 0.35rem 0.65rem; border-radius: 99px;">
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#10b981; margin-right:4px;"></span> Live System
            </span>
        </div>
        <p class="text-muted mb-0" style="font-size: 0.82rem; margin-top: 2px;">
            Active Batch: <strong>{{ $currentBatch->name }}</strong> · Today: <strong class="text-primary">+{{ number_format($todayOutput) }} units</strong> printed
        </p>
    </div>
    
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('printing.index') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Record Output
        </a>
        <a href="{{ route('printing.report') }}" class="btn btn-outline-primary btn-sm px-3">
            <i class="bi bi-file-earmark-text me-1"></i> Daily Report
        </a>
        @if(\App\Services\RoleService::can('view_audit_logs'))
        <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-shield-check me-1"></i> Audit Trail
        </a>
        @endif
        <a href="{{ route('telegram.setup') }}" class="tg-widget">
            <i class="bi bi-telegram" style="color:#0ea5e9; font-size:1.1rem"></i>
            <span>Bot</span>
            @if($telegramStatus === 'active')
                <div class="tg-status-dot active" title="Active & Configured"></div>
            @elseif($telegramStatus === 'pending')
                <div class="tg-status-dot pending" title="Bot Connected, Missing Alert Group"></div>
            @else
                <div class="tg-status-dot disconnected" title="Disconnected"></div>
            @endif
        </a>
    </div>
</div>

{{-- ── KPI Row ── --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="kpi-card" style="display:flex; flex-direction:column; gap:1.25rem; height: 100%; --color-1:#3b82f6; --color-2:#2563eb; --color-rgb:37,99,235;">
      <div style="display:flex; gap:1rem; align-items:flex-start;">
        <div class="kpi-icon-wrap"><i class="bi bi-printer"></i></div>
        <div>
          <div class="kpi-val">{{ $overallPct }}<span style="font-size:1rem">%</span></div>
          <div class="kpi-title">ការបោះពុម្ពរួម — Overall</div>
          <div class="kpi-desc">{{ number_format($totalPrinted) }} / {{ number_format($totalTarget) }}</div>
        </div>
      </div>
      <div class="premium-prog-bg">
         <div class="premium-prog-fill" style="width: {{ min($overallPct, 100) }}%;"></div>
      </div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="kpi-card" style="display:flex; flex-direction:column; gap:1.25rem; height: 100%; --color-1:#8b5cf6; --color-2:#7c3aed; --color-rgb:124,58,237;">
      <div style="display:flex; gap:1rem; align-items:flex-start;">
        <div class="kpi-icon-wrap"><i class="bi bi-layers"></i></div>
        <div>
          <div class="kpi-val">{{ $currentBatchPct }}<span style="font-size:1rem">%</span></div>
          <div class="kpi-title">{{ $currentBatch->name }} — Batch</div>
          <div class="kpi-desc">{{ number_format($currentBatchPrinted) }} / {{ number_format($currentBatchTotal) }}</div>
        </div>
      </div>
      <div class="premium-prog-bg">
         <div class="premium-prog-fill" style="width: {{ min($currentBatchPct, 100) }}%;"></div>
      </div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="kpi-card" style="display:flex; flex-direction:column; gap:1.25rem; height: 100%; --color-1:#10b981; --color-2:#059669; --color-rgb:5,150,105;">
      <div style="display:flex; gap:1rem; align-items:flex-start;">
        <div class="kpi-icon-wrap"><i class="bi bi-check2-circle"></i></div>
        <div>
          <div class="kpi-val">{{ $doneCount }}</div>
          <div class="kpi-title">សៀវភៅរួចរាល់ — Done</div>
          <div class="kpi-desc">{{ $inProgress }} កំពុងបោះ (In Progress)</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6 col-xl-3">
    <div class="kpi-card" style="display:flex; flex-direction:column; gap:1.25rem; height: 100%; --color-1:#f59e0b; --color-2:#d97706; --color-rgb:217,119,6;">
      <div style="display:flex; gap:1rem; align-items:flex-start;">
        <div class="kpi-icon-wrap"><i class="bi bi-boxes"></i></div>
        <div>
          <div class="kpi-val">{{ $totalLowStockMaterials ?? $lowStockItems }}</div>
          <div class="kpi-title">Stock ទាប — Low Stock</div>
          <div class="kpi-desc" style="font-size:.78rem">
            @if(($criticalCount ?? 0) > 0)
              <span style="color:#ef4444;font-weight:700">🔴 {{ $criticalCount }} Critical</span> ·
            @endif
            <span style="color:#f59e0b;font-weight:700">🟡 {{ $lowCount ?? 0 }} ជិតអស់</span> ·
            <span style="color:#6b7280;font-weight:700">⚫ {{ $outOfStockCount ?? 0 }} អស់</span>
          </div>
        </div>
      </div>
      <a href="{{ route('stock.materials.index') }}?stock=low" class="stretched-link" title="View Low Stock Items"></a>
    </div>
  </div>
</div>

{{-- ── Batch Breakdown Panel (NEW) ── --}}
@if($batchStats->count() > 1)
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-collection"></i></div>
      <span>ការវិភាគតាម Batch — Batch Breakdown</span>
    </div>
    <span class="badge" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color:#fff; font-family:var(--font-latin); padding: 0.4rem 0.8rem; box-shadow: 0 4px 12px var(--primary-glow); border-radius: 8px;">
      {{ $batchStats->count() }} Batches Active
    </span>
  </div>
  <div class="tbl-wrap" style="max-height:none; padding: 0.5rem; background: var(--surface);">
    <table class="data-table">
      <thead>
        <tr>
          <th><span class="th-km">បាច់</span><span class="th-en">BATCH</span></th>
          <th><span class="th-km">ស្ថានភាព</span><span class="th-en">STATUS</span></th>
          <th class="text-end"><span class="th-km">សៀវភៅ</span><span class="th-en">BOOKS</span></th>
          <th class="text-end"><span class="th-km">គោលដៅ</span><span class="th-en">TARGET</span></th>
          <th class="text-end"><span class="th-km">បានបោះពុម្ព</span><span class="th-en">PRINTED</span></th>
          <th><span class="th-km">វឌ្ឍនភាព</span><span class="th-en">PROGRESS</span></th>
          <th><span class="th-km">ថ្ងៃចាប់ផ្តើម</span><span class="th-en">STARTED</span></th>
          <th><span class="th-km">ថ្ងៃបញ្ចប់</span><span class="th-en">COMPLETED</span></th>
        </tr>
      </thead>
      <tbody>
        @foreach($batchStats as $batch)
        <tr class="{{ $batch['status'] === 'active' ? 'row-selected' : '' }}">
          <td>
            <strong style="color:var(--text-primary)">{{ $batch['name'] }}</strong>
          </td>
          <td>
            @if($batch['status'] === 'completed')
              <span class="badge badge-done">✓ Completed</span>
            @elseif($batch['status'] === 'active')
              <span class="badge badge-progress">🔄 In Progress</span>
            @elseif($batch['status'] === 'suspended' || $batch['status'] === 'paused')
              <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fbbf24">⏸ Suspended</span>
            @else
              <span class="badge badge-pending">⏳ Pending</span>
            @endif
          </td>
          <td class="text-end" style="font-family:var(--font-latin);font-weight:600">
            {{ $batch['book_count'] }}
          </td>
          <td class="text-end" style="font-family:var(--font-latin);font-weight:600">
            {{ number_format($batch['target']) }}
          </td>
          <td class="text-end" style="font-family:var(--font-latin);font-weight:600;color:var(--primary)">
            {{ number_format($batch['printed']) }}
          </td>
          <td>
            <div class="prog-cell">
              <div class="prog-track">
                <div class="prog-fill {{ $batch['percentage'] >= 100 ? 'green' : ($batch['percentage'] >= 70 ? '' : 'amber') }}" 
                     style="width:{{ min($batch['percentage'], 100) }}%"></div>
              </div>
              <span class="prog-num" style="{{ $batch['percentage'] > 100 ? 'color:var(--success);font-weight:700' : '' }}">
                {{ $batch['percentage'] }}%
              </span>
            </div>
          </td>
          <td style="font-family:var(--font-latin);font-size:.8rem;color:var(--text-muted)">
            {{ $batch['started_at'] ? $batch['started_at']->format('d M Y') : '-' }}
          </td>
          <td style="font-family:var(--font-latin);font-size:.8rem;color:var(--text-muted)">
            @if($batch['completed_at'])
              <span style="color:var(--success);font-weight:600">{{ $batch['completed_at']->format('d M Y') }}</span>
            @elseif($batch['status'] === 'suspended' || $batch['status'] === 'paused')
              <span style="color:#d97706;font-weight:600">⏸ Suspended</span>
            @else
              <span style="color:var(--text-muted)">-</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- ── Row 2: Chart + Alerts ── --}}
<div class="row g-4 mb-4">

  {{-- Production trend chart --}}
  <div class="col-lg-7">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-graph-up"></i></div>
          <span>ចំនួនបោះពុម្ព — ៧ ថ្ងៃចុងក្រោយ (Trend)</span>
        </div>
      </div>
      <div class="panel-body">
        <canvas id="trendChart" height="200"></canvas>
      </div>
    </div>
  </div>

  {{-- Batch Progress Doughnut chart --}}
  <div class="col-lg-5">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-pie-chart-fill"></i></div>
          <span>វឌ្ឍនភាពបាច់ថ្មី — Current Batch</span>
        </div>
      </div>
      <div class="panel-body d-flex align-items-center justify-content-center position-relative">
        <canvas id="batchChart" height="200" style="max-height: 250px;"></canvas>
        <div style="position: absolute; text-align: center;">
            <div style="font-size: 1.8rem; font-weight: 700; color: var(--primary);">{{ $currentBatchPct }}%</div>
            <div style="font-size: .8rem; color: var(--text-muted); font-family: var(--font-latin);">COMPLETED</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── Row 3: Requests + Inventory alerts ── --}}
<div class="row g-4 mb-4">

  {{-- Right column: Requests + Inventory alerts --}}
  <div class="col-lg-5 d-flex flex-column gap-4">

    {{-- Pending requests --}}
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-file-earmark-plus"></i></div>
          <span>ស្នើរសុំដែលរង់ចាំ</span>
        </div>
        <a href="{{ route('requests.index') }}" class="btn btn-ghost btn-sm" style="font-size:.75rem">
          មើលទាំងអស់ <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      <div style="max-height:200px;overflow-y:auto">
        @forelse($recentRequests as $req)
          @php
            $priColor = match($req->priority) {
              'urgent' => '#ef4444', 'high' => '#f97316',
              'normal' => '#6366f1', default => '#94a3b8'
            };
            $stColor = match($req->status) {
              'pending' => 'badge-progress', 'approved' => 'badge-done',
              'rejected' => 'badge-pending', 'completed' => 'badge-done',
              default => 'badge-pending'
            };
          @endphp
          <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem 1.25rem;border-bottom:1px solid var(--border)">
            <div style="width:8px;height:8px;border-radius:50%;background:{{ $priColor }};flex-shrink:0"></div>
            <div style="flex:1;min-width:0">
              <div style="font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $req->title }}
              </div>
              <div style="font-size:.72rem;color:var(--text-muted)">
                {{ $req->requester_name }} · {{ $req->request_code }}
              </div>
            </div>
            <span class="badge {{ $stColor }}" style="font-size:.68rem">{{ $req->status }}</span>
          </div>
        @empty
          <div style="padding:2rem;text-align:center;background:var(--surface-2);border-radius:12px;margin:1rem;border:1px dashed var(--border)">
            <i class="bi bi-file-earmark-check" style="font-size:1.8rem;color:var(--text-muted);opacity:0.5;display:block;margin-bottom:0.4rem"></i>
            <div style="color:var(--text-muted);font-size:.85rem;font-weight:600">មិនមានស្នើរសុំថ្មី</div>
          </div>
        @endforelse
      </div>
    </div>

    {{-- Low stock alert --}}
    @if($inventoryAlerts->isNotEmpty())
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fee2e2;color:#dc2626"><i class="bi bi-exclamation-triangle"></i></div>
          <span>Stock ទាប</span>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm" style="font-size:.75rem">
          Inventory <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      @foreach($inventoryAlerts as $item)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem 1.25rem;border-bottom:1px solid var(--border)">
          <div>
            <div style="font-size:.82rem;font-weight:600">{{ $item->name }}</div>
            <div style="font-size:.72rem;color:var(--text-muted)">{{ $item->code }}</div>
          </div>
          <div style="text-align:right">
            <div style="font-family:var(--font-latin);font-weight:700;color:var(--danger);font-size:.88rem">
              {{ number_format($item->quantity_in_stock, 1) }}
            </div>
            <div style="font-size:.68rem;color:var(--text-muted)">
              min: {{ number_format($item->minimum_stock, 1) }} {{ $item->unit }}
            </div>
          </div>
        </div>
      @endforeach
    </div>
    @endif

  </div>
</div>

{{-- ── Row 3: Machines + Maintenance + PO ── --}}
<div class="row g-4">

  {{-- Machine status --}}
  <div class="col-md-4">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-gear"></i></div>
          <span>ស្ថានភាពម៉ាស៊ីន</span>
        </div>
        <a href="{{ route('machines.index') }}" class="btn btn-ghost btn-sm" style="font-size:.75rem">
          <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      <div class="panel-body d-flex flex-column gap-3">
        @php
          $mStatuses = [
            ['label'=>'Operational','key'=>'operational','color'=>'#10b981'],
            ['label'=>'Maintenance', 'key'=>'maintenance', 'color'=>'#f59e0b'],
            ['label'=>'Breakdown',  'key'=>'breakdown',  'color'=>'#ef4444'],
            ['label'=>'Idle',       'key'=>'idle',       'color'=>'#94a3b8'],
          ];
        @endphp
        @foreach($mStatuses as $ms)
          @php $cnt = \App\Models\Machine::where('status',$ms['key'])->count(); @endphp
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div style="display:flex;align-items:center;gap:.5rem">
              <span style="width:10px;height:10px;border-radius:50%;background:{{ $ms['color'] }};display:inline-block"></span>
              <span style="font-size:.85rem">{{ $ms['label'] }}</span>
            </div>
            <span style="font-family:var(--font-latin);font-weight:700;color:{{ $ms['color'] }}">{{ $cnt }}</span>
          </div>
        @endforeach
        @if($maintenanceDue > 0)
          <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);padding:.65rem .85rem;font-size:.8rem;color:#92400e;margin-top:.25rem">
            <i class="bi bi-clock-history me-1"></i>
            {{ $maintenanceDue }} ម៉ាស៊ីនត្រូវការថែទាំក្នុងរយៈ ៧ ថ្ងៃ
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Upcoming maintenance --}}
  <div class="col-md-4">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-calendar-check"></i></div>
          <span>ការថែទាំខាងមុខ</span>
        </div>
      </div>
      @forelse($upcomingMaintenance as $m)
        <div style="padding:.65rem 1.25rem;border-bottom:1px solid var(--border)">
          <div style="font-size:.82rem;font-weight:600">{{ $m->machine->name ?? '—' }}</div>
          <div style="display:flex;justify-content:space-between;margin-top:.2rem">
            <span style="font-size:.72rem;color:var(--text-muted)">{{ ucfirst($m->type) }}</span>
            <span style="font-family:var(--font-latin);font-size:.72rem;font-weight:600;
              color:{{ $m->scheduled_date->isPast() ? 'var(--danger)' : 'var(--text-muted)' }}">
              {{ $m->scheduled_date->format('d/m/Y') }}
            </span>
          </div>
        </div>
      @empty
        <div style="padding:2.5rem 1rem;text-align:center;background:var(--surface-2);border-radius:12px;margin:1.5rem;border:1px dashed var(--border)">
          <i class="bi bi-tools" style="font-size:2rem;color:var(--success);opacity:0.4;display:block;margin-bottom:0.5rem"></i>
          <div style="color:var(--text-muted);font-size:.85rem;font-weight:600">គ្មានការថែទាំ</div>
        </div>
      @endforelse
    </div>
  </div>

  {{-- Recent notifications --}}
  <div class="col-md-4">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#f5f3ff;color:#7c3aed"><i class="bi bi-bell"></i></div>
          <span>ការជូនដំណឹង</span>
        </div>
        <a href="{{ route('notifications.mark-all-read') }}" class="btn btn-ghost btn-sm"
           style="font-size:.72rem" onclick="event.preventDefault();document.getElementById('markAllForm').submit()">
          អាននៅសល់
        </a>
        <form id="markAllForm" action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-none">@csrf</form>
      </div>
      @forelse($notifications as $n)
        @php
          $nColor = match($n->type) {
            'danger' => '#ef4444', 'warning' => '#f59e0b',
            'success' => '#10b981', default => '#6366f1'
          };
        @endphp
        <div style="display:flex;gap:.65rem;padding:.65rem 1.25rem;border-bottom:1px solid var(--border);
          {{ !$n->is_read ? 'background:#f8f7ff' : '' }}">
          <div style="width:8px;height:8px;border-radius:50%;background:{{ $nColor }};flex-shrink:0;margin-top:.3rem"></div>
          <div style="min-width:0">
            <div style="font-size:.8rem;font-weight:600">{{ $n->title }}</div>
            <div style="font-size:.72rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ $n->message }}
            </div>
          </div>
        </div>
      @empty
        <div style="padding:2.5rem 1rem;text-align:center;background:var(--surface-2);border-radius:12px;margin:1.5rem;border:1px dashed var(--border)">
          <i class="bi bi-bell-slash" style="font-size:2rem;color:var(--success);opacity:0.4;display:block;margin-bottom:0.5rem"></i>
          <div style="color:var(--text-muted);font-size:.85rem;font-weight:600">គ្មានការជូនដំណឹង</div>
        </div>
      @endforelse
    </div>
  </div>

</div>

{{-- ── Row 4: Top Takers This Week ── --}}
<div class="row g-4 mt-1">
  <div class="col-12">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:linear-gradient(135deg,rgba(79,70,229,.15),rgba(99,102,241,.08));color:#4f46e5">
            <i class="bi bi-person-lines-fill"></i>
          </div>
          <span>Top Stock Takers — <span style="font-family:var(--font-latin);font-size:.8rem;font-weight:600;color:var(--text-muted)">this week</span></span>
        </div>
        <a href="{{ route('stock.person-report') }}" class="btn btn-ghost btn-sm" style="font-size:.78rem">
          Full Report <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      @if($weekTopTakers->isEmpty())
        <div style="padding:2rem 1.5rem;text-align:center;color:var(--text-muted);font-size:.85rem">
          <i class="bi bi-person-dash" style="font-size:1.8rem;display:block;opacity:.3;margin-bottom:.5rem"></i>
          គ្មានអ្នកទាញ Stock សប្តាហ៍នេះ
        </div>
      @else
      @php
        $takerColors = ['#4f46e5','#0ea5e9','#10b981','#f59e0b','#ef4444'];
        $maxMoves = $weekTopTakers->max('move_count') ?: 1;
      @endphp
      <div style="padding:1rem 1.5rem;display:flex;flex-direction:column;gap:.75rem">
        @foreach($weekTopTakers as $ti => $taker)
        @php $tc = $takerColors[$ti % count($takerColors)]; @endphp
        <div style="display:flex;align-items:center;gap:1rem">
          {{-- Rank --}}
          <div style="width:28px;height:28px;border-radius:50%;background:{{ $tc }}22;color:{{ $tc }};display:flex;align-items:center;justify-content:center;font-family:var(--font-latin);font-size:.78rem;font-weight:800;flex-shrink:0">
            {{ $ti + 1 }}
          </div>
          {{-- Name --}}
          <div style="min-width:140px;max-width:200px;font-size:.88rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-primary)">
            {{ $taker->performed_by }}
          </div>
          {{-- Bar --}}
          <div style="flex:1;height:8px;border-radius:999px;background:rgba(0,0,0,.05);overflow:hidden">
            <div style="height:100%;width:{{ round($taker->move_count / $maxMoves * 100) }}%;background:{{ $tc }};border-radius:999px;transition:width .8s cubic-bezier(.34,1.56,.64,1)"></div>
          </div>
          {{-- Stats --}}
          <div style="text-align:right;flex-shrink:0">
            <span style="font-family:var(--font-latin);font-weight:800;font-size:.9rem;color:{{ $tc }}">{{ $taker->move_count }}</span>
            <span style="font-size:.72rem;color:var(--text-muted);font-family:var(--font-latin)"> moves</span>
            @if($taker->out_qty > 0)
              <div style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-latin)">-{{ number_format($taker->out_qty, 1) + 0 }} taken</div>
            @endif
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>
</div>

{{-- ── Row 5: Recent Factory Audit Trail ── --}}
<div class="row g-4 mt-1">
  <div class="col-12">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:linear-gradient(135deg,rgba(79,70,229,.15),rgba(99,102,241,.08));color:#4f46e5">
            <i class="bi bi-activity"></i>
          </div>
          <span>Recent Factory Audit Trail — <span style="font-family:var(--font-latin);font-size:.8rem;font-weight:600;color:var(--text-muted)">សកម្មភាពថ្មីៗក្នុងរោងពុម្ព</span></span>
        </div>
        @if(\App\Services\RoleService::can('view_audit_logs'))
        <a href="{{ route('audit.index') }}" class="btn btn-ghost btn-sm" style="font-size:.78rem">
          Full Audit Trail <i class="bi bi-arrow-right"></i>
        </a>
        @endif
      </div>

      @if($recentActivities->isEmpty())
        <div style="padding:2rem 1.5rem;text-align:center;color:var(--text-muted);font-size:.85rem">
          <i class="bi bi-clock-history" style="font-size:1.8rem;display:block;opacity:.3;margin-bottom:.5rem"></i>
          គ្មានសកម្មភាពថ្មីៗទេ / No recent activity recorded
        </div>
      @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
          <thead class="table-light" style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">
            <tr>
              <th style="padding-left: 1.5rem; width: 170px;">Time</th>
              <th style="width: 180px;">User & Position</th>
              <th style="width: 140px;">Module</th>
              <th>Action & Summary</th>
              <th style="width: 120px; text-align: right; padding-right: 1.5rem;">Network</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentActivities as $act)
            @php $badge = $act->module_badge; @endphp
            <tr>
              <td style="padding-left: 1.5rem; font-family: var(--font-latin); color: var(--text-secondary);">
                <span class="fw-bold text-dark">{{ $act->created_at->format('H:i:s') }}</span>
                <span class="text-muted small">({{ $act->created_at->diffForHumans() }})</span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="width: 26px; height: 26px; border-radius: 50%; background: #4f46e522; color: #4f46e5; display:flex; align-items:center; justify-content:center; font-weight:700; font-size: 0.72rem;">
                    {{ strtoupper(substr($act->user_name, 0, 1)) }}
                  </div>
                  <div>
                    <div class="fw-bold text-dark" style="font-size: 0.82rem;">{{ $act->user_name }}</div>
                    <div class="text-muted" style="font-size: 0.7rem;">{{ ucfirst(str_replace('_', ' ', $act->position)) }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge" style="background: {{ $badge['bg'] }}18; color: {{ $badge['bg'] }}; border: 1px solid {{ $badge['bg'] }}33; font-size: 0.72rem;">
                  <i class="bi {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                </span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $act->action }}</div>
                @if($act->details)
                  <div class="text-muted small text-truncate" style="max-width: 450px;">{{ $act->details }}</div>
                @endif
              </td>
              <td style="text-align: right; padding-right: 1.5rem; font-family: var(--font-latin); font-size: 0.75rem;" class="text-muted">
                {{ $act->ip_address ?: '127.0.0.1' }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
</div>

@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
const ctx = document.getElementById('trendChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: @json($trendLabels),
    datasets: [{
      label: 'ចំនួនបោះពុម្ព',
      data: @json($trendValues),
      borderColor: '#4f46e5',
      backgroundColor: (context) => {
        const chartCtx = context.chart.ctx;
        const gradient = chartCtx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(79,70,229,0.35)');
        gradient.addColorStop(1, 'rgba(79,70,229,0.0)');
        return gradient;
      },
      borderWidth: 3,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#ffffff',
      pointBorderColor: '#4f46e5',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: 'rgba(15, 23, 42, 0.9)',
        titleFont: { family: 'Outfit', size: 13, weight: 'bold' },
        bodyFont: { family: 'Outfit', size: 12 },
        padding: 12,
        cornerRadius: 12,
        displayColors: false,
        callbacks: {
          label: ctx => ' ' + ctx.raw.toLocaleString() + ' ក្បាល'
        }
      }
    },
    scales: {
      x: { 
        grid: { display: false }, 
        border: { display: false },
        ticks: { font: { family: 'Outfit', size: 11 }, color: '#64748b' } 
      },
      y: { 
        beginAtZero: true, 
        grid: { color: 'rgba(0,0,0,0.03)' },
        border: { display: false },
        ticks: { font: { family: 'Outfit', size: 11 }, color: '#64748b', maxTicksLimit: 6 } 
      }
    }
  }
});

const batchCtx = document.getElementById('batchChart').getContext('2d');
new Chart(batchCtx, {
  type: 'doughnut',
  data: {
    labels: ['Printed', 'Remaining'],
    datasets: [{
      data: [{{ $currentBatchPrinted }}, {{ max(0, $currentBatchTotal - $currentBatchPrinted) }}],
      backgroundColor: ['#10b981', '#f1f5f9'],
      borderWidth: 0,
      hoverOffset: 6
    }]
  },
  options: {
    responsive: true,
    cutout: '75%',
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: 'rgba(15, 23, 42, 0.9)',
        titleFont: { family: 'Outfit', size: 13, weight: 'bold' },
        bodyFont: { family: 'Outfit', size: 12 },
        padding: 12,
        cornerRadius: 12,
        displayColors: false,
        callbacks: {
          label: ctx => ' ' + ctx.raw.toLocaleString() + ' ក្បាល'
        }
      }
    }
  }
});
</script>
@endpush
