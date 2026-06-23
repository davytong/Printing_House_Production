@extends('layouts.app')
@section('title',$material->name)
@section('page-title','Material Detail')

@section('content')
@php $stock = $material->calculated_stock ?? $material->currentStock(); @endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">{{ $material->name }}</h1>
    <p class="section-sub" style="font-family:var(--font-latin)">{{ $material->code }} · {{ $material->categoryLabelShort() }}{{ $material->sub_type ? ' · '.$material->sub_type : '' }}</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('stock.movements.create') }}?material={{ $material->id }}" class="btn btn-success btn-sm"><i class="bi bi-arrow-left-right"></i> Movement</a>
    <a href="{{ route('stock.materials.edit', $material) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
    <a href="{{ route('stock.materials.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-body" style="text-align:center;padding:2rem">
        @php $stockColor = $stock<=0?'var(--danger)':($material->isLowStock()?'var(--warning)':'var(--success)'); @endphp
        <div style="font-family:var(--font-latin);font-size:3rem;font-weight:800;color:{{ $stockColor }}">
          {{ number_format($stock, 1) }}
        </div>
        <div style="font-size:.9rem;color:var(--text-muted)">{{ $material->unit }} in stock</div>
        @if($material->isLowStock())
          <div style="margin-top:.75rem;padding:.4rem .85rem;background:#fffbeb;border:1px solid #fde68a;border-radius:999px;display:inline-block;font-size:.78rem;color:#92400e;font-weight:600">
            ⚠️ Stock ទាប (Min: {{ number_format($material->min_stock, 1) }})
          </div>
        @endif
      </div>
    </div>

    <div class="panel mt-4">
      <div class="panel-header"><div class="ph-title"><div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-info-circle"></i></div><span>ព័ត៌មាន</span></div></div>
      <div class="panel-body d-flex flex-column gap-3">
                @php
          $infoRows = [
            ['ប្រភេទ', $material->categoryLabel(), false],
            ['Sub-Type', $material->sub_type ?? '—', false],
            ['Size', $material->size ?? '—', false],
            ['ឯកតា', $material->unit, false],
            ['Min Stock', number_format($material->min_stock,1).' '.$material->unit, true],
            ['តម្លៃ/Unit', '$'.number_format($material->unit_cost,2), true],
            ['តម្លៃសរុប', '$'.number_format($stock * $material->unit_cost, 2), true],
            ['ទីតាំង', $material->location ?? '—', false],
          ];
        @endphp
        @foreach($infoRows as $row)
          @php [$lbl, $val, $latin] = $row; @endphp
          <div>
            <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">{{ $lbl }}</div>
            <div style="font-size:.88rem;margin-top:.1rem;{{ $latin ? 'font-family:var(--font-latin)' : '' }}">{{ $val }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-clock-history"></i></div><span>ប្រវត្តិ Stock</span></div>
      </div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-km">ថ្ងៃ</span><span class="th-en">Date</span></th>
              <th class="col-center"><span class="th-km">ប្រភេទ</span><span class="th-en">Type</span></th>
              <th class="col-right"><span class="th-km">បរិមាណ</span><span class="th-en">Qty</span></th>
              <th><span class="th-km">Reference</span><span class="th-en">Ref</span></th>
              <th><span class="th-km">ដោយ</span><span class="th-en">By</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($movements as $mv)
              @php
                $typeColor = match($mv->type) {'in'=>'var(--success)','out'=>'var(--danger)',default=>'var(--primary)'};
                $typeSign  = match($mv->type) {'in'=>'+','out'=>'-',default=>'='};
                $typeBg    = match($mv->type) {'in'=>'var(--success-light)','out'=>'var(--danger-light)',default=>'#ede9fe'};
              @endphp
              <tr>
                <td style="font-family:var(--font-latin);font-size:.82rem">{{ $mv->movement_date->format('d/m/Y') }}</td>
                <td style="text-align:center">
                  <span style="font-family:var(--font-latin);font-size:.72rem;font-weight:700;padding:.2em .6em;border-radius:6px;background:{{ $typeBg }};color:{{ $typeColor }}">
                    {{ strtoupper($mv->type) }}
                  </span>
                </td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:700;color:{{ $typeColor }}">
                  {{ $typeSign }}{{ number_format($mv->quantity, 1) }}
                </td>
                <td style="font-size:.82rem;color:var(--text-secondary)">{{ $mv->reference ?? '—' }}</td>
                <td style="font-size:.82rem;color:var(--text-muted)">{{ $mv->performed_by ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" style="text-align:center;padding:1.5rem;color:var(--text-muted)">មិនទាន់មានចលនា Stock</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($movements->hasPages())<div class="panel-body">{{ $movements->links() }}</div>@endif
    </div>
  </div>
</div>
@endsection
