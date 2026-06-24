@extends('layouts.app')
@section('title', 'Batch History')
@section('page-title', 'Batch History')

@section('content')
@php
  $totalTarget  = $snapshots->sum('target_qty');
  $totalPrinted = $snapshots->sum('printed_qty');
  $pct = $totalTarget > 0 ? floor($totalPrinted / $totalTarget * 100) : 0;
  $doneCount = $snapshots->filter(fn($s) => $s->printed_qty >= $s->target_qty && $s->target_qty > 0)->count();
@endphp

<style>
@media print {
  .sidebar, .topbar, nav, .btn { display:none !important; }
  .page-content { max-width:100% !important; margin:0 !important; }
}
</style>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">
      <i class="bi bi-layers-fill me-2" style="color:var(--primary)"></i>{{ $batch->name }}
    </h1>
    <p class="section-sub">
      Completed
      @if($batch->completed_at) {{ $batch->completed_at->format('d/m/Y H:i') }} @endif
      @if($batch->started_at) · Started {{ $batch->started_at->format('d/m/Y') }} @endif
      @if($batch->notes) · {{ $batch->notes }} @endif
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('printing.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Back
    </a>
    <form action="{{ route('printing.batch-restore', $batch) }}" method="POST" class="m-0"
          onsubmit="return confirm('ប្ដូរទៅ {{ $batch->name }} ហើយធ្វើឱ្យវាសកម្ម? Batch បច្ចុប្បន្ននឹងត្រូវរក្សាទុកជាប្រវត្តិ។')">
      @csrf
      <button type="submit" class="btn btn-success btn-sm">
        <i class="bi bi-box-arrow-in-left"></i> ប្ដូរទៅ Batch នេះ (Make Active)
      </button>
    </form>
    <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-printer"></i> Print / PDF
    </button>
    @if($batch->status !== 'active')
    <form action="{{ route('printing.batch-delete', $batch) }}" method="POST" class="m-0"
          onsubmit="return confirm('⚠️ លុប {{ $batch->name }} ជាអចិន្ត្រៃយ៍? សៀវភៅ និងលទ្ធផលរបស់វានឹងបាត់បង់ទាំងស្រុង។')">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-trash3"></i> លុប Batch
      </button>
    </form>
    @endif
  </div>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-blue">
      <div class="kpi-icon"><i class="bi bi-journals"></i></div>
      <div>
        <div class="kpi-value">{{ $snapshots->count() }}</div>
        <div class="kpi-label">ចំនួនសៀវភៅ</div>
        <div class="kpi-sub">Total Books</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-green">
      <div class="kpi-icon"><i class="bi bi-check2-circle"></i></div>
      <div>
        <div class="kpi-value">{{ number_format($totalPrinted) }}</div>
        <div class="kpi-label">បានបោះពុម្ព</div>
        <div class="kpi-sub">{{ $pct }}% of target</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-amber">
      <div class="kpi-icon"><i class="bi bi-bullseye"></i></div>
      <div>
        <div class="kpi-value">{{ number_format($totalTarget) }}</div>
        <div class="kpi-label">គោលដៅ</div>
        <div class="kpi-sub">Total Target</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-purple">
      <div class="kpi-icon"><i class="bi bi-trophy"></i></div>
      <div>
        <div class="kpi-value">{{ $doneCount }}</div>
        <div class="kpi-label">រួចរាល់</div>
        <div class="kpi-sub">Completed books</div>
      </div>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-table"></i></div>
      <span>លទ្ធផល {{ $batch->name }}</span>
      <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ $snapshots->count() }}</span>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th style="width:44px;text-align:center">#</th>
          <th>ឈ្មោះសៀវភៅ</th>
          <th class="col-center">ថ្នាក់</th>
          <th>ប្រភេទ</th>
          <th class="col-right">គោលដៅ</th>
          <th class="col-right">បោះពុម្ព</th>
          <th style="min-width:150px">ដំណើរការ</th>
          <th class="col-center">ស្ថានភាព</th>
        </tr>
      </thead>
      <tbody>
        @forelse($snapshots as $i => $s)
          @php
            $sp = $s->target_qty > 0 ? floor($s->printed_qty / $s->target_qty * 100) : 0;
            $isDone = $s->printed_qty >= $s->target_qty && $s->target_qty > 0;
            $catLabel = $s->category === 'perfect_binding' ? 'បិតក្បាល' : 'កិបកណ្ដាល';
            $catClass = $s->category === 'perfect_binding' ? 'badge-binding' : 'badge-staple';
            $gradeDisp = $s->grade ? (is_numeric($s->grade) ? 'ថ្នាក់ '.$s->grade : $s->grade) : '—';
            $bc = $isDone ? 'green' : ($sp >= 40 ? '' : 'amber');
          @endphp
          <tr>
            <td style="text-align:center;font-family:var(--font-latin);font-size:.78rem;color:var(--text-muted)">{{ $i+1 }}</td>
            <td><div style="font-weight:700;font-size:.88rem">{{ $s->title }}</div></td>
            <td style="text-align:center"><span class="grade-badge">{{ $gradeDisp }}</span></td>
            <td><span class="badge {{ $catClass }}">{{ $catLabel }}</span></td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:600">{{ number_format($s->target_qty) }}</td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:600;color:var(--success)">{{ number_format($s->printed_qty) }}</td>
            <td>
              <div class="prog-cell">
                <div class="prog-track"><div class="prog-fill {{ $bc }}" style="width:{{ min($sp,100) }}%"></div></div>
                <span class="prog-num">{{ $sp }}%</span>
              </div>
            </td>
            <td style="text-align:center">
              @if($isDone)
                <span class="badge badge-done">រួចរាល់</span>
              @elseif($s->printed_qty > 0)
                <span class="badge badge-progress">កំពុងបោះ</span>
              @else
                <span class="badge badge-pending">មិនទាន់</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8"><div class="empty-state"><p>គ្មានទិន្នន័យ</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
