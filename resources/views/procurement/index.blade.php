@extends('layouts.app')
@section('title','Procurement')
@section('page-title','Procurement Requests')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">Procurement Requests</h1>
    <p class="section-sub">Track purchase requests, approvals, and deliveries</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('procurement.quick-entry') }}" class="btn btn-success btn-sm">
      <i class="bi bi-list-columns"></i> Quick Entry
    </a>
    <a href="{{ route('procurement.analytics') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-bar-chart-line"></i> Analytics
    </a>
    <a href="{{ route('procurement.create') }}" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg"></i> New Request
    </a>
  </div>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-blue">
      <div class="kpi-icon"><i class="bi bi-receipt"></i></div>
      <div class="kpi-value">{{ $stats['total'] ?? 0 }}</div>
      <div class="kpi-label">Total Requests</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-amber">
      <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
      <div class="kpi-value">{{ $stats['pending'] ?? 0 }}</div>
      <div class="kpi-label">Pending</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-green">
      <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
      <div class="kpi-value">{{ $stats['completed'] ?? 0 }}</div>
      <div class="kpi-label">Completed</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-purple">
      <div class="kpi-icon"><i class="bi bi-currency-dollar"></i></div>
      <div class="kpi-value">${{ number_format($stats['month_value'] ?? 0, 0) }}</div>
      <div class="kpi-label">This Month Value</div>
    </div>
  </div>
</div>

{{-- Filter Bar --}}
<div class="panel mb-4">
  <div class="panel-body" style="padding:1rem 1.5rem">
    <form method="GET" action="{{ route('procurement.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
      <select name="status" class="form-select form-select-sm" style="width:auto">
        <option value="">All Status</option>
        @foreach(['pending','approved','ordered','received','completed','cancelled'] as $s)
          <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
      <select name="category" class="form-select form-select-sm" style="width:auto">
        <option value="">All Categories</option>
        @foreach(['consumable'=>'Consumable','spare_part'=>'Spare Part','component'=>'Component','service'=>'Service','equipment'=>'Equipment','other'=>'Other'] as $v=>$l)
          <option value="{{ $v }}" {{ request('category')===$v?'selected':'' }}>{{ $l }}</option>
        @endforeach
      </select>
      <div class="flex-grow-1" style="min-width:180px;max-width:300px">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search supplier, item..."
               value="{{ request('search') }}">
      </div>
      <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel"></i> Filter</button>
      @if(request()->hasAny(['status','category','search']))
        <a href="{{ route('procurement.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-x-lg"></i> Clear</a>
      @endif
    </form>
  </div>
</div>

{{-- Data Table --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-receipt"></i></div>
      <span>Requests</span>
      <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ $requests->total() }}</span>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th><span class="th-km">#</span><span class="th-en">Number</span></th>
          <th><span class="th-km">Date</span><span class="th-en">Requested</span></th>
          <th><span class="th-km">Supplier</span><span class="th-en">Vendor</span></th>
          <th><span class="th-km">Item</span><span class="th-en">Description</span></th>
          <th class="col-right"><span class="th-km">Qty</span><span class="th-en">Amount</span></th>
          <th class="col-center"><span class="th-km">Priority</span><span class="th-en">Level</span></th>
          <th class="col-center"><span class="th-km">Status</span><span class="th-en">State</span></th>
          <th class="col-center"><span class="th-km"><i class="bi bi-paperclip"></i></span><span class="th-en">Files</span></th>
          <th class="col-center"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $req)
          @php
            $priorityColors = ['low'=>'var(--success)','medium'=>'var(--warning)','high'=>'#f97316','urgent'=>'var(--danger)'];
            $statusBadge = match($req->status) {
              'pending'=>'badge-pending','approved'=>'badge-binding','ordered'=>'badge-progress',
              'received'=>'badge-staple','completed'=>'badge-done','cancelled'=>'badge-pending',
              default=>'badge-progress'
            };
          @endphp
          <tr>
            <td style="font-family:var(--font-latin);font-size:.78rem;font-weight:600;color:var(--primary)">
              {{ $req->request_number }}
            </td>
            <td style="font-family:var(--font-latin);font-size:.82rem">
              {{ $req->request_date?->format('d M Y') }}
            </td>
            <td>
              <div style="font-weight:600;font-size:.85rem">{{ $req->supplier_name }}</div>
            </td>
            <td>
              <div style="font-weight:600;font-size:.85rem">{{ Str::limit($req->item_name, 30) }}</div>
              <div style="font-size:.72rem;color:var(--text-muted)">{{ $req->categoryLabel() }}</div>
            </td>
            <td style="text-align:right;font-family:var(--font-latin);font-size:.82rem">
              <div style="font-weight:700">{{ $req->quantity }} {{ $req->unit }}</div>
              <div style="font-size:.72rem;color:var(--text-muted)">${{ number_format($req->total_amount, 2) }}</div>
            </td>
            <td style="text-align:center">
              <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $priorityColors[$req->priority] ?? 'var(--text-muted)' }}"
                    title="{{ ucfirst($req->priority) }}"></span>
            </td>
            <td style="text-align:center">
              <span class="badge {{ $statusBadge }}">{{ ucfirst($req->status) }}</span>
            </td>
            <td style="text-align:center;font-family:var(--font-latin);font-size:.8rem;color:var(--text-muted)">
              @if($req->attachments_count > 0)
                <i class="bi bi-paperclip"></i> {{ $req->attachments_count }}
              @endif
            </td>
            <td style="text-align:center">
              <a href="{{ route('procurement.show', $req) }}" class="btn btn-ghost btn-sm" title="View">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="9">
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-receipt"></i></div>
              <p style="font-weight:600;margin:0">No procurement requests found</p>
              <a href="{{ route('procurement.create') }}" class="btn btn-primary btn-sm mt-2">
                <i class="bi bi-plus-lg"></i> Create First Request
              </a>
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($requests->hasPages())
    <div class="panel-body" style="padding:.75rem 1.5rem;border-top:1px solid var(--border)">
      {{ $requests->withQueryString()->links() }}
    </div>
  @endif
</div>
@endsection
