@extends('layouts.app')
@section('title','New Procurement Request')
@section('page-title','New Procurement Request')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">New Procurement Request</h1>
  <a href="{{ route('procurement.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form action="{{ route('procurement.store') }}" method="POST" enctype="multipart/form-data">
@csrf

{{-- HEADER INFO --}}
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-info-circle"></i></div>
      <span>Request Info</span>
    </div>
  </div>
  <div class="panel-body">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Date *</label>
        <input type="date" name="request_date" class="form-control" value="{{ old('request_date', date('Y-m-d')) }}" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Requester *</label>
        <input type="text" name="requester" class="form-control" value="{{ old('requester', session('user_name')) }}" placeholder="Your name" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Department</label>
        <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="e.g. Production">
      </div>
      <div class="col-md-3">
        <label class="form-label">Supplier / Store *</label>
        <input type="text" name="supplier_name" class="form-control" value="{{ old('supplier_name') }}" placeholder="Vendor name" required list="supplierList">
        <datalist id="supplierList">
          @foreach($suppliers as $s)<option value="{{ $s }}">@endforeach
        </datalist>
      </div>
      <div class="col-md-3">
        <label class="form-label">Priority *</label>
        <select name="priority" class="form-select" required>
          <option value="low" {{ old('priority')==='low'?'selected':'' }}>Low</option>
          <option value="medium" {{ old('priority','medium')==='medium'?'selected':'' }}>Medium</option>
          <option value="high" {{ old('priority')==='high'?'selected':'' }}>High</option>
          <option value="urgent" {{ old('priority')==='urgent'?'selected':'' }}>Urgent</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Due Date</label>
        <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="pending" selected>Pending</option>
          <option value="approved">Approved</option>
          <option value="ordered">Ordered</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Remarks</label>
        <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}" placeholder="Notes...">
      </div>
    </div>
  </div>
</div>

{{-- ITEMS TABLE --}}
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-list-check"></i></div>
      <span>Items</span>
      <span class="badge badge-binding" style="font-family:var(--font-latin)" id="itemCount">1</span>
    </div>
    <button type="button" class="btn btn-success btn-sm" onclick="addItem()">
      <i class="bi bi-plus-lg"></i> Add Item
    </button>
  </div>
  <div class="tbl-wrap" style="overflow-x:auto">
    <table class="data-table" id="itemsTable" style="min-width:800px">
      <thead>
        <tr>
          <th style="width:30px">#</th>
          <th style="min-width:150px">Item Name *</th>
          <th style="min-width:110px">Category *</th>
          <th style="width:80px">Qty *</th>
          <th style="width:80px">Unit</th>
          <th style="width:100px">Price ($)</th>
          <th style="width:100px">Total</th>
          <th style="min-width:120px">Description</th>
          <th style="width:40px"></th>
        </tr>
      </thead>
      <tbody id="itemsBody"></tbody>
      <tfoot>
        <tr style="background:var(--surface-2)">
          <td colspan="6" style="text-align:right;font-weight:700;padding:.75rem 1rem">Grand Total:</td>
          <td style="font-family:var(--font-latin);font-weight:800;font-size:1rem;color:var(--primary)" id="grandTotal">$0.00</td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

{{-- ATTACHMENTS --}}
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-paperclip"></i></div>
      <span>Attachments (optional)</span>
    </div>
  </div>
  <div class="panel-body">
    <div id="dropZone" style="border:2px dashed var(--border-dark);border-radius:var(--radius);padding:1.25rem;text-align:center;background:var(--surface-2);cursor:pointer"
         onclick="document.getElementById('fileInput').click()"
         ondragover="event.preventDefault();this.style.borderColor='var(--primary)'"
         ondragleave="this.style.borderColor='var(--border-dark)'"
         ondrop="handleDrop(event)">
      <i class="bi bi-cloud-arrow-up" style="font-size:1.5rem;color:var(--text-muted)"></i>
      <p style="margin:.3rem 0 0;font-size:.82rem;color:var(--text-secondary)">Click or drag files — PDF, Images, Word, Excel (max 20MB each)</p>
    </div>
    <input type="file" name="attachments[]" id="fileInput" multiple class="d-none"
           accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx" onchange="handleFiles(this.files)">
    <div id="previewArea" style="display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.6rem"></div>
  </div>
</div>

{{-- SUBMIT --}}
<div class="d-flex gap-2">
  <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg"></i> Submit Request</button>
  <a href="{{ route('procurement.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
</div>

</form>
@endsection

@push('scripts')
<script>
const categories = {consumable:'Consumable',spare_part:'Spare Part',component:'Component',service:'Service',equipment:'Equipment',other:'Other'};
const units = {pcs:'Pcs',pack:'Pack',roll:'Roll',can:'Can',bottle:'Bottle',box:'Box',kg:'Kg',liter:'Liter',sheet:'Sheet',set:'Set'};
let itemIdx = 0;

function addItem() {
  const i = itemIdx++;
  const tr = document.createElement('tr');
  tr.id = `item-${i}`;
  tr.innerHTML = `
    <td style="font-family:var(--font-latin);font-size:.78rem;color:var(--text-muted);text-align:center">${i+1}</td>
    <td><input type="text" name="items[${i}][item_name]" class="form-control form-control-sm" placeholder="Item name" required></td>
    <td><select name="items[${i}][category]" class="form-select form-select-sm">${Object.entries(categories).map(([v,l])=>`<option value="${v}">${l}</option>`).join('')}</select></td>
    <td><input type="number" name="items[${i}][quantity]" class="form-control form-control-sm item-qty" min="0.01" step="1" value="1" style="font-family:var(--font-latin)" required></td>
    <td><select name="items[${i}][unit]" class="form-select form-select-sm">${Object.entries(units).map(([v,l])=>`<option value="${v}">${l}</option>`).join('')}</select></td>
    <td><input type="number" name="items[${i}][unit_price]" class="form-control form-control-sm item-price" min="0" step="0.01" style="font-family:var(--font-latin)" placeholder="0.00"></td>
    <td style="font-family:var(--font-latin);font-weight:700" class="item-total">$0.00</td>
    <td><input type="text" name="items[${i}][item_description]" class="form-control form-control-sm" placeholder="Specs..."></td>
    <td><button type="button" class="btn btn-ghost btn-sm" style="color:var(--danger)" onclick="removeItem(${i})"><i class="bi bi-x-lg"></i></button></td>
  `;
  document.getElementById('itemsBody').appendChild(tr);
  updateItemCount();
  tr.querySelector('input')?.focus();

  // Bind calc
  tr.querySelector('.item-qty').addEventListener('input', () => calcRow(tr));
  tr.querySelector('.item-price').addEventListener('input', () => calcRow(tr));
}

function removeItem(i) {
  document.getElementById(`item-${i}`)?.remove();
  updateItemCount();
  calcGrandTotal();
}

function calcRow(tr) {
  const qty = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
  const price = parseFloat(tr.querySelector('.item-price')?.value) || 0;
  const total = qty * price;
  tr.querySelector('.item-total').textContent = total > 0 ? `$${total.toFixed(2)}` : '$0.00';
  calcGrandTotal();
}

function calcGrandTotal() {
  let sum = 0;
  document.querySelectorAll('.item-total').forEach(el => {
    sum += parseFloat(el.textContent.replace('$','')) || 0;
  });
  document.getElementById('grandTotal').textContent = `$${sum.toFixed(2)}`;
}

function updateItemCount() {
  document.getElementById('itemCount').textContent = document.querySelectorAll('#itemsBody tr').length;
}

// Add first item on load
addItem();

// Enter on last field → add new row
document.getElementById('itemsBody').addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); addItem(); }
});

// ── File upload with preview ──────────────────
let selectedFiles = new DataTransfer();
const fileInput = document.getElementById('fileInput');
const previewArea = document.getElementById('previewArea');

function handleFiles(files) {
  for (const f of files) {
    if (selectedFiles.files.length >= 10) break;
    selectedFiles.items.add(f);
  }
  fileInput.files = selectedFiles.files;
  renderPreviews();
}

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('dropZone').style.borderColor = 'var(--border-dark)';
  handleFiles(e.dataTransfer.files);
}

function removeFile(idx) {
  const dt = new DataTransfer();
  Array.from(selectedFiles.files).forEach((f, i) => { if (i !== idx) dt.items.add(f); });
  selectedFiles = dt;
  fileInput.files = selectedFiles.files;
  renderPreviews();
}

function renderPreviews() {
  previewArea.innerHTML = '';
  Array.from(selectedFiles.files).forEach((f, i) => {
    const isImg = f.type.startsWith('image/');
    const wrap = document.createElement('div');
    wrap.style.cssText = 'position:relative;border:1px solid var(--border);border-radius:8px;overflow:hidden;background:var(--surface-2);width:100px';
    if (isImg) {
      wrap.innerHTML = `<img src="${URL.createObjectURL(f)}" style="width:100px;height:72px;object-fit:cover;display:block"><div style="padding:.2rem .4rem;font-size:.65rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${f.name}</div>`;
    } else {
      const ext = f.name.split('.').pop().toUpperCase();
      wrap.innerHTML = `<div style="height:72px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--text-muted)"><i class="bi bi-file-earmark"></i></div><div style="padding:.2rem .4rem;font-size:.65rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${f.name}</div>`;
    }
    const rm = document.createElement('button');
    rm.type = 'button';
    rm.innerHTML = '&times;';
    rm.style.cssText = 'position:absolute;top:2px;right:2px;width:20px;height:20px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;border:none;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center';
    rm.onclick = () => removeFile(i);
    wrap.appendChild(rm);
    previewArea.appendChild(wrap);
  });
  if (selectedFiles.files.length > 1) {
    const clr = document.createElement('button');
    clr.type = 'button';
    clr.textContent = 'Clear All';
    clr.style.cssText = 'background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:.35rem .7rem;font-size:.72rem;font-weight:600;cursor:pointer;align-self:center';
    clr.onclick = () => { selectedFiles = new DataTransfer(); fileInput.files = selectedFiles.files; renderPreviews(); };
    previewArea.appendChild(clr);
  }
}
</script>
@endpush
