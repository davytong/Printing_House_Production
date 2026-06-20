@extends('layouts.app')
@section('title','Quick Entry — Procurement')
@section('page-title','Quick Procurement Entry')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="section-title">Quick Entry</h1>
    <p class="section-sub">Add multiple procurement requests at once — spreadsheet style</p>
  </div>
  <a href="{{ route('procurement.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form action="{{ route('procurement.quick-store') }}" method="POST" id="quickForm">
@csrf

{{-- Shared info (applies to all rows) --}}
<div class="panel mb-4">
  <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;padding:1rem 1.5rem">
    <div style="min-width:140px">
      <label class="form-label">Date *</label>
      <input type="date" name="request_date" class="form-control form-control-sm"
             value="{{ now()->format('Y-m-d') }}" required style="font-family:var(--font-latin)">
    </div>
    <div style="min-width:160px;flex:1">
      <label class="form-label">Requester *</label>
      <input type="text" name="requester" class="form-control form-control-sm"
             value="{{ old('requester') }}" placeholder="Your name" required>
    </div>
    <div style="min-width:160px;flex:1">
      <label class="form-label">Department</label>
      <input type="text" name="department" class="form-control form-control-sm"
             value="{{ old('department') }}" placeholder="e.g. Production">
    </div>
    <button type="button" class="btn btn-success btn-sm" onclick="addRow()" style="height:36px">
      <i class="bi bi-plus-lg"></i> Add Row
    </button>
  </div>
</div>

{{-- Table rows --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-list-columns"></i></div>
      <span>Items</span>
      <span class="badge badge-binding" style="font-family:var(--font-latin)" id="rowCount">1</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addRow()">
      <i class="bi bi-plus-lg"></i> Add Row
    </button>
  </div>
  <div class="tbl-wrap" style="overflow-x:auto">
    <table class="data-table" id="entryTable" style="min-width:900px">
      <thead>
        <tr>
          <th style="width:30px">#</th>
          <th style="min-width:140px">Supplier *</th>
          <th style="min-width:110px">Category *</th>
          <th style="min-width:160px">Item Name *</th>
          <th style="width:80px">Qty *</th>
          <th style="width:80px">Unit</th>
          <th style="width:90px">Price ($)</th>
          <th style="width:90px">Priority</th>
          <th style="min-width:120px">Remarks</th>
          <th style="width:40px"></th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <!-- Rows added by JS -->
      </tbody>
    </table>
  </div>
  <div class="panel-body" style="border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <div style="font-size:.82rem;color:var(--text-muted)">
      <i class="bi bi-info-circle"></i> Press <strong>Tab</strong> to move between fields. Click "+ Add Row" or press <strong>Enter</strong> on last field to add more.
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('procurement.index') }}" class="btn btn-outline-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary btn-lg">
        <i class="bi bi-check-all"></i> Save All (<span id="submitCount">1</span> items)
      </button>
    </div>
  </div>
</div>

</form>
@endsection

@push('scripts')
<script>
let rowIdx = 0;
const categories = {!! json_encode(['consumable'=>'Consumable','spare_part'=>'Spare Part','component'=>'Component','service'=>'Service','equipment'=>'Equipment','other'=>'Other']) !!};
const units = {!! json_encode(['pcs'=>'Pcs','pack'=>'Pack','roll'=>'Roll','can'=>'Can','bottle'=>'Bottle','box'=>'Box','kg'=>'Kg','liter'=>'Liter','sheet'=>'Sheet','set'=>'Set']) !!};
const priorities = {!! json_encode(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent']) !!};
const suppliers = {!! json_encode($suppliers) !!};

function addRow() {
  const tbody = document.getElementById('tableBody');
  const i = rowIdx++;
  const tr = document.createElement('tr');
  tr.id = `row-${i}`;
  tr.innerHTML = `
    <td style="font-family:var(--font-latin);font-size:.78rem;color:var(--text-muted);text-align:center">${i+1}</td>
    <td><input type="text" name="rows[${i}][supplier_name]" class="form-control form-control-sm" list="supplierList" placeholder="Store/Vendor" required></td>
    <td>
      <select name="rows[${i}][category]" class="form-select form-select-sm" required>
        ${Object.entries(categories).map(([v,l]) => `<option value="${v}">${l}</option>`).join('')}
      </select>
    </td>
    <td><input type="text" name="rows[${i}][item_name]" class="form-control form-control-sm" placeholder="Item name" required></td>
    <td><input type="number" name="rows[${i}][quantity]" class="form-control form-control-sm" min="0.01" step="1" value="1" style="font-family:var(--font-latin)" required></td>
    <td>
      <select name="rows[${i}][unit]" class="form-select form-select-sm">
        ${Object.entries(units).map(([v,l]) => `<option value="${v}">${l}</option>`).join('')}
      </select>
    </td>
    <td><input type="number" name="rows[${i}][unit_price]" class="form-control form-control-sm" min="0" step="0.01" style="font-family:var(--font-latin)" placeholder="0.00"></td>
    <td>
      <select name="rows[${i}][priority]" class="form-select form-select-sm">
        ${Object.entries(priorities).map(([v,l]) => `<option value="${v}" ${v==='medium'?'selected':''}>${l}</option>`).join('')}
      </select>
    </td>
    <td><input type="text" name="rows[${i}][remarks]" class="form-control form-control-sm" placeholder="Notes..."></td>
    <td>
      <button type="button" class="btn btn-ghost btn-sm" style="color:var(--danger);padding:0;width:28px;height:28px" onclick="removeRow(${i})" title="Remove">
        <i class="bi bi-x-lg"></i>
      </button>
    </td>
  `;
  tbody.appendChild(tr);
  updateCount();
  // Focus first input of new row
  tr.querySelector('input')?.focus();
}

function removeRow(i) {
  document.getElementById(`row-${i}`)?.remove();
  updateCount();
}

function updateCount() {
  const count = document.querySelectorAll('#tableBody tr').length;
  document.getElementById('rowCount').textContent = count;
  document.getElementById('submitCount').textContent = count;
}

// Add first row on load
addRow();

// Handle Enter on last input → add new row
document.getElementById('tableBody').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    const inputs = this.querySelectorAll('input, select');
    const last = inputs[inputs.length - 1];
    if (e.target === last || e.target.closest('tr') === this.lastElementChild) {
      addRow();
    }
  }
});

// Supplier datalist
const datalist = document.createElement('datalist');
datalist.id = 'supplierList';
suppliers.forEach(s => {
  const opt = document.createElement('option');
  opt.value = s;
  datalist.appendChild(opt);
});
document.body.appendChild(datalist);
</script>
@endpush
