@extends('layouts.app')
@section('title','ម៉ាស៊ីន')
@section('page-title','Machine & Maintenance')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">ម៉ាស៊ីន និងការថែទាំ</h1>
    <p class="section-sub">ត្រួតពិនិត្យ និងកំណត់ការថែទាំ</p>
  </div>
  <a href="{{ route('machines.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> បន្ថែមម៉ាស៊ីន</a>
</div>

<div class="row g-3 mb-4">
  @foreach([
    ['operational','Operational','kpi-green','bi-check-circle'],
    ['maintenance','Maintenance','kpi-amber','bi-tools'],
    ['breakdown','Breakdown','kpi-rose','bi-x-octagon'],
    ['idle','Idle','kpi-purple','bi-pause-circle'],
    ['due_soon','ថែទាំខាងមុខ','kpi-blue','bi-calendar-event'],
  ] as [$k,$l,$cls,$ic])
  <div class="col-6 col-lg">
    <div class="kpi-card {{ $cls }}" style="padding:1rem;gap:.4rem">
      <div class="kpi-icon" style="width:34px;height:34px;font-size:.9rem"><i class="bi {{ $ic }}"></i></div>
      <div><div class="kpi-value" style="font-size:1.5rem">{{ $stats[$k] }}</div><div class="kpi-label" style="font-size:.72rem">{{ $l }}</div></div>
    </div>
  </div>
  @endforeach
</div>

<div class="row g-4">
  @forelse($machines as $m)
    @php
      $stColor = match($m->status){
        'operational'=>'#10b981','maintenance'=>'#f59e0b','breakdown'=>'#ef4444','idle'=>'#94a3b8',default=>'#6366f1'
      };
      $typeMap = ['offset'=>'Offset','digital'=>'Digital','binding'=>'Binding','cutting'=>'Cutting','folding'=>'Folding','other'=>'Other'];
      $daysDue = $m->daysUntilMaintenance();
    @endphp
    <div class="col-md-6 col-xl-4">
      <div class="panel h-100">
        <div style="padding:1.25rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:flex-start">
          <div>
            <div style="font-weight:700;font-size:.95rem">{{ $m->name }}</div>
            <div style="font-size:.75rem;color:var(--text-muted);font-family:var(--font-latin);margin-top:.15rem">
              {{ $m->code }} · {{ $typeMap[$m->type]??$m->type }}
            </div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.3rem">
            <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.25em .65em;border-radius:999px;font-size:.72rem;font-weight:700;font-family:var(--font-latin);background:{{ $stColor }}22;color:{{ $stColor }};border:1px solid {{ $stColor }}44">
              <span style="width:6px;height:6px;border-radius:50%;background:{{ $stColor }};display:inline-block"></span>
              {{ ucfirst($m->status) }}
            </span>
          </div>
        </div>
        <div class="panel-body d-flex flex-column gap-2">
          @if($m->model)<div style="font-size:.8rem;color:var(--text-muted)">{{ $m->manufacturer }} — {{ $m->model }}</div>@endif

          {{-- Maintenance info --}}
          <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:.65rem .85rem">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:.78rem;color:var(--text-secondary)">ថែទាំបន្ទាប់</span>
              <span style="font-family:var(--font-latin);font-size:.8rem;font-weight:700;
                color:{{ $daysDue<0?'var(--danger)':($daysDue<=7?'var(--warning)':'var(--success)') }}">
                {{ $m->next_maintenance?->format('d/m/Y') ?? '—' }}
                @if($m->next_maintenance)
                  ({{ $daysDue<0?abs($daysDue).'d overdue':$daysDue.'d' }})
                @endif
              </span>
            </div>
            @if($m->last_maintenance)
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem">
                ថែទាំចុងក្រោយ: {{ $m->last_maintenance->format('d/m/Y') }}
              </div>
            @endif
          </div>

          <div style="display:flex;justify-content:space-between;margin-top:.25rem">
            <span style="font-size:.78rem;color:var(--text-muted)">Maintenance ចំនួន</span>
            <span style="font-family:var(--font-latin);font-size:.8rem;font-weight:600">
              {{ $m->completed_maintenance_count }}
            </span>
          </div>

          <a href="{{ route('machines.show', $m) }}" class="btn btn-outline-primary btn-sm w-100 mt-1">
            <i class="bi bi-eye"></i> មើលលម្អិត
          </a>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="empty-state"><div class="empty-icon"><i class="bi bi-gear"></i></div><p style="font-weight:600;margin:0">មិនទាន់មានម៉ាស៊ីន</p></div>
    </div>
  @endforelse
</div>
@endsection
