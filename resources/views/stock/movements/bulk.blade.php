@extends('layouts.app')
@section('title','Bulk Stock Entry')
@section('page-title','Bulk Movement')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">Bulk Stock Entry</h1>
  <a href="{{ route('stock.movements.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
</div>

<form action="{{ route('stock.movements.bulk-store') }}" method="POST">
@csrf
<div class="panel">
  <div class="panel-header">
    <div class="ph-title"><div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-list-columns"></i></div><span>បញ្ចូលចលនាច្រើនក្នុងពេលតែមួយ</span></div>
  </div>
  <div class="panel-body">
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">ប្រភេទ *</label>
        <select name="type" class="form-select" required>
          <option value="in">📥 Stock IN</option>
          <option value="out">📤 Stock OUT</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">កាលបរិច្ឆេទ *</label>
        <input type="date" name="movement_date" class="form-control" value="{{ now()->format('Y-m-d') }}" style="font-family:var(--font-latin)" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">អ្នកធ្វើ</label>
        <input type="text" name="performed_by" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Reference</label>
        <input type="text" name="reference" class="form-control">
      </div>
    </div>

    <table class="data-table" id="bulkTable">
      <thead>
        <tr>
          <th style="width:30px"><input type="checkbox" id="checkAll"></th>
          <th>Material</th>
          <th>Category</th>
          <th class="col-right">Current Stock</th>
          <th style="width:120px">Qty *</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        @foreach($materials as $i => $m)
          <tr>
            <td><input type="checkbox" class="row-check" data-idx="{{ $i }}"></td>
            <td>
              <div style="font-weight:600">{{ $m->name }}</div>
              @if($m->sub_type)<div style="font-size:.72rem;color:var(--text-muted)">{{ $m->sub_type }}</div>@endif
              <input type="hidden" name="items[{{ $i }}][material_id]" value="{{ $m->id }}" disabled>
            </td>
            <td><span class="badge {{ match($m->category){'paper'=>'badge-binding','film'=>'badge-staple',default=>'badge-progress'} }}">{{ $m->categoryLabelShort() }}</span></td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:600">{{ number_format($m->currentStock(),1) }} {{ $m->unit }}</td>
            <td>
              <input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm"
                     min="0.01" step="0.01" style="font-family:var(--font-latin)" disabled>
            </td>
            <td>
              <input type="text" name="items[{{ $i }}][notes]" class="form-control form-control-sm" disabled>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="panel-body" style="border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.75rem">
    <a href="{{ route('stock.movements.index') }}" class="btn btn-outline-secondary">បោះបង់</a>
    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-all"></i> រក្សាទុកទាំងអស់</button>
  </div>
</div>
</form>
@endsection

@push('scripts')
<script>
// Toggle row inputs on checkbox
document.querySelectorAll('.row-check').forEach(cb => {
  cb.addEventListener('change', () => {
    const row = cb.closest('tr');
    const inputs = row.querySelectorAll('input[name]');
    inputs.forEach(inp => inp.disabled = !cb.checked);
    if (cb.checked) row.querySelector('input[type=number]')?.focus();
  });
});
document.getElementById('checkAll')?.addEventListener('change', e => {
  document.querySelectorAll('.row-check').forEach(cb => {
    cb.checked = e.target.checked;
    cb.dispatchEvent(new Event('change'));
  });
});
</script>
@endpush
