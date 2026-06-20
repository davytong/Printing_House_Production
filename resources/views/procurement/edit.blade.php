@extends('layouts.app')
@section('title','Edit — '.$procurement->request_number)
@section('page-title','Edit Procurement Request')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">Edit: {{ $procurement->request_number }}</h1>
  <a href="{{ route('procurement.show', $procurement) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-pencil-square"></i></div>
          <span>Edit Request Details</span>
        </div>
      </div>
      <div class="panel-body">
        <form action="{{ route('procurement.update', $procurement) }}" method="POST" enctype="multipart/form-data" id="editForm">
          @csrf @method('PUT')
          <div class="row g-3">

            {{-- Request Number --}}
            <div class="col-md-4">
              <label class="form-label">Request Number *</label>
              <input type="text" name="request_number" class="form-control"
                     value="{{ old('request_number', $procurement->request_number) }}" required>
              @error('request_number')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Request Date --}}
            <div class="col-md-4">
              <label class="form-label">Request Date *</label>
              <input type="date" name="request_date" class="form-control"
                     value="{{ old('request_date', $procurement->request_date?->format('Y-m-d')) }}" required>
              @error('request_date')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Due Date --}}
            <div class="col-md-4">
              <label class="form-label">Due Date</label>
              <input type="date" name="due_date" class="form-control"
                     value="{{ old('due_date', $procurement->due_date?->format('Y-m-d')) }}">
              @error('due_date')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Requester --}}
            <div class="col-md-6">
              <label class="form-label">Requester *</label>
              <input type="text" name="requester" class="form-control"
                     value="{{ old('requester', $procurement->requester) }}" required>
              @error('requester')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Department --}}
            <div class="col-md-6">
              <label class="form-label">Department *</label>
              <input type="text" name="department" class="form-control"
                     value="{{ old('department', $procurement->department) }}" required>
              @error('department')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Supplier --}}
            <div class="col-md-6">
              <label class="form-label">Supplier Name *</label>
              <input type="text" name="supplier_name" class="form-control"
                     value="{{ old('supplier_name', $procurement->supplier_name) }}" required>
              @error('supplier_name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Category --}}
            <div class="col-md-6">
              <label class="form-label">Category *</label>
              <select name="category" class="form-select" required>
                <option value="">Select category...</option>
                @foreach(['consumable'=>'Consumable','spare_part'=>'Spare Part','component'=>'Component','service'=>'Service','equipment'=>'Equipment','other'=>'Other'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('category', $procurement->category)===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
              @error('category')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Item Name --}}
            <div class="col-md-6">
              <label class="form-label">Item Name *</label>
              <input type="text" name="item_name" class="form-control"
                     value="{{ old('item_name', $procurement->item_name) }}" required>
              @error('item_name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Item Description --}}
            <div class="col-md-6">
              <label class="form-label">Item Description</label>
              <input type="text" name="item_description" class="form-control"
                     value="{{ old('item_description', $procurement->item_description) }}">
              @error('item_description')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Quantity --}}
            <div class="col-md-3">
              <label class="form-label">Quantity *</label>
              <input type="number" name="quantity" id="qty" class="form-control"
                     value="{{ old('quantity', $procurement->quantity) }}" min="1" step="1" required
                     style="font-family:var(--font-latin)">
              @error('quantity')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Unit --}}
            <div class="col-md-3">
              <label class="form-label">Unit *</label>
              <select name="unit" class="form-select" required>
                @foreach(['pcs'=>'Pcs','pack'=>'Pack','roll'=>'Roll','can'=>'Can','bottle'=>'Bottle','box'=>'Box','kg'=>'Kg','liter'=>'Liter','sheet'=>'Sheet','set'=>'Set','other'=>'Other'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('unit', $procurement->unit)===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
              @error('unit')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Unit Price --}}
            <div class="col-md-3">
              <label class="form-label">Unit Price ($) *</label>
              <input type="number" name="unit_price" id="unitPrice" class="form-control"
                     value="{{ old('unit_price', $procurement->unit_price) }}" min="0" step="0.01" required
                     style="font-family:var(--font-latin)">
              @error('unit_price')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Total Amount --}}
            <div class="col-md-3">
              <label class="form-label">Total Amount ($)</label>
              <input type="number" name="total_amount" id="totalAmount" class="form-control"
                     value="{{ old('total_amount', $procurement->total_amount) }}" min="0" step="0.01" readonly
                     style="font-family:var(--font-latin);background:var(--surface-2);font-weight:700">
              @error('total_amount')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Priority --}}
            <div class="col-md-4">
              <label class="form-label">Priority *</label>
              <select name="priority" class="form-select" required>
                @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('priority', $procurement->priority)===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
              @error('priority')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Status --}}
            <div class="col-md-4">
              <label class="form-label">Status *</label>
              <select name="status" class="form-select" required>
                @foreach(['pending'=>'Pending','approved'=>'Approved','ordered'=>'Ordered','received'=>'Received','completed'=>'Completed','cancelled'=>'Cancelled'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('status', $procurement->status)===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
              @error('status')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Remarks --}}
            <div class="col-md-4">
              <label class="form-label">Remarks</label>
              <input type="text" name="remarks" class="form-control"
                     value="{{ old('remarks', $procurement->remarks) }}">
              @error('remarks')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            {{-- Existing Attachments --}}
            @if($procurement->attachments->count())
              <div class="col-12">
                <label class="form-label">Existing Attachments</label>
                <div style="display:flex;flex-wrap:wrap;gap:.75rem">
                  @foreach($procurement->attachments as $att)
                    @php
                      $ext = pathinfo($att->file_name, PATHINFO_EXTENSION);
                      $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
                      $icon = match(strtolower($ext)) {
                        'pdf'=>'bi-file-earmark-pdf text-danger',
                        'doc','docx'=>'bi-file-earmark-word text-primary',
                        'xls','xlsx','csv'=>'bi-file-earmark-excel text-success',
                        default=>'bi-file-earmark text-secondary'
                      };
                    @endphp
                    <div style="position:relative;border:1px solid var(--border);border-radius:var(--radius);padding:.5rem .75rem;display:flex;align-items:center;gap:.5rem;background:var(--surface-2)">
                      @if($isImage)
                        <img src="{{ Storage::url($att->file_path) }}" style="width:32px;height:32px;object-fit:cover;border-radius:4px">
                      @else
                        <i class="bi {{ $icon }}" style="font-size:1.2rem"></i>
                      @endif
                      <span style="font-size:.78rem;font-weight:500;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $att->file_name }}</span>
                      <form action="{{ route('procurement.delete-attachment', $att) }}" method="POST" style="margin:0"
                            onsubmit="return confirm('Delete this file?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm" style="padding:0;width:24px;height:24px;color:var(--danger);font-size:.75rem">
                          <i class="bi bi-x-lg"></i>
                        </button>
                      </form>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Upload New Attachments --}}
            <div class="col-12">
              <label class="form-label">Upload New Attachments</label>
              <div style="border:2px dashed var(--border-dark);border-radius:var(--radius);padding:1.25rem;text-align:center;background:var(--surface-2);cursor:pointer"
                   onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-cloud-arrow-up" style="font-size:1.5rem;color:var(--text-muted)"></i>
                <p style="margin:.3rem 0 0;font-size:.82rem;color:var(--text-secondary)">Click to upload additional files</p>
              </div>
              <input type="file" name="attachments[]" id="fileInput" multiple class="d-none"
                     accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.csv">
              <div id="fileList" style="margin-top:.5rem"></div>
              @error('attachments.*')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg"></i> Update Request</button>
            <a href="{{ route('procurement.show', $procurement) }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-calculate total
const qtyInput = document.getElementById('qty');
const priceInput = document.getElementById('unitPrice');
const totalInput = document.getElementById('totalAmount');

function calcTotal() {
  const qty = parseFloat(qtyInput.value) || 0;
  const price = parseFloat(priceInput.value) || 0;
  totalInput.value = (qty * price).toFixed(2);
}

qtyInput.addEventListener('input', calcTotal);
priceInput.addEventListener('input', calcTotal);

// File list display
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');

fileInput.addEventListener('change', function() {
  fileList.innerHTML = '';
  Array.from(this.files).forEach(f => {
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:.3rem .5rem;font-size:.8rem;color:var(--text-secondary)';
    div.innerHTML = '<i class="bi bi-file-earmark"></i> ' + f.name + ' <span style="color:var(--text-muted)">(' + (f.size/1024).toFixed(1) + ' KB)</span>';
    fileList.appendChild(div);
  });
});
</script>
@endpush
