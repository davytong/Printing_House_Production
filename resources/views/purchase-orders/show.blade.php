@extends('layouts.app')
@section('title',$purchaseOrder->po_number)
@section('page-title','PO Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">{{ $purchaseOrder->po_number }}</h1>
    <p class="section-sub">{{ $purchaseOrder->supplier->name }}</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    @if($purchaseOrder->status === 'draft')
      <form action="{{ route('purchase-orders.status',$purchaseOrder) }}" method="POST">
        @csrf
        <input type="hidden" name="status" value="sent">
        <button class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Mark Sent</button>
      </form>
      <a href="{{ route('purchase-orders.edit',$purchaseOrder) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil"></i> Edit
      </a>
    @endif
    @if(in_array($purchaseOrder->status,['sent','partially_received']))
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#receiveModal">
        <i class="bi bi-box-arrow-in-down"></i> Record Receipt
      </button>
    @endif
    @if(!in_array($purchaseOrder->status,['received']))
      <form action="{{ route('purchase-orders.destroy',$purchaseOrder) }}" method="POST"
            data-confirm="តើអ្នកប្រាកដជាចង់លុប PO នេះមែនទេ? / Are you sure you want to delete this PO?">
        @csrf @method('DELETE')
        <button class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none"><i class="bi bi-trash3"></i></button>
      </form>
    @endif
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-info-circle"></i></div><span>ព័ត៌មាន PO</span></div>
      </div>
      <div class="panel-body d-flex flex-column gap-3">
        @php
          $stMap=['draft'=>'badge-staple','sent'=>'badge-binding','partially_received'=>'badge-progress','received'=>'badge-done','cancelled'=>'badge-pending'];
        @endphp
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:.82rem;color:var(--text-muted)">ស្ថានភាព</span>
          <span class="badge {{ $stMap[$purchaseOrder->status]??'badge-binding' }}" style="font-family:var(--font-latin)">{{ $purchaseOrder->status }}</span>
        </div>
        @foreach([
          ['ថ្ងៃបញ្ជា', $purchaseOrder->order_date->format('d/m/Y'), true],
          ['ត្រូវទទួល', $purchaseOrder->expected_date?->format('d/m/Y')??'—', true],
          ['ថ្ងៃទទួល', $purchaseOrder->received_date?->format('d/m/Y')??'—', true],
          ['រូបិយប័ណ្ណ', $purchaseOrder->currency, true],
          ['បង្កើតដោយ', $purchaseOrder->created_by??'—', false],
        ] as $row)
          @php [$lbl, $val] = $row; $latin = $row[2] ?? false; @endphp
          <div>
            <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">{{ $lbl }}</div>
            <div style="font-size:.9rem;{{ $latin?'font-family:var(--font-latin)':'' }}">{{ $val }}</div>
          </div>
        @endforeach
        <div class="divider" style="margin:0"></div>
        <div style="text-align:center">
          <div style="font-family:var(--font-latin);font-size:1.6rem;font-weight:800;color:var(--primary)">
            {{ number_format($purchaseOrder->total_amount,2) }}
          </div>
          <div style="font-size:.78rem;color:var(--text-muted)">{{ $purchaseOrder->currency }} Total</div>
        </div>
        @if($purchaseOrder->notes)
          <div><div style="font-size:.72rem;color:var(--text-muted)">Notes</div><div style="font-size:.85rem">{{ $purchaseOrder->notes }}</div></div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-header"><div class="ph-title"><div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-list-ul"></i></div><span>Items</span></div></div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-km">ឈ្មោះ</span><span class="th-en">Item</span></th>
              <th class="col-right"><span class="th-km">បញ្ជា</span><span class="th-en">Ordered</span></th>
              <th class="col-right"><span class="th-km">ទទួល</span><span class="th-en">Received</span></th>
              <th class="col-right"><span class="th-km">តម្លៃ/Unit</span><span class="th-en">Unit Price</span></th>
              <th class="col-right"><span class="th-km">សរុប</span><span class="th-en">Total</span></th>
            </tr>
          </thead>
          <tbody>
            @foreach($purchaseOrder->items as $item)
              <tr>
                <td>
                  <div style="font-weight:600">{{ $item->item_name }}</div>
                  @if($item->inventoryItem)
                    <div style="font-size:.72rem;color:var(--text-muted)">{{ $item->inventoryItem->code }}</div>
                  @endif
                </td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:600">{{ number_format($item->quantity_ordered,1) }} {{ $item->unit }}</td>
                <td style="text-align:right;font-family:var(--font-latin);
                  color:{{ $item->quantity_received>=$item->quantity_ordered?'var(--success)':($item->quantity_received>0?'var(--warning)':'var(--text-muted)') }};font-weight:600">
                  {{ number_format($item->quantity_received,1) }}
                </td>
                <td style="text-align:right;font-family:var(--font-latin)">${{ number_format($item->unit_price,2) }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:600">${{ number_format($item->total_price,2) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background:var(--surface-2)">
              <td colspan="4" style="text-align:right;font-weight:700;padding:.75rem 1rem">Grand Total:</td>
              <td style="text-align:right;font-family:var(--font-latin);font-weight:800;color:var(--primary);padding:.75rem 1rem">
                ${{ number_format($purchaseOrder->total_amount,2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- Attachments --}}
    @if($purchaseOrder->attachments && count($purchaseOrder->attachments) > 0)
    <div class="panel mt-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-paperclip"></i></div>
          <span>ឯកសារភ្ជាប់</span>
          <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ count($purchaseOrder->attachments) }}</span>
        </div>
      </div>
      <div class="panel-body">
        <div class="row g-3">
          @foreach($purchaseOrder->attachments as $index => $att)
            <div class="col-md-6">
              <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem;background:var(--surface-2);border-radius:var(--radius);border:1px solid var(--border)">
                <div style="width:40px;height:40px;background:var(--surface);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--primary)">
                  @if(str_contains($att['mime']??'', 'image'))
                    <i class="bi bi-file-image" style="font-size:1.3rem"></i>
                  @elseif(str_contains($att['mime']??'', 'pdf'))
                    <i class="bi bi-file-pdf" style="font-size:1.3rem"></i>
                  @else
                    <i class="bi bi-file-earmark" style="font-size:1.3rem"></i>
                  @endif
                </div>
                <div style="flex:1;min-width:0">
                  <div style="font-size:.85rem;font-weight:600;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $att['original_name'] }}
                  </div>
                  <div style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-latin)">
                    {{ number_format(($att['size']??0)/1024, 1) }} KB
                    @if($att['uploaded_at'] ?? false)
                      · {{ \Carbon\Carbon::parse($att['uploaded_at'])->format('d/m/Y H:i') }}
                    @endif
                  </div>
                </div>
                <a href="{{ asset('storage/'.$att['path']) }}" target="_blank" class="btn btn-ghost btn-sm" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                <form action="{{ route('purchase-orders.attachments.remove', $purchaseOrder) }}" method="POST"
                      data-confirm="តើអ្នកប្រាកដជាចង់លុបឯកសារភ្ជាប់នេះមែនទេ? / Are you sure you want to delete this attachment?">
                  @csrf @method('DELETE')
                  <input type="hidden" name="index" value="{{ $index }}">
                  <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)" title="Delete">
                    <i class="bi bi-trash3"></i>
                  </button>
                </form>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    {{-- Add Attachments Form (works for any status including received) --}}
    <div class="panel mt-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-plus-circle"></i></div>
          <span>បន្ថែមឯកសារភ្ជាប់ថ្មី</span>
        </div>
      </div>
      <div class="panel-body">
        <form action="{{ route('purchase-orders.attachments.add', $purchaseOrder) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label" style="font-size:.82rem;font-weight:600">
              ជ្រើសរើសឯកសារ (រូបភាព, PDF, Excel, Word)
            </label>
            <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" required>
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:.5rem">
              <i class="bi bi-info-circle"></i> អាចបន្ថែមបានច្រើនឯកសារក្នុងពេលតែមួយ (អតិបរមា 10 ឯកសារ, 10MB/file)
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="bi bi-upload"></i> បន្ថែមឯកសារ (Upload)
            </button>
            <div style="font-size:.78rem;color:var(--text-muted);padding:.5rem 0">
              ✅ អាចបន្ថែមឯកសារបានទោះបីជា PO ត្រូវបានទទួលរួចក៏ដោយ
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Receive Modal --}}
<div class="modal fade" id="receiveModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form action="{{ route('purchase-orders.receive',$purchaseOrder) }}" method="POST">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">Record Goods Receipt</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <table class="data-table">
            <thead>
              <tr>
                <th>Item</th>
                <th style="text-align:right">Ordered</th>
                <th style="text-align:right">Previously Received</th>
                <th style="text-align:right">Receiving Now</th>
              </tr>
            </thead>
            <tbody>
              @foreach($purchaseOrder->items as $i=>$item)
                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                <tr>
                  <td style="font-weight:600">{{ $item->item_name }}</td>
                  <td style="text-align:right;font-family:var(--font-latin)">{{ number_format($item->quantity_ordered,1) }}</td>
                  <td style="text-align:right;font-family:var(--font-latin)">{{ number_format($item->quantity_received,1) }}</td>
                  <td style="text-align:right">
                    <input type="number" name="items[{{ $i }}][quantity_received]"
                           class="form-control form-control-sm"
                           value="{{ number_format($item->quantity_ordered - $item->quantity_received, 1, '.', '') }}"
                           min="0" step="0.01" max="{{ $item->quantity_ordered }}"
                           style="width:100px;font-family:var(--font-latin);text-align:right;margin-left:auto">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> Confirm Receipt</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
