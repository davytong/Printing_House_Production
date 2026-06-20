@extends('layouts.app')
@section('title','Inventory')
@section('page-title','Inventory Management')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">Inventory Management</h1>
    <p class="section-sub">ក្រដាស ទឹកថ្នាំ សម្ភារៈបន្ថែម និងវត្ថុធាតុផ្សេងៗទៀត</p>
  </div>
  <a href="{{ route('inventory.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> បន្ថែម Item</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-blue" style="padding:1.1rem;gap:.5rem">
      <div class="kpi-icon" style="width:36px;height:36px;font-size:1rem"><i class="bi bi-boxes"></i></div>
      <div><div class="kpi-value" style="font-size:1.5rem">{{ $stats['total'] }}</div><div class="kpi-label" style="font-size:.75rem">Items សរុប</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card {{ $stats['low_stock']>0?'kpi-rose':'kpi-green' }}" style="padding:1.1rem;gap:.5rem">
      <div class="kpi-icon" style="width:36px;height:36px;font-size:1rem"><i class="bi bi-exclamation-triangle"></i></div>
      <div><div class="kpi-value" style="font-size:1.5rem">{{ $stats['low_stock'] }}</div><div class="kpi-label" style="font-size:.75rem">Stock ទាប</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card {{ $stats['out_stock']>0?'kpi-rose':'kpi-amber' }}" style="padding:1.1rem;gap:.5rem">
      <div class="kpi-icon" style="width:36px;height:36px;font-size:1rem"><i class="bi bi-slash-circle"></i></div>
      <div><div class="kpi-value" style="font-size:1.5rem">{{ $stats['out_stock'] }}</div><div class="kpi-label" style="font-size:.75rem">អស់ Stock</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-purple" style="padding:1.1rem;gap:.5rem">
      <div class="kpi-icon" style="width:36px;height:36px;font-size:1rem"><i class="bi bi-currency-dollar"></i></div>
      <div><div class="kpi-value" style="font-size:1.5rem">${{ number_format($stats['total_value'],0) }}</div><div class="kpi-label" style="font-size:.75rem">តម្លៃសរុប</div></div>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-boxes"></i></div>
      <span>បញ្ជី Inventory</span>
    </div>
    <div class="d-flex gap-2">
      <select id="typeFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
        <option value="">ប្រភេទទាំងអស់</option>
        @foreach(['paper'=>'ក្រដាស','ink'=>'ថ្នាំ','plate'=>'Plate','spare_part'=>'Spare Parts','chemical'=>'Chemical','other'=>'ផ្សេងៗ'] as $v=>$l)
          <option value="{{ $v }}">{{ $l }}</option>
        @endforeach
      </select>
      <select id="stockFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
        <option value="">Stock ទាំងអស់</option>
        <option value="low">Stock ទាប</option>
        <option value="ok">ធម្មតា</option>
      </select>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="data-table" id="invTable">
      <thead>
        <tr>
          <th><span class="th-km">លេខ</span><span class="th-en">Code</span></th>
          <th><span class="th-km">ឈ្មោះ</span><span class="th-en">Item Name</span></th>
          <th><span class="th-km">ប្រភេទ</span><span class="th-en">Type</span></th>
          <th class="col-right"><span class="th-km">ក្នុង Stock</span><span class="th-en">In Stock</span></th>
          <th class="col-right"><span class="th-km">ចំនួនអប្បបរមា</span><span class="th-en">Min Stock</span></th>
          <th><span class="th-km">ទីតាំង</span><span class="th-en">Location</span></th>
          <th class="col-right"><span class="th-km">តម្លៃ/Unit</span><span class="th-en">Unit Cost</span></th>
          <th class="col-center"><span class="th-km">ស្ថានភាព</span><span class="th-en">Stock Status</span></th>
          <th class="col-center"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          @php
            $isLow  = $item->isLowStock();
            $isOut  = $item->quantity_in_stock <= 0;
            $typeMap= ['paper'=>'ក្រដាស','ink'=>'ថ្នាំ','plate'=>'Plate','spare_part'=>'Spare','chemical'=>'Chemical','other'=>'ផ្សេងៗ'];
          @endphp
          <tr data-type="{{ $item->type }}" data-stock="{{ $isLow ? 'low' : 'ok' }}">
            <td style="font-family:var(--font-latin);font-size:.8rem;font-weight:600;color:var(--primary)">{{ $item->code }}</td>
            <td>
              <div style="font-weight:600">{{ $item->name }}</div>
              @if($item->supplier)<div style="font-size:.72rem;color:var(--text-muted)">{{ $item->supplier->name }}</div>@endif
            </td>
            <td><span class="badge badge-binding">{{ $typeMap[$item->type] ?? $item->type }}</span></td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:700;
              color:{{ $isOut?'var(--danger)':($isLow?'var(--warning)':'var(--success)') }}">
              {{ number_format($item->quantity_in_stock,1) }} <span style="font-weight:400;font-size:.75rem">{{ $item->unit }}</span>
            </td>
            <td style="text-align:right;font-family:var(--font-latin);font-size:.82rem;color:var(--text-muted)">
              {{ number_format($item->minimum_stock,1) }} {{ $item->unit }}
            </td>
            <td style="font-size:.82rem;color:var(--text-secondary)">{{ $item->location ?? '—' }}</td>
            <td style="text-align:right;font-family:var(--font-latin);font-size:.82rem">${{ number_format($item->unit_cost,2) }}</td>
            <td style="text-align:center">
              @if($isOut)
                <span class="badge badge-pending">អស់</span>
              @elseif($isLow)
                <span class="badge badge-progress">ទាប</span>
              @else
                <span class="badge badge-done">ធម្មតា</span>
              @endif
            </td>
            <td style="text-align:center">
              <a href="{{ route('inventory.show', $item) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
            </td>
          </tr>
        @empty
          <tr><td colspan="9"><div class="empty-state"><div class="empty-icon"><i class="bi bi-boxes"></i></div><p style="font-weight:600;margin:0">មិនទាន់មាន Items</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($items->hasPages())<div class="panel-body">{{ $items->links() }}</div>@endif
</div>
@endsection

@push('scripts')
<script>
const tF = document.getElementById('typeFilter');
const sF = document.getElementById('stockFilter');
function filterRows() {
  const t = tF.value, s = sF.value;
  document.querySelectorAll('#invTable tbody tr[data-type]').forEach(r => {
    r.style.display = ((!t||r.dataset.type===t)&&(!s||r.dataset.stock===s))?'':'none';
  });
}
tF?.addEventListener('change',filterRows);
sF?.addEventListener('change',filterRows);
</script>
@endpush
