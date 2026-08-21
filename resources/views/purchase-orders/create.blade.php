@extends('layouts.app')
@section('title', t('purchase_order') . ' ថ្មី')
@section('page-title', 'New Purchase Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="section-title">បង្កើត {{ t('purchase_order') }} ថ្មី</h1>
    <p class="section-sub">រៀបចំការបញ្ជាទិញសម្ភារៈពីអ្នកផ្គត់ផ្គង់</p>
  </div>
  <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> {{ t('back') }}
  </a>
</div>

<form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm" enctype="multipart/form-data">
@csrf
<div class="row g-4">
  <div class="col-lg-8">
    
    {{-- Basic Info --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-info-circle"></i></div>
          <span>{{ t('request_info') }}</span>
        </div>
      </div>
      <div class="panel-body">
        <div class="row g-3">
          {{-- Priority --}}
          <div class="col-md-4">
            <label class="form-label">{{ t('priority') }} *</label>
            <select name="priority" class="form-select" required id="prioritySelect">
              <option value="low" {{ old('priority')=='low'?'selected':'' }}>🟢 {{ t('low') }}</option>
              <option value="medium" {{ old('priority','medium')=='medium'?'selected':'' }}>🟡 {{ t('medium') }}</option>
              <option value="high" {{ old('priority')=='high'?'selected':'' }}>🟠 {{ t('high') }}</option>
              <option value="urgent" {{ old('priority')=='urgent'?'selected':'' }}>🔴 {{ t('urgent') }}</option>
            </select>
          </div>

          {{-- Status --}}
          <div class="col-md-4">
            <label class="form-label">{{ t('status') }} *</label>
            <select name="status" class="form-select" required>
              <option value="draft" selected>📝 {{ t('draft') }}</option>
              @if(\App\Services\RoleService::can('approve_po'))
              <option value="pending_approval">⏳ {{ t('pending_approval') }}</option>
              <option value="approved">✅ {{ t('approved') }}</option>
              @endif
            </select>
          </div>

          {{-- Order Date --}}
          <div class="col-md-4">
            <label class="form-label">{{ t('date') }} *</label>
            <input type="date" name="order_date" class="form-control" 
                   value="{{ old('order_date', today()->format('Y-m-d')) }}" 
                   required style="font-family:var(--font-latin)">
          </div>

          {{-- Reason --}}
          <div class="col-md-12">
            <label class="form-label">{{ t('reason_for_purchase') }} *</label>
            <select id="reasonSelect" class="form-select mb-2" onchange="fillReason()">
              <option value="">— ជ្រើសរើសហេតុផល ឬវាយដោយខ្លួនឯង —</option>
              <optgroup label="ស្តុកទាប — Low Stock">
                <option value="ស្តុកសម្ភារៈទាបខ្លាំង - ត្រូវការបន្ទាន់សម្រាប់ផលិតកម្ម">🔴 ស្តុកសម្ភារៈទាបខ្លាំង - ត្រូវការបន្ទាន់សម្រាប់ផលិតកម្ម</option>
                <option value="ស្តុកក្រដាសនៅសល់តិច - ត្រូវការសម្រាប់ Batch បច្ចុប្បន្ន">📄 ស្តុកក្រដាសនៅសល់តិច - ត្រូវការសម្រាប់ Batch បច្ចុប្បន្ន</option>
                <option value="ស្តុក Film អស់ស្ទើរ - ចាំបាច់ទិញបន្ថែម">🎞️ ស្តុក Film អស់ស្ទើរ - ចាំបាច់ទិញបន្ថែម</option>
                <option value="ស្តុក Consumable ចាំបាច់បញ្ជាទិញឡើងវិញ">🧴 ស្តុក Consumable ចាំបាច់បញ្ជាទិញឡើងវិញ</option>
              </optgroup>
              <optgroup label="សម្ភារៈថ្មី — New Materials">
                <option value="ត្រូវការសម្ភារៈថ្មីសម្រាប់ការងារថ្មី">✨ ត្រូវការសម្ភារៈថ្មីសម្រាប់ការងារថ្មី</option>
                <option value="បន្ថែមសម្ភារៈថ្មីដើម្បីបង្កើនផលិតកម្ម">📈 បន្ថែមសម្ភារៈថ្មីដើម្បីបង្កើនផលិតកម្ម</option>
                <option value="ទិញសម្ភារៈថ្មីសម្រាប់ Batch ថ្មី">🆕 ទិញសម្ភារៈថ្មីសម្រាប់ Batch ថ្មី</option>
              </optgroup>
              <optgroup label="ជំនួស — Replacement">
                <option value="ជំនួសសម្ភារៈចាស់ដែលខូច">🔄 ជំនួសសម្ភារៈចាស់ដែលខូច</option>
                <option value="ជំនួសគ្រឿងបន្លាស់ម៉ាស៊ីនដែលអស់">🔧 ជំនួសគ្រឿងបន្លាស់ម៉ាស៊ីនដែលអស់</option>
                <option value="ជំនួសសម្ភារៈគុណភាពទាប">⚠️ ជំនួសសម្ភារៈគុណភាពទាប</option>
              </optgroup>
              <optgroup label="ថែទាំ — Maintenance">
                <option value="ទិញគ្រឿងបន្លាស់សម្រាប់ថែទាំម៉ាស៊ីនតាមកាលវិភាគ">🛠️ ទិញគ្រឿងបន្លាស់សម្រាប់ថែទាំម៉ាស៊ីនតាមកាលវិភាគ</option>
                <option value="ទិញសម្ភារៈថែទាំ និងសម្អាតម៉ាស៊ីន">🧹 ទិញសម្ភារៈថែទាំ និងសម្អាតម៉ាស៊ីន</option>
                <option value="ជួសជុលម៉ាស៊ីន - ត្រូវការគ្រឿងបន្លាស់បន្ទាន់">🚨 ជួសជុលម៉ាស៊ីន - ត្រូវការគ្រឿងបន្លាស់បន្ទាន់</option>
              </optgroup>
              <optgroup label="ផ្សេងៗ — Other">
                <option value="តម្រូវការពីអតិថិជន - ការបញ្ជាដ៏ធំ">👥 តម្រូវការពីអតិថិជន - ការបញ្ជាដ៏ធំ</option>
                <option value="បន្ថែមស្តុកសុវត្ថិភាព">🛡️ បន្ថែមស្តុកសុវត្ថិភាព</option>
                <option value="ទិញមុនអស់ពីផ្សារ - តម្លៃល្អ">💰 ទិញមុនអស់ពីផ្សារ - តម្លៃល្អ</option>
              </optgroup>
            </select>
            <textarea name="reason" id="reasonTextarea" class="form-control" rows="3" required 
                      placeholder="ជ្រើសរើសពីខាងលើ ឬវាយដោយខ្លួនឯង...&#10;&#10;Example: ស្តុកក្រដាស A4 នៅសល់ត្រឹម 50 ream - ត្រូវការបន្ថែម 100 reams សម្រាប់ Batch 2 ដែលចាប់ផ្តើមថ្ងៃទី 1 ខែកក្កដា">{{ old('reason') }}</textarea>
            <small class="text-muted">
              <i class="bi bi-info-circle"></i> ជ្រើសរើសហេតុផលរួចរាល់ ឬបញ្ចូលដោយខ្លួនឯង • អាចកែសម្រួលបន្ថែម
            </small>
          </div>
        </div>
      </div>
    </div>

    {{-- Supplier & Dates --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-building"></i></div>
          <span>អ្នកផ្គត់ផ្គង់ & ពេលវេលា</span>
        </div>
      </div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ t('supplier') }} *</label>
            <select name="supplier_id" class="form-select" required>
              <option value="">— {{ t('select') }} —</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}" {{ old('supplier_id')==$s->id?'selected':'' }}>
                  {{ $s->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">{{ t('expected_date') }}</label>
            <input type="date" name="expected_date" class="form-control" 
                   value="{{ old('expected_date') }}" 
                   style="font-family:var(--font-latin)">
            <small class="text-muted">ថ្ងៃដែលរំពឹងថាទទួលបានទំនិញ</small>
          </div>
        </div>
      </div>
    </div>

    {{-- Items --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-list-ul"></i></div>
          <span>{{ t('items') }}</span>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
          <i class="bi bi-plus-lg"></i> បន្ថែម Item
        </button>
      </div>
      <div class="panel-body">
        <div id="itemsContainer">
          {{-- Item Row Template --}}
          <div class="item-row mb-3 p-3" style="background:var(--surface-2);border-radius:var(--radius);border:1px solid var(--border)">
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label class="form-label" style="font-size:.75rem">{{ t('item_name') }} *</label>
                <input type="text" name="items[0][item_name]" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-2">
                <label class="form-label" style="font-size:.75rem">{{ t('unit') }} *</label>
                <select name="items[0][unit]" class="form-select form-select-sm">
                  @foreach(['pcs','kg','liter','ream','box','roll','sheet','set'] as $u)
                    <option value="{{ $u }}">{{ $u }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label" style="font-size:.75rem">{{ t('quantity') }} *</label>
                <input type="number" name="items[0][quantity_ordered]" 
                       class="form-control form-control-sm qty" 
                       min="0.01" step="0.01" value="1" 
                       style="font-family:var(--font-latin)" required>
              </div>
              <div class="col-md-2">
                <label class="form-label" style="font-size:.75rem">{{ t('price') }}/Unit</label>
                <input type="number" name="items[0][unit_price]" 
                       class="form-control form-control-sm price" 
                       min="0" step="0.01" value="0" 
                       style="font-family:var(--font-latin)">
              </div>
              <div class="col-md-1">
                <label class="form-label" style="font-size:.75rem">{{ t('total') }}</label>
                <div class="form-control form-control-sm line-total" 
                     style="background:var(--surface);font-family:var(--font-latin);font-weight:600;pointer-events:none">0.00</div>
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
                  <option value="">ភ្ជាប់ Inventory ({{ t('optional') }})</option>
                  @foreach($inventoryItems as $inv)
                    <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->code }})</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
        
        <div class="d-flex justify-content-end mt-3 pt-3" style="border-top:2px solid var(--border)">
          <div style="font-family:var(--font-latin);font-size:1.2rem;font-weight:700;color:var(--primary)">
            {{ t('total') }}: <span id="grandTotal">0.00</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Payment Info --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-credit-card"></i></div>
          <span>ព័ត៌មានទូទាត់</span>
        </div>
      </div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">{{ t('currency') }} *</label>
            <select name="currency" class="form-select" required>
              <option value="USD" selected>USD</option>
              <option value="KHR">KHR</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ t('payment_method') }} *</label>
            <select name="payment_method" class="form-select" required>
              <option value="cash" selected>💵 {{ t('cash') }}</option>
              <option value="bank">🏦 {{ t('bank_transfer') }}</option>
              <option value="credit">💳 {{ t('credit') }}</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ t('payment_status') }}</label>
            <select name="payment_status" class="form-select">
              <option value="pending" selected>⏳ {{ t('pending') }}</option>
              <option value="partial">📊 Partial</option>
              <option value="paid">✅ Paid</option>
            </select>
          </div>

          <div class="col-md-12">
            <label class="form-label">{{ t('notes') }}</label>
            <textarea name="notes" class="form-control" rows="2" 
                      placeholder="បញ្ចូលចំណាំផ្សេងទៀត...">{{ old('notes') }}</textarea>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- Sidebar --}}
  <div class="col-lg-4">
    
    {{-- Attachments --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fee2e2;color:#991b1b"><i class="bi bi-paperclip"></i></div>
          <span>ភ្ជាប់ឯកសារ</span>
        </div>
      </div>
      <div class="panel-body">
        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;line-height:1.6">
          ភ្ជាប់ Quotation, Invoice, ឬឯកសារពាក់ព័ន្ធ
          <br><span style="font-size:.72rem">Max: 10 files, 10MB each</span>
        </p>
        <input type="file" name="attachments[]" class="form-control form-control-sm" 
               multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx" 
               style="font-size:.8rem">
        <div style="font-size:.7rem;color:var(--text-muted);margin-top:.5rem">
          <i class="bi bi-info-circle"></i> ទទួលយក: JPG, PNG, PDF, DOC, XLS
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="panel mb-4">
      <div class="panel-body d-flex flex-column gap-3">
        <button type="submit" name="action" value="create" 
                class="btn btn-primary btn-lg w-100">
          <i class="bi bi-check-lg"></i> {{ t('save') }} PO
        </button>
        
        @if(\App\Services\RoleService::can('approve_po'))
        <button type="submit" name="action" value="submit_for_approval" 
                class="btn btn-success btn-lg w-100">
          <i class="bi bi-send"></i> {{ t('submit_for_approval') }}
        </button>
        @endif

        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary w-100">
          {{ t('cancel') }}
        </a>
      </div>
    </div>

    {{-- Info Card --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius);padding:1rem">
      <h6 style="font-size:.85rem;font-weight:700;color:#1e40af;margin-bottom:.5rem">
        <i class="bi bi-info-circle"></i> ការណែនាំ
      </h6>
      <ul style="font-size:.75rem;color:#1e40af;margin:0;padding-left:1.25rem;line-height:1.7">
        <li>បំពេញព័ត៌មានលម្អិត សម្ភារៈ និងតម្លៃ</li>
        <li>បញ្ជាក់ហេតុផលនៃការទិញឱ្យច្បាស់</li>
        <li>PO ក្នុងស្ថានភាព "Draft" អាចកែបានគ្រប់ពេល</li>
        <li>PO "Pending Approval" ត្រូវរង់ចាំការអនុម័ត</li>
        <li>ភ្ជាប់ឯកសារដើម្បីងាយស្រួលតាមដាន</li>
      </ul>
    </div>

  </div>
</div>
</form>
@endsection

@push('scripts')
<script>
// ─── REASON SUGGESTION ───────────────────────────────────
function fillReason() {
  const select = document.getElementById('reasonSelect');
  const textarea = document.getElementById('reasonTextarea');
  const selectedValue = select.value;
  
  if (selectedValue) {
    // If textarea is empty, fill it with selected value
    // If textarea has content, append with newline
    if (textarea.value.trim() === '') {
      textarea.value = selectedValue;
    } else {
      // Ask user if they want to replace or append
      if (confirm('តើអ្នកចង់ជំនួសអត្ថបទបច្ចុប្បន្ន ឬបន្ថែម?\n\nចុច OK = ជំនួស\nចុច Cancel = បន្ថែម')) {
        textarea.value = selectedValue;
      } else {
        textarea.value = textarea.value.trim() + '\n\n' + selectedValue;
      }
    }
    // Reset select
    select.selectedIndex = 0;
    // Focus textarea for editing
    textarea.focus();
  }
}

// ─── ITEM CALCULATIONS ───────────────────────────────────
let idx = 1;
const inventoryOptions = `@foreach($inventoryItems as $inv)<option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->code }})</option>@endforeach`;
const unitOptions = `@foreach(['pcs','kg','liter','ream','box','roll','sheet','set'] as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach`;

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
      <div class="col-md-4"><label class="form-label" style="font-size:.75rem">{{ t('item_name') }} *</label><input type="text" name="items[${idx}][item_name]" class="form-control form-control-sm" required></div>
      <div class="col-md-2"><label class="form-label" style="font-size:.75rem">{{ t('unit') }} *</label><select name="items[${idx}][unit]" class="form-select form-select-sm">${unitOptions}</select></div>
      <div class="col-md-2"><label class="form-label" style="font-size:.75rem">{{ t('quantity') }} *</label><input type="number" name="items[${idx}][quantity_ordered]" class="form-control form-control-sm qty" min="0.01" step="0.01" value="1" style="font-family:var(--font-latin)" required></div>
      <div class="col-md-2"><label class="form-label" style="font-size:.75rem">{{ t('price') }}/Unit</label><input type="number" name="items[${idx}][unit_price]" class="form-control form-control-sm price" min="0" step="0.01" value="0" style="font-family:var(--font-latin)"></div>
      <div class="col-md-1"><label class="form-label" style="font-size:.75rem">{{ t('total') }}</label><div class="form-control form-control-sm line-total" style="background:var(--surface);font-family:var(--font-latin);font-weight:600;pointer-events:none">0.00</div></div>
      <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-ghost btn-sm remove-row" style="color:var(--danger)"><i class="bi bi-trash3"></i></button></div>
    </div>
    <div class="row g-2 mt-1">
      <div class="col-md-4"><select name="items[${idx}][inventory_item_id]" class="form-select form-select-sm" style="font-size:.75rem"><option value="">ភ្ជាប់ Inventory ({{ t('optional') }})</option>${inventoryOptions}</select></div>
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
    btn.addEventListener('click', () => { 
      if(document.querySelectorAll('.item-row').length > 1) {
        btn.closest('.item-row').remove(); 
        calcGrand(); 
      } else {
        alert('ត្រូវមានយ៉ាងហោចណាស់ 1 item!');
      }
    });
  });
}

// Initial calculation
bindEvents();
calcGrand();
</script>
@endpush
