@extends('layouts.app')
@section('title','ការបញ្ជាទិញ')
@section('page-title','Purchase Orders')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">ការបញ្ជាទិញ (PO)</h1>
    <p class="section-sub">គ្រប់គ្រង Purchase Orders និងការទទួលទំនិញ</p>
  </div>
  <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> PO ថ្មី</a>
</div>

<div class="row g-3 mb-4">
  @foreach([
    ['draft','Draft','kpi-purple','bi-file-earmark'],
    ['sent','Sent','kpi-blue','bi-send'],
    ['partial','Received Part','kpi-amber','bi-box-arrow-in-down'],
    ['received','Received','kpi-green','bi-check-all'],
    ['overdue','Overdue','kpi-rose','bi-exclamation-circle'],
  ] as [$k,$l,$cls,$ic])
  <div class="col-6 col-lg">
    <div class="kpi-card {{ $cls }}" style="padding:1rem;gap:.4rem">
      <div class="kpi-icon" style="width:34px;height:34px;font-size:.9rem"><i class="bi {{ $ic }}"></i></div>
      <div><div class="kpi-value" style="font-size:1.5rem">{{ $stats[$k] }}</div><div class="kpi-label" style="font-size:.72rem">{{ $l }}</div></div>
    </div>
  </div>
  @endforeach
</div>

<div class="panel">
  <div class="tbl-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th><span class="th-km">PO</span><span class="th-en">Number</span></th>
          <th><span class="th-km">អ្នកផ្គត់ផ្គង់</span><span class="th-en">Supplier</span></th>
          <th><span class="th-km">ថ្ងៃបញ្ជា</span><span class="th-en">Order Date</span></th>
          <th><span class="th-km">ត្រូវទទួល</span><span class="th-en">Expected</span></th>
          <th class="col-right"><span class="th-km">តម្លៃ</span><span class="th-en">Amount</span></th>
          <th class="col-center"><span class="th-km">ស្ថានភាព</span><span class="th-en">Status</span></th>
          <th class="col-center"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $po)
          @php
            $stMap=['draft'=>'badge-staple','sent'=>'badge-binding','partially_received'=>'badge-progress','received'=>'badge-done','cancelled'=>'badge-pending'];
            $overdue = $po->isOverdue();
          @endphp
          <tr>
            <td style="font-family:var(--font-latin);font-weight:700;color:var(--primary)">
              <a href="{{ route('purchase-orders.show',$po) }}" style="color:inherit;text-decoration:none">{{ $po->po_number }}</a>
            </td>
            <td style="font-weight:600">{{ $po->supplier->name }}</td>
            <td style="font-family:var(--font-latin);font-size:.82rem">{{ $po->order_date->format('d/m/Y') }}</td>
            <td style="font-family:var(--font-latin);font-size:.82rem;
              {{ $overdue?'color:var(--danger);font-weight:700':'color:var(--text-muted)' }}">
              {{ $po->expected_date?->format('d/m/Y') ?? '—' }}
              @if($overdue)<i class="bi bi-exclamation-circle ms-1"></i>@endif
            </td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:600">
              {{ number_format($po->total_amount,2) }} {{ $po->currency }}
            </td>
            <td style="text-align:center">
              <span class="badge {{ $stMap[$po->status]??'badge-binding' }}" style="font-family:var(--font-latin);font-size:.7rem">{{ $po->status }}</span>
            </td>
            <td style="text-align:center">
              <a href="{{ route('purchase-orders.show',$po) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon"><i class="bi bi-cart3"></i></div><p style="font-weight:600;margin:0">មិនទាន់មាន PO</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($orders->hasPages())<div class="panel-body">{{ $orders->links() }}</div>@endif
</div>
@endsection
