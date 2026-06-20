@extends('layouts.app')
@section('title','វត្ថុធាតុដើម — Stock')
@section('page-title','Stock Management')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">វត្ថុធាតុដើម</h1>
    <p class="section-sub">ក្រដាស · Film · Offset Materials</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('stock.movements.create') }}" class="btn btn-success btn-sm">
      <i class="bi bi-arrow-left-right"></i> Record Movement
    </a>
    <a href="{{ route('stock.materials.create') }}" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg"></i> បន្ថែម Material
    </a>
  </div>
</div>

{{-- Category KPIs --}}
<div class="row g-3 mb-4">
  @foreach(['paper'=>['📄','ក្រដាស (Paper)','kpi-blue'],'film'=>['🎞️','Film (ហ្វីម)','kpi-purple'],'consumable'=>['🧴','Consumable (សម្ភារៈប្រើប្រាស់)','kpi-green']] as $cat=>[$emoji,$label,$cls])
    @php $s = $summary[$cat] ?? ['total_items'=>0,'low_stock'=>0,'out_of_stock'=>0,'total_value'=>0]; @endphp
    <div class="col-md-4">
      <div class="kpi-card {{ $cls }}" style="padding:1.1rem;gap:.5rem;flex-direction:row;align-items:center">
        <div class="kpi-icon" style="width:42px;height:42px;font-size:1.3rem">{{ $emoji }}</div>
        <div style="flex:1">
          <div class="kpi-label" style="margin:0">{{ $label }}</div>
          <div style="display:flex;gap:1rem;margin-top:.3rem">
            <span style="font-family:var(--font-latin);font-size:.8rem"><strong>{{ $s['total_items'] }}</strong> items</span>
            @if($s['low_stock'] > 0)
              <span style="font-family:var(--font-latin);font-size:.8rem;color:#fde68a">⚠️ {{ $s['low_stock'] }} low</span>
            @endif
          </div>
        </div>
        <div style="text-align:right">
          <div style="font-family:var(--font-latin);font-size:1.2rem;font-weight:800">${{ number_format($s['total_value'],0) }}</div>
          <div style="font-size:.68rem;opacity:.8">តម្លៃសរុប</div>
        </div>
      </div>
    </div>
  @endforeach
</div>

{{-- Materials Table --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-box-seam"></i></div>
      <span>បញ្ជីវត្ថុធាតុដើម</span>
      <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ $materials->count() }}</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <select id="catFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
        <option value="">ប្រភេទទាំងអស់</option>
        <option value="paper">📄 ក្រដាស (Paper)</option>
        <option value="film">🎞️ Film (ហ្វីម)</option>
        <option value="consumable">🧴 Consumable (សម្ភារៈប្រើប្រាស់)</option>
      </select>
      <select id="stockFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
        <option value="">Stock ទាំងអស់</option>
        <option value="low">⚠️ Stock ទាប</option>
        <option value="ok">✅ ធម្មតា</option>
      </select>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="data-table" id="materialsTable">
      <thead>
        <tr>
          <th><span class="th-km">លេខ</span><span class="th-en">Code</span></th>
          <th><span class="th-km">ឈ្មោះ</span><span class="th-en">Name</span></th>
          <th><span class="th-km">ប្រភេទ</span><span class="th-en">Category</span></th>
          <th><span class="th-km">Size</span><span class="th-en">Spec</span></th>
          <th class="col-right"><span class="th-km">Stock</span><span class="th-en">Current</span></th>
          <th class="col-right"><span class="th-km">Min</span><span class="th-en">Threshold</span></th>
          <th class="col-center"><span class="th-km">ស្ថានភាព</span><span class="th-en">Status</span></th>
          <th class="col-center"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($materials as $m)
          @php
            $stockColor = $m->calculated_stock <= 0 ? 'var(--danger)' : ($m->is_low ? 'var(--warning)' : 'var(--success)');
            $catEmoji = match($m->category) { 'paper'=>'📄','film'=>'🎞️','offset'=>'🖨️','consumable'=>'🧴',default=>'📦' };
          @endphp
          <tr data-category="{{ $m->category }}" data-stock="{{ $m->is_low ? 'low' : 'ok' }}">
            <td style="font-family:var(--font-latin);font-size:.78rem;font-weight:600;color:var(--primary)">
              {{ $m->code }}
            </td>
            <td>
              <div style="font-weight:700;font-size:.88rem">{{ $m->name }}</div>
              @if($m->name_km)
                <div style="font-size:.78rem;color:var(--text-secondary);font-family:var(--font-khmer)">{{ $m->name_km }}</div>
              @elseif($m->sub_type)
                <div style="font-size:.72rem;color:var(--text-muted)">{{ $m->sub_type }}</div>
              @endif
            </td>
            <td>
              <span class="badge {{ match($m->category) {'paper'=>'badge-binding','film'=>'badge-staple',default=>'badge-progress'} }}">
                {{ $catEmoji }} {{ $m->categoryLabelShort() }}
              </span>
            </td>
            <td style="font-size:.82rem;color:var(--text-secondary)">{{ $m->size ?? '—' }}</td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:700;font-size:.9rem;color:{{ $stockColor }}">
              {{ number_format($m->calculated_stock, 1) }}
              <span style="font-size:.72rem;font-weight:400;color:var(--text-muted)">{{ $m->unit }}</span>
            </td>
            <td style="text-align:right;font-family:var(--font-latin);font-size:.82rem;color:var(--text-muted)">
              {{ number_format($m->min_stock, 1) }}
            </td>
            <td style="text-align:center">
              @if($m->calculated_stock <= 0)
                <span class="badge badge-pending">អស់</span>
              @elseif($m->is_low)
                <span class="badge badge-progress">ទាប</span>
              @else
                <span class="badge badge-done">ធម្មតា</span>
              @endif
            </td>
            <td style="text-align:center">
              <a href="{{ route('stock.materials.show', $m) }}" class="btn btn-ghost btn-sm">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="8">
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-box-seam"></i></div>
              <p style="font-weight:600;margin:0">មិនទាន់មាន Materials</p>
              <a href="{{ route('stock.materials.create') }}" class="btn btn-primary btn-sm mt-2">
                <i class="bi bi-plus-lg"></i> បន្ថែម
              </a>
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
const catF = document.getElementById('catFilter');
const stF  = document.getElementById('stockFilter');
function filterMats() {
  const cat = catF.value, st = stF.value;
  document.querySelectorAll('#materialsTable tbody tr[data-category]').forEach(r => {
    r.style.display = ((!cat || r.dataset.category === cat) && (!st || r.dataset.stock === st)) ? '' : 'none';
  });
}
catF?.addEventListener('change', filterMats);
stF?.addEventListener('change', filterMats);
</script>
@endpush
