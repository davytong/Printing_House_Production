@extends('layouts.app')
@section('title','Executive Dashboard')
@section('page-title','Executive Dashboard')

@section('content')

{{-- ── KPI Row ── --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="kpi-card kpi-blue">
      <div class="kpi-icon"><i class="bi bi-printer"></i></div>
      <div>
        <div class="kpi-value">{{ $overallPct }}<span style="font-size:1rem">%</span></div>
        <div class="kpi-label">ការបោះពុម្ពរួម</div>
        <div class="kpi-sub">{{ number_format($totalPrinted) }} / {{ number_format($totalTarget) }}</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card kpi-green">
      <div class="kpi-icon"><i class="bi bi-check2-circle"></i></div>
      <div>
        <div class="kpi-value">{{ $doneCount }}</div>
        <div class="kpi-label">សៀវភៅរួចរាល់</div>
        <div class="kpi-sub">{{ $inProgress }} កំពុងបោះ</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card kpi-amber">
      <div class="kpi-icon"><i class="bi bi-boxes"></i></div>
      <div>
        <div class="kpi-value">{{ $lowStockItems }}</div>
        <div class="kpi-label">Stock ទាប</div>
        <div class="kpi-sub">{{ $pendingPOs }} PO រង់ចាំ</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="kpi-card {{ $breakdowns > 0 ? 'kpi-rose' : 'kpi-purple' }}">
      <div class="kpi-icon"><i class="bi bi-gear"></i></div>
      <div>
        <div class="kpi-value">{{ $operationalMachines }}<span style="font-size:1rem">/{{ $totalMachines }}</span></div>
        <div class="kpi-label">ម៉ាស៊ីន Operational</div>
        <div class="kpi-sub">
          {{ $breakdowns > 0 ? $breakdowns.' Breakdown · ' : '' }}{{ $maintenanceDue }} ថែទាំជិតដល់
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── Row 2: Chart + Alerts ── --}}
<div class="row g-4 mb-4">

  {{-- Production trend chart --}}
  <div class="col-lg-7">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-graph-up"></i></div>
          <span>ចំនួនបោះពុម្ព — ៧ ថ្ងៃចុងក្រោយ</span>
        </div>
      </div>
      <div class="panel-body">
        <canvas id="trendChart" height="130"></canvas>
      </div>
    </div>
  </div>

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
          <div style="padding:1.25rem;text-align:center;color:var(--text-muted);font-size:.83rem">
            មិនមានស្នើរសុំថ្មី
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
        <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.83rem">
          <i class="bi bi-check-circle me-1" style="color:var(--success)"></i> គ្មានការថែទាំ
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
        <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.83rem">
          <i class="bi bi-check-circle me-1" style="color:var(--success)"></i> គ្មានការជូនដំណឹង
        </div>
      @endforelse
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
  type: 'bar',
  data: {
    labels: @json($trendLabels),
    datasets: [{
      label: 'ចំនួនបោះពុម្ព',
      data: @json($trendValues),
      backgroundColor: 'rgba(79,70,229,.2)',
      borderColor: '#4f46e5',
      borderWidth: 2,
      borderRadius: 6,
      hoverBackgroundColor: 'rgba(79,70,229,.4)',
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ' ' + ctx.raw.toLocaleString() + ' ក្បាល'
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { font: { family: 'Poppins', size: 11 } } },
      y: { beginAtZero: true, ticks: { font: { family: 'Poppins', size: 11 } } }
    }
  }
});
</script>
@endpush
