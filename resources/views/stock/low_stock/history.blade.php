@extends('layouts.app')
@section('title', 'ប្រវត្តិផ្ញើសារស្តុកជិតអស់')
@section('page-title', 'Low Stock Notification History')

@section('breadcrumbs')
<div class="breadcrumbs">
  <a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <a href="{{ route('stock.movements.index') }}">Stock</a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <span class="bc-active">Notification History</span>
</div>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">
      <i class="bi bi-clock-history text-warning me-2"></i>
      <span>ប្រវត្តិផ្ញើសារស្តុកជិតអស់ទៅអ្នកដឹកនាំ</span>
    </h1>
    <p class="section-sub">Low Stock Notification History &amp; Delivery Tracking</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('stock.low-stock.settings') }}" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-gear-fill me-1"></i> ការកំណត់ Low Stock
    </a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success d-flex align-items-center mb-3">
    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger d-flex align-items-center mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <div>{{ session('error') }}</div>
  </div>
@endif

{{-- Filter panel --}}
<div class="panel mb-4">
  <div class="panel-body p-3">
    <form method="GET" action="{{ route('stock.low-stock.history') }}" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label fs-8 text-muted">កាលបរិច្ឆេទ (Date)</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label fs-8 text-muted">ស្ថានភាព (Status)</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">-- All Statuses --</option>
          <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>SENT (បានផ្ញើ)</option>
          <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>FAILED (បរាជ័យ)</option>
          <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING (កំពុងរង់ចាំ)</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-filter me-1"></i> Filter
        </button>
        <a href="{{ route('stock.low-stock.history') }}" class="btn btn-outline-secondary btn-sm">
          Reset
        </a>
      </div>
    </form>
  </div>
</div>

{{-- Notifications Table --}}
<div class="panel">
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
        <thead class="table-light">
          <tr>
            <th>កាលបរិច្ឆេទ</th>
            <th>ផ្នែក (Category)</th>
            <th>ទំនិញ (Items)</th>
            <th>ក្រុមទទួល (Destination)</th>
            <th>អ្នករាយការណ៍</th>
            <th>ស្ថានភាព</th>
            <th>កាលបរិច្ឆេទផ្ញើ</th>
            <th class="text-end">សកម្មភាព</th>
          </tr>
        </thead>
        <tbody>
          @forelse($notifications as $n)
            <tr>
              <td class="fw-bold" style="font-family:var(--font-latin)">
                {{ $n->report_date->format('d/m/Y') }}
              </td>
              <td>
                <span class="badge bg-light text-dark border">
                  {{ ucfirst($n->category ?? 'All') }}
                </span>
              </td>
              <td>
                <span class="fw-bold text-danger">{{ $n->items_count }}</span> items
              </td>
              <td>
                <i class="bi bi-telegram text-primary me-1"></i>
                <span class="fw-bold">{{ $n->destination_group_name ?? 'Leader Group' }}</span>
              </td>
              <td>
                <i class="bi bi-person-fill text-muted me-1"></i>
                {{ $n->sent_by ?? 'Reporter' }}
              </td>
              <td>
                {!! $n->statusBadge() !!}
              </td>
              <td style="font-family:var(--font-latin);font-size:.82rem" class="text-muted">
                {{ $n->sent_at ? $n->sent_at->format('d/m/Y H:i') : '-' }}
              </td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewNotificationDetails({{ $n->id }})">
                  <i class="bi bi-eye-fill"></i> មើល
                </button>
                @if($n->status === 'FAILED')
                  <form action="{{ route('stock.low-stock.retry', $n->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('តើអ្នកចង់ផ្ញើសារនេះឡើងវិញដែរឬទេ?')">
                      <i class="bi bi-arrow-clockwise"></i> ផ្ញើឡើងវិញ
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                មិនទាន់មានប្រវត្តិផ្ញើសារស្តុកជិតអស់នៅឡើយទេ (No history records)
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($notifications->hasPages())
    <div class="panel-footer p-3">
      {{ $notifications->links() }}
    </div>
  @endif
</div>

{{-- Detail View Modal --}}
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i class="bi bi-envelope-paper-fill text-primary"></i>
          <span>ព័ត៌មានលម្អិតអំពីការផ្ញើសារស្តុកជិតអស់</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="p-2 border rounded bg-light">
              <span class="text-muted fs-8 d-block">ក្រុមទទួល (Destination)</span>
              <strong id="modalGroup" class="fs-7 text-primary"></strong>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-2 border rounded bg-light">
              <span class="text-muted fs-8 d-block">អ្នករាយការណ៍ (Sent By)</span>
              <strong id="modalSentBy" class="fs-7"></strong>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-2 border rounded bg-light">
              <span class="text-muted fs-8 d-block">ស្ថានភាព (Status)</span>
              <div id="modalStatusBadge"></div>
            </div>
          </div>
        </div>

        <div id="modalErrorAlert" class="alert alert-danger d-none mb-3">
          <i class="bi bi-exclamation-octagon-fill me-1"></i>
          <span id="modalErrorMessage"></span>
        </div>

        <label class="form-label fw-bold"><i class="bi bi-chat-left-text me-1"></i> សារដែលបានផ្ញើ (Message Content)</label>
        <div id="modalMessageContent" class="p-3 border rounded font-monospace bg-light fs-8" style="white-space:pre-wrap;max-height:300px;overflow-y:auto">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បិទ (Close)</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function viewNotificationDetails(id) {
  fetch(`{{ url('/stock/low-stock/notifications') }}/${id}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('modalGroup').textContent = data.destination_group_name || 'Leader Group';
      document.getElementById('modalSentBy').textContent = data.sent_by || 'Reporter';
      document.getElementById('modalStatusBadge').innerHTML = data.status_badge;
      document.getElementById('modalMessageContent').innerHTML = data.message;

      const errAlert = document.getElementById('modalErrorAlert');
      if (data.status === 'FAILED' && data.error_message) {
        errAlert.classList.remove('d-none');
        document.getElementById('modalErrorMessage').textContent = data.error_message;
      } else {
        errAlert.classList.add('d-none');
      }

      const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
      modal.show();
    })
    .catch(err => alert('Unable to load details: ' + err));
}
</script>
@endpush
