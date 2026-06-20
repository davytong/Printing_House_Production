@extends('layouts.app')
@section('title','PR — '.$procurement->request_number)
@section('page-title','Procurement Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">{{ $procurement->request_number }}</h1>
    <p class="section-sub">{{ $procurement->item_name }} — {{ $procurement->supplier_name }}</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('procurement.edit', $procurement) }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-pencil"></i> Edit
    </a>
    <div class="dropdown">
      <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi bi-arrow-repeat"></i> Change Status
      </button>
      <ul class="dropdown-menu">
        @foreach(['pending','approved','ordered','received','completed','cancelled'] as $s)
          @if($s !== $procurement->status)
            <li>
              <form action="{{ route('procurement.status', $procurement) }}" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $s }}">
                <button type="submit" class="dropdown-item">{{ ucfirst($s) }}</button>
              </form>
            </li>
          @endif
        @endforeach
      </ul>
    </div>
    <form action="{{ route('procurement.destroy', $procurement) }}" method="POST"
          onsubmit="return confirm('Are you sure you want to delete this request?')">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-outline-secondary btn-sm" style="color:var(--danger)">
        <i class="bi bi-trash"></i> Delete
      </button>
    </form>
  </div>
</div>

<div class="row g-4">
  {{-- Left Column — Details --}}
  <div class="col-lg-7">
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-info-circle"></i></div>
          <span>Request Details</span>
        </div>
        @php
          $statusBadge = match($procurement->status) {
            'pending'=>'badge-pending','approved'=>'badge-binding','ordered'=>'badge-progress',
            'received'=>'badge-staple','completed'=>'badge-done','cancelled'=>'badge-pending',
            default=>'badge-progress'
          };
        @endphp
        <span class="badge {{ $statusBadge }}" style="font-size:.8rem">{{ ucfirst($procurement->status) }}</span>
      </div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-6 col-md-4">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Request Date</div>
            <div style="font-weight:600;font-family:var(--font-latin);font-size:.88rem">{{ $procurement->request_date?->format('d M Y') }}</div>
          </div>
          <div class="col-6 col-md-4">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Due Date</div>
            <div style="font-weight:600;font-family:var(--font-latin);font-size:.88rem">
              {{ $procurement->due_date?->format('d M Y') ?? '—' }}
              @if($procurement->isOverdue())
                <span class="badge badge-pending" style="font-size:.65rem;margin-left:.3rem">Overdue</span>
              @endif
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Priority</div>
            <div style="font-weight:600;font-size:.88rem;display:flex;align-items:center;gap:.4rem">
              @php $pColor = $procurement->priorityColor(); @endphp
              <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $pColor }}"></span>
              {{ ucfirst($procurement->priority) }}
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Requester</div>
            <div style="font-weight:600;font-size:.88rem">{{ $procurement->requester }}</div>
          </div>
          <div class="col-6 col-md-4">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Department</div>
            <div style="font-weight:600;font-size:.88rem">{{ $procurement->department }}</div>
          </div>
          <div class="col-6 col-md-4">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Category</div>
            <div style="font-weight:600;font-size:.88rem">{{ $procurement->categoryLabel() }}</div>
          </div>
        </div>

        <div class="divider"></div>

        <div class="row g-3">
          <div class="col-md-6">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Supplier</div>
            <div style="font-weight:700;font-size:.95rem">{{ $procurement->supplier_name }}</div>
          </div>
          <div class="col-md-6">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Item Name</div>
            <div style="font-weight:700;font-size:.95rem">{{ $procurement->item_name }}</div>
          </div>
          @if($procurement->item_description)
            <div class="col-12">
              <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Description</div>
              <div style="font-size:.88rem;color:var(--text-secondary)">{{ $procurement->item_description }}</div>
            </div>
          @endif
        </div>

        <div class="divider"></div>

        <div class="row g-3">
          <div class="col-4 col-md-3">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Quantity</div>
            <div style="font-weight:700;font-family:var(--font-latin);font-size:1.1rem">{{ $procurement->quantity }} <span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">{{ $procurement->unit }}</span></div>
          </div>
          <div class="col-4 col-md-3">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Unit Price</div>
            <div style="font-weight:700;font-family:var(--font-latin);font-size:1.1rem">${{ number_format($procurement->unit_price, 2) }}</div>
          </div>
          <div class="col-4 col-md-3">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Total Amount</div>
            <div style="font-weight:800;font-family:var(--font-latin);font-size:1.2rem;color:var(--primary)">${{ number_format($procurement->total_amount, 2) }}</div>
          </div>
        </div>

        @if($procurement->remarks)
          <div class="divider"></div>
          <div>
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.2rem">Remarks</div>
            <div style="font-size:.88rem;color:var(--text-secondary)">{{ $procurement->remarks }}</div>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Right Column — Attachments & Log --}}
  <div class="col-lg-5">
    {{-- Attachments --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-paperclip"></i></div>
          <span>Attachments</span>
          <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ $procurement->attachments->count() }}</span>
        </div>
      </div>
      <div class="panel-body">
        @forelse($procurement->attachments as $att)
          @php
            $ext = pathinfo($att->file_name, PATHINFO_EXTENSION);
            $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
            $icon = match(strtolower($ext)) {
              'pdf'=>'bi-file-earmark-pdf text-danger',
              'doc','docx'=>'bi-file-earmark-word text-primary',
              'xls','xlsx','csv'=>'bi-file-earmark-excel text-success',
              'jpg','jpeg','png','gif','webp'=>'bi-file-earmark-image text-warning',
              default=>'bi-file-earmark text-secondary'
            };
          @endphp
          <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem 0;border-bottom:1px solid var(--border);{{ $loop->last?'border:none':'' }}">
            @if($isImage)
              <img src="{{ Storage::url($att->file_path) }}" alt="{{ $att->file_name }}"
                   style="width:40px;height:40px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border)">
            @else
              <div style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:var(--surface-2);border-radius:var(--radius-sm)">
                <i class="bi {{ $icon }}" style="font-size:1.2rem"></i>
              </div>
            @endif
            <div style="flex:1;min-width:0">
              <div style="font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $att->file_name }}</div>
              <div style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-latin)">{{ strtoupper($ext) }} · {{ number_format($att->file_size / 1024, 1) }} KB</div>
            </div>
            <div class="d-flex gap-1">
              <a href="{{ Storage::url($att->file_path) }}" class="btn btn-ghost btn-sm" target="_blank" title="Download">
                <i class="bi bi-download"></i>
              </a>
              <form action="{{ route('procurement.delete-attachment', $att) }}" method="POST"
                    onsubmit="return confirm('Delete this attachment?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        @empty
          <div class="empty-state" style="padding:2rem 1rem">
            <div class="empty-icon" style="width:48px;height:48px;font-size:1.2rem"><i class="bi bi-paperclip"></i></div>
            <p style="font-size:.82rem;margin:0">No attachments</p>
          </div>
        @endforelse
      </div>
    </div>

    {{-- Activity Log --}}
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#d1fae5;color:#065f46"><i class="bi bi-clock-history"></i></div>
          <span>Activity Log</span>
        </div>
      </div>
      <div class="panel-body" style="max-height:360px;overflow-y:auto">
        @forelse($procurement->logs->sortByDesc('created_at') as $log)
          <div style="display:flex;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--border);{{ $loop->last?'border:none':'' }}">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--primary);margin-top:6px;flex-shrink:0"></div>
            <div style="flex:1;min-width:0">
              <div style="font-size:.82rem;font-weight:600;color:var(--text-primary)">{{ $log->action }}</div>
              @if($log->description)
                <div style="font-size:.78rem;color:var(--text-secondary)">{{ $log->description }}</div>
              @endif
              <div style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-latin);margin-top:.2rem">
                {{ $log->created_at->format('d M Y H:i') }}
                @if($log->user_name) · {{ $log->user_name }}@endif
              </div>
            </div>
          </div>
        @empty
          <div class="empty-state" style="padding:2rem 1rem">
            <div class="empty-icon" style="width:48px;height:48px;font-size:1.2rem"><i class="bi bi-clock-history"></i></div>
            <p style="font-size:.82rem;margin:0">No activity yet</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<div class="mt-3">
  <a href="{{ route('procurement.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>
@endsection
