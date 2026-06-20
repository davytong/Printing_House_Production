@extends('layouts.app')
@section('title','Stock Movements')
@section('page-title','Stock Movements')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">Stock Movements</h1>
    <p class="section-sub">បញ្ចូល / ដក / កែតម្រូវ Stock</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('stock.movements.bulk') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-list-columns"></i> Bulk Entry</a>
    <a href="{{ route('stock.movements.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Record Movement</a>
  </div>
</div>

{{-- Today summary --}}
@if($todayMovements->isNotEmpty())
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title"><div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-calendar-check"></i></div><span>សង្ខេបថ្ងៃនេះ ({{ now()->format('d/m/Y') }})</span></div>
    <span style="font-family:var(--font-latin);font-size:.78rem;color:var(--primary);font-weight:700">{{ $todayMovements->count() }} movements</span>
  </div>
  <div class="tbl-wrap" style="max-height:200px">
    <table class="data-table">
      <tbody>
        @foreach($todayMovements as $mv)
          @php $tc = match($mv->type){'in'=>'var(--success)','out'=>'var(--danger)',default=>'var(--primary)'}; @endphp
          <tr>
            <td style="font-family:var(--font-latin);font-size:.72rem;font-weight:700;padding:.4rem .8rem;
                       color:{{ $tc }}">{{ strtoupper($mv->type) }}</td>
            <td style="font-weight:600;font-size:.85rem;padding:.4rem .8rem">{{ $mv->material->name }}</td>
            <td style="font-family:var(--font-latin);font-weight:700;text-align:right;padding:.4rem .8rem;color:{{ $tc }}">
              {{ $mv->type==='out'?'-':'+' }}{{ number_format($mv->quantity,1) }} {{ $mv->material->unit }}
            </td>
            <td style="font-size:.78rem;color:var(--text-muted);padding:.4rem .8rem">{{ $mv->reference ?? '' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- All movements --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title"><div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-arrow-left-right"></i></div><span>ប្រវត្តិចលនាទាំងអស់</span></div>
  </div>
  <div class="tbl-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th><span class="th-km">ថ្ងៃ</span><span class="th-en">Date</span></th>
          <th class="col-center"><span class="th-km">ប្រភេទ</span><span class="th-en">Type</span></th>
          <th><span class="th-km">Material</span><span class="th-en">Name</span></th>
          <th class="col-right"><span class="th-km">បរិមាណ</span><span class="th-en">Qty</span></th>
          <th><span class="th-km">Reference</span><span class="th-en">Ref</span></th>
          <th><span class="th-km">ដោយ</span><span class="th-en">By</span></th>
        </tr>
      </thead>
      <tbody>
        @forelse($movements as $mv)
          @php $tc = match($mv->type){'in'=>'var(--success)','out'=>'var(--danger)',default=>'var(--primary)'}; @endphp
          <tr>
            <td style="font-family:var(--font-latin);font-size:.82rem">{{ $mv->movement_date->format('d/m/Y') }}</td>
            <td style="text-align:center">
              <span style="font-family:var(--font-latin);font-size:.72rem;font-weight:700;padding:.2em .55em;border-radius:6px;
                background:{{ match($mv->type){'in'=>'var(--success-light)','out'=>'var(--danger-light)',default=>'#ede9fe'} }};
                color:{{ $tc }}">{{ strtoupper($mv->type) }}</span>
            </td>
            <td>
              <div style="font-weight:600;font-size:.85rem">{{ $mv->material->name ?? '—' }}</div>
              @if($mv->material?->sub_type)<div style="font-size:.7rem;color:var(--text-muted)">{{ $mv->material->sub_type }}</div>@endif
            </td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:700;color:{{ $tc }}">
              {{ $mv->type==='out'?'-':($mv->type==='in'?'+':'=') }}{{ number_format($mv->quantity,1) }}
              <span style="font-weight:400;font-size:.72rem;color:var(--text-muted)">{{ $mv->material->unit ?? '' }}</span>
            </td>
            <td style="font-size:.82rem;color:var(--text-secondary)">{{ $mv->reference ?? '—' }}</td>
            <td style="font-size:.82rem;color:var(--text-muted)">{{ $mv->performed_by ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6"><div class="empty-state"><div class="empty-icon"><i class="bi bi-arrow-left-right"></i></div><p style="font-weight:600;margin:0">មិនទាន់មានចលនា Stock</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($movements->hasPages())<div class="panel-body">{{ $movements->links() }}</div>@endif
</div>
@endsection
