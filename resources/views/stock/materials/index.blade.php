@extends('layouts.app')
@section('title','វត្ថុធាតុដើម — Stock')
@section('page-title','Stock Management')

@section('content')
<div class="workspace-hero d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">វត្ថុធាតុដើម</h1>
    <p class="section-sub">ក្រដាស · Film · Offset Materials</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route('stock.low-stock.settings') }}" class="btn btn-outline-secondary btn-sm" title="Low Stock Settings">
      <i class="bi bi-gear-fill me-1"></i> កំណត់ Low Stock
    </a>
    <a href="{{ route('stock.low-stock.history') }}" class="btn btn-outline-info btn-sm" title="Notification History">
      <i class="bi bi-clock-history me-1"></i> ប្រវត្តិផ្ញើសារ
    </a>
    <form action="{{ route('stock.materials.alert') }}" method="POST" style="display:inline" data-confirm="តើអ្នកពិតជាចង់ផ្ញើសារប្រកាសស្តុកទាបទៅកាន់ Telegram មែនទេ?">
      @csrf
      <button type="submit" class="btn btn-warning btn-sm">
        <i class="bi bi-megaphone-fill"></i> ផ្ញើសារស្តុកទាប
      </button>
    </form>
    <a href="{{ route('stock.materials.export') }}" class="btn btn-success btn-sm">
      <i class="bi bi-file-earmark-excel"></i> Export
    </a>
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
  @foreach(['paper'=>['<i class="bi bi-file-earmark-text-fill"></i>','ក្រដាស (Paper)','kpi-blue'],'film'=>['<i class="bi bi-film"></i>','Laminate (ស្គុត)','kpi-purple'],'consumable'=>['<i class="bi bi-droplet-fill"></i>','Consumable (សម្ភារៈប្រើប្រាស់)','kpi-green']] as $cat=>[$emoji,$label,$cls])
    @php $s = $summary[$cat] ?? ['total_items'=>0,'low_stock'=>0,'out_of_stock'=>0,'total_value'=>0]; @endphp
    <div class="col-md-4">
      <div class="kpi-card {{ $cls }}" style="padding:1.1rem;gap:.5rem;flex-direction:row;align-items:center">
        <div class="kpi-icon" style="width:42px;height:42px;font-size:1.3rem">{!! $emoji !!}</div>
        <div style="flex:1">
          <div class="kpi-label" style="margin:0">{{ $label }}</div>
          <div style="display:flex;gap:1rem;margin-top:.3rem">
            <span style="font-family:var(--font-latin);font-size:.8rem"><strong>{{ $s['total_items'] }}</strong> items</span>
            @if($s['low_stock'] > 0)
              <span style="font-family:var(--font-latin);font-size:.8rem;color:#fde68a"><i class="bi bi-exclamation-triangle-fill"></i> {{ $s['low_stock'] }} low</span>
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
        <option value="paper">ក្រដាស (Paper)</option>
        <option value="film">Laminate (ស្គុត)</option>
        <option value="consumable">Consumable (សម្ភារៈប្រើប្រាស់)</option>
      </select>
      <select id="stockFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
        <option value="">Stock ទាំងអស់</option>
        <option value="low">Stock ទាប</option>
        <option value="ok">ធម្មតា</option>
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
            $catEmoji = match($m->category) { 'paper'=>'<i class="bi bi-file-earmark-text-fill"></i>','film'=>'<i class="bi bi-film"></i>','offset'=>'<i class="bi bi-printer-fill"></i>','consumable'=>'<i class="bi bi-droplet-fill"></i>',default=>'<i class="bi bi-box-fill"></i>' };
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
                {!! $catEmoji !!} {{ $m->categoryLabelShort() }}
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
              {!! $m->stockStatusBadge($m->calculated_stock) !!}
            </td>
            <td style="text-align:center; white-space:nowrap;">
              <a href="{{ route('stock.materials.show', $m) }}" class="btn btn-ghost btn-sm" title="View">
                <i class="bi bi-eye"></i>
              </a>
              <a href="{{ route('stock.materials.edit', $m) }}" class="btn btn-ghost btn-sm" title="Edit" style="color:var(--primary);">
                <i class="bi bi-pencil-square"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="8">
            <div class="empty-box">
              <i class="bi bi-box-seam"></i>
              <div class="empty-text">មិនទាន់មានវត្ថុធាតុដើម (Materials) ទេ</div>
              <div style="margin-top:1rem">
                <a href="{{ route('stock.materials.create') }}" class="btn btn-primary btn-sm">
                  <i class="bi bi-plus-lg"></i> បន្ថែមថ្មី
                </a>
              </div>
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
