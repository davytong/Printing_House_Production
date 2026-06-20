@extends('layouts.app')
@section('title','PO ថ្មី')
@section('page-title','New Purchase Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">បង្កើត Purchase Order ថ្មី</h1>
  <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
</div>

<form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
@csrf
<div class="row g-4">
  <div class="col-lg-8">
    {{-- Header --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-cart3"></i></div>
          <span>ព័ត៌មាន PO</span>
        </div>
      </div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">អ្នកផ្គត់ផ្គង់ *</label>
            <select name="supplier_id" class="form-select" required>
              <option value="">— ជ្រើស —</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}" {{ old('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">ថ្ងៃបញ្ជា *</label>
            <input type="date" name="order_date" class="form-control" value="{{ old('order_date',today()->format('Y-m-d')) }}" required style="font-family:var(--font-latin)">
          </div>
          <div class="col-md-3">
            <label class="form-label">ត្រូវទទួល</label>
            <input type="date" name="expected_date" class="form-control" value="{{ old('expected_date') }}" style="font-family:var(--font-latin)">
          </div>
          <div class="col-md-3">
            <label class="form-label">រូបិយប័ណ្ណ</label>
            <select name="currency" class="form-select">
              <option value="USD" selected>USD</option>
              <option value="KHR">KHR</option>
            </select>
          </div>
          <div class="col-md-9">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
          </div>
        </div>
      </div>
    </div>

    {{-- Items --}}
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-list-ul"></i></div>
          <span>Items</span>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
          <i class="bi bi-plus-lg"></i> បន្ថែម Item
        </button>
      </div>
      <div class="panel-body">
        <div id="itemsContainer">
          <div class="item-row mb-3 p-3" style="background:var(--surface-2);border-radius:var(--radius);border:1px solid var(--border)">
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label class="form-label" style="font-size:.75rem">ឈ្មោះ Item *</label>
                <input type="text" name="items[0][item_name]" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-2">
                <label class="form-label" style="font-size:.75rem">Unit *</label>
                <select name="items[0][unit]" class="form-select form-select-sm">
                  @foreach(['pcs','kg','liter','ream','box','roll','sheet'] as $u)
                    <option value="{{ $u }}">{{ $u }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label" style="font-size:.75rem">ចំនួន *</label>
                <input type="number" name="items[0][quantity_ordered]" class="form-control form-control-sm qty" min="0.01" step="0.01" value="1" style="font-family:var(--font-latin)" required>
              </div>
              <div class="col-md-2">
                <label class="form-label" style="font-size:.75rem">តម្លៃ/Unit</label>
                <input type="number" name="items[0][unit_price]" class="form-control form-control-sm price" min="0" step="0.01" value="0" style="font-family:var(--font-latin)">
              </div>
              <div class="col-md-1">
                <label class="form-label" style="font-size:.75rem">សរុប</label>
                <div class="form-control form-control-sm line-total" style="background:var(--surface);font-family:var(--font-latin);font-weight:600;pointer-events:none">0.00</div>
              </div>
              <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-ghost btn-sm remove-row" style="color:var(--danger)">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
            <div class="row g-2 mt-1">
              <div class="col-md-4">
                <select name="items[0][inventory_item_id]" class="form-select form-select-sm" style="font-size:.75rem">
                  <option value="">ភ្ជាប់ Inventory (ស្រេចចិត្ត)</option>
                  @foreach($inventoryItems as $inv)
                    <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->code }})</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-end mt-2">
          <div style="font-family:var(--font-latin);font-size:1rem;font-weight:700;color:var(--primary)">
            Total: <span id="grandTotal">0.00</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="panel mb-4">
      <div class="panel-body d-flex flex-column gap-3">
        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-check-lg"></i> បង្កើត PO</button>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary w-100">បោះបង់</a>
      </div>
    </div>
  </div>
</div>
</form>
@endsection

@push('scripts')
<script>
let idx = 1;
const inventoryOptions = `@foreach($inventoryItems as $inv)<option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->code }})</option>@endforeach`;
const unitOptions = `@foreach(['pcs','kg','liter','ream','box','roll','sheet'] as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach`;

function calcRow(row) {
  const qty   = parseFloat(row.querySelector('.qty').value)   || 0;
  const price = parseFloat(row.querySelector('.price').value) || 0;
  const total = (qty * price).toFixed(2);
  row.querySelector('.line-total').textContent = total;
  calcGrand();
}
function calcGrand() {
  let grand = 0;
  document.querySelectorAll('.line-total').forEach(el => grand += parseFloat(el.textContent)||0);
  document.getElementById('grandTotal').textContent = grand.toFixed(2);
}
document.getElementById('addItemBtn').addEventListener('click', () => {
  const tpl = `
  <div class="item-row mb-3 p-3" style="background:var(--surface-2);border-radius:var(--radius);border:1px solid var(--border)">
    <div class="row g-2 align-items-end">
      <div class="col-md-4"><label class="form-label" style="font-size:.75rem">ឈ្មោះ Item *</label><input type="text" name="items[${idx}][item_name]" class="form-control form-control-sm" required></div>
      <div class="col-md-2"><label class="form-label" style="font-size:.75rem">Unit *</label><select name="items[${idx}][unit]" class="form-select form-select-sm">${unitOptions}</select></div>
      <div class="col-md-2"><label class="form-label" style="font-size:.75rem">ចំនួន *</label><input type="number" name="items[${idx}][quantity_ordered]" class="form-control form-control-sm qty" min="0.01" step="0.01" value="1" style="font-family:var(--font-latin)" required></div>
      <div class="col-md-2"><label class="form-label" style="font-size:.75rem">តម្លៃ/Unit</label><input type="number" name="items[${idx}][unit_price]" class="form-control form-control-sm price" min="0" step="0.01" value="0" style="font-family:var(--font-latin)"></div>
      <div class="col-md-1"><label class="form-label" style="font-size:.75rem">សរុប</label><div class="form-control form-control-sm line-total" style="background:var(--surface);font-family:var(--font-latin);font-weight:600;pointer-events:none">0.00</div></div>
      <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-ghost btn-sm remove-row" style="color:var(--danger)"><i class="bi bi-trash3"></i></button></div>
    </div>
    <div class="row g-2 mt-1">
      <div class="col-md-4"><select name="items[${idx}][inventory_item_id]" class="form-select form-select-sm" style="font-size:.75rem"><option value="">ភ្ជាប់ Inventory</option>${inventoryOptions}</select></div>
    </div>
  </div>`;
  document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', tpl);
  idx++;
  bindEvents();
});
function bindEvents() {
  document.querySelectorAll('.qty, .price').forEach(el => {
    el.addEventListener('input', () => calcRow(el.closest('.item-row')));
  });
  document.querySelectorAll('.remove-row').forEach(btn => {
    btn.addEventListener('click', () => { btn.closest('.item-row').remove(); calcGrand(); });
  });
}
bindEvents();
</script>
@endpush
