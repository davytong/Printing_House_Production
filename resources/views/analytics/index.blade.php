@extends('layouts.app')
@section('title','Analytics')
@section('page-title','Analytics & Reporting')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">Analytics</h1>
    <p class="section-sub">ការវិភាគទិន្នន័យ — Production, Inventory, Machines</p>
  </div>
  <form method="GET" class="d-flex gap-2 align-items-center">
    <label class="form-label mb-0" style="white-space:nowrap;font-size:.82rem">ចន្លោះ:</label>
    <select name="period" class="form-select form-select-sm" style="width:auto;font-family:var(--font-latin)" onchange="this.form.submit()">
      @foreach(['7'=>'7 Days','14'=>'14 Days','30'=>'30 Days','90'=>'90 Days'] as $v=>$l)
        <option value="{{ $v }}" {{ $period==$v?'selected':'' }}>{{ $l }}</option>
      @endforeach
    </select>
  </form>
</div>

<div class="row g-4 mb-4">
  {{-- Production trend --}}
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-graph-up"></i></div><span>Production Trend ({{ $period }} Days)</span></div>
      </div>
      <div class="panel-body"><canvas id="trendChart" height="100"></canvas></div>
    </div>
  </div>

  {{-- By Category --}}
  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-pie-chart"></i></div><span>ប្រភេទ</span></div>
      </div>
      <div class="panel-body">
        @foreach($byCategory as $bc)
          @php $pct = $bc->target > 0 ? round($bc->printed/$bc->target*100) : 0; @endphp
          <div class="mb-3">
            <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
              <span style="font-size:.85rem;font-weight:600">{{ $bc->category==='perfect_binding'?'បិតក្បាល':'កិបកណ្ដាល' }}</span>
              <span style="font-family:var(--font-latin);font-size:.8rem;font-weight:700;color:var(--primary)">{{ $pct }}%</span>
            </div>
            <div class="prog-track" style="height:8px">
              <div class="prog-fill {{ $pct>=80?'green':'' }}" style="width:{{ $pct }}%"></div>
            </div>
            <div style="font-family:var(--font-latin);font-size:.72rem;color:var(--text-muted);margin-top:.2rem">
              {{ number_format($bc->printed) }} / {{ number_format($bc->target) }} · {{ $bc->books }} books
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  {{-- Monthly production --}}
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-bar-chart"></i></div><span>ផលិតកម្ម 6 ខែ</span></div>
      </div>
      <div class="panel-body"><canvas id="monthlyChart" height="120"></canvas></div>
    </div>
  </div>

  {{-- Top books --}}
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-trophy"></i></div><span>Top 5 Books ({{ $period }}d)</span></div>
      </div>
      <div class="tbl-wrap" style="max-height:280px">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th><span class="th-km">ឈ្មោះ</span><span class="th-en">Title</span></th>
              <th class="col-right"><span class="th-km">ចំនួន</span><span class="th-en">Printed</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($topBooks->take(5) as $i=>$tb)
              <tr>
                <td style="font-family:var(--font-latin);color:var(--text-muted);font-weight:600">{{ $i+1 }}</td>
                <td style="font-weight:600;font-size:.85rem">{{ $tb->book->title ?? '—' }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:700;color:var(--success)">
                  {{ number_format($tb->total_in_period) }}
                </td>
              </tr>
            @empty
              <tr><td colspan="3" style="text-align:center;padding:1.5rem;color:var(--text-muted)">No data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  {{-- By Grade --}}
  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#f0fdf4;color:#15803d"><i class="bi bi-mortarboard"></i></div><span>ថ្នាក់</span></div>
      </div>
      <div class="tbl-wrap" style="max-height:260px">
        <table class="data-table">
          <thead><tr>
            <th><span class="th-km">ថ្នាក់</span><span class="th-en">Grade</span></th>
            <th class="col-right"><span class="th-km">បោះពុម្ព</span><span class="th-en">Printed</span></th>
            <th class="col-right"><span class="th-km">គោលដៅ</span><span class="th-en">Target</span></th>
          </tr></thead>
          <tbody>
            @forelse($byGrade as $bg)
              <tr>
                <td><span class="grade-badge {{ is_numeric($bg->grade)?'grade-num':'grade-primary' }}">{{ is_numeric($bg->grade)?'ថ្នាក់ '.$bg->grade:$bg->grade }}</span></td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:600;color:var(--success)">{{ number_format($bg->printed) }}</td>
                <td style="text-align:right;font-family:var(--font-latin);color:var(--text-muted)">{{ number_format($bg->target) }}</td>
              </tr>
            @empty
              <tr><td colspan="3" style="text-align:center;padding:1rem;color:var(--text-muted)">No grade data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Machine status --}}
  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#dbeafe;color:#1e40af"><i class="bi bi-gear"></i></div><span>ម៉ាស៊ីន</span></div>
      </div>
      <div class="panel-body">
        @foreach(['operational'=>['#10b981','Operational'],'maintenance'=>['#f59e0b','Maintenance'],'breakdown'=>['#ef4444','Breakdown'],'idle'=>['#94a3b8','Idle']] as $k=>[$c,$l])
          <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:.5rem">
              <span style="width:10px;height:10px;border-radius:50%;background:{{ $c }};display:inline-block"></span>
              <span style="font-size:.85rem">{{ $l }}</span>
            </div>
            <span style="font-family:var(--font-latin);font-weight:700;color:{{ $c }}">{{ $machineStats[$k]??0 }}</span>
          </div>
        @endforeach
        <div style="margin-top:.75rem;display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:.82rem;color:var(--text-muted)">Maintenance Cost ({{ $period }}d)</span>
          <span style="font-family:var(--font-latin);font-weight:700;color:var(--primary)">${{ number_format($maintenanceCosts,2) }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Request stats --}}
  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-file-earmark-bar-graph"></i></div><span>ស្នើរសុំ</span></div>
      </div>
      <div class="panel-body">
        @foreach(['pending'=>['#f59e0b','រង់ចាំ'],'approved'=>['#10b981','បានអនុម័ត'],'in_production'=>['#6366f1','ផលិត'],'completed'=>['#059669','រួចរាល់'],'rejected'=>['#ef4444','បដិសេធ']] as $k=>[$c,$l])
          <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border)">
            <span style="font-size:.85rem">{{ $l }}</span>
            <span style="font-family:var(--font-latin);font-weight:700;color:{{ $c }}">{{ $requestStats[$k]??0 }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
const colors = { primary:'#4f46e5', success:'#10b981', warning:'#f59e0b' };

new Chart(document.getElementById('trendChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: @json($labels),
    datasets: [{
      label: 'Printed',
      data: @json($values),
      borderColor: colors.primary,
      backgroundColor: 'rgba(79,70,229,.08)',
      borderWidth: 2,
      tension: 0.3,
      fill: true,
      pointBackgroundColor: colors.primary,
      pointRadius: 4,
    }]
  },
  options: { responsive:true, plugins:{legend:{display:false}},
    scales:{ x:{grid:{display:false},ticks:{font:{family:'Poppins',size:10}}}, y:{beginAtZero:true,ticks:{font:{family:'Poppins',size:10}}} } }
});

const mLabels = @json($monthlyProduction->keys());
const mValues = @json($monthlyProduction->values());
new Chart(document.getElementById('monthlyChart').getContext('2d'), {
  type: 'bar',
  data: {
    labels: mLabels,
    datasets: [{
      label: 'Printed',
      data: mValues,
      backgroundColor: 'rgba(16,185,129,.25)',
      borderColor: colors.success,
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: { responsive:true, plugins:{legend:{display:false}},
    scales:{ x:{grid:{display:false},ticks:{font:{family:'Poppins',size:10}}}, y:{beginAtZero:true,ticks:{font:{family:'Poppins',size:10}}} } }
});
</script>
@endpush
