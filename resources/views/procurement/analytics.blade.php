@extends('layouts.app')
@section('title','Procurement Analytics')
@section('page-title','Procurement Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">Procurement Analytics</h1>
    <p class="section-sub">Spending trends, top suppliers, and category breakdown</p>
  </div>
  <a href="{{ route('procurement.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Back to Requests
  </a>
</div>

{{-- Monthly Trend Chart --}}
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-bar-chart-line"></i></div>
      <span>Monthly Spending Trend</span>
    </div>
  </div>
  <div class="panel-body">
    <canvas id="monthlyChart" height="80"></canvas>
  </div>
</div>

<div class="row g-4">
  {{-- Top Suppliers --}}
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#d1fae5;color:#065f46"><i class="bi bi-building"></i></div>
          <span>Top Suppliers</span>
        </div>
      </div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-km">Supplier</span><span class="th-en">Name</span></th>
              <th class="col-right"><span class="th-km">Orders</span><span class="th-en">Count</span></th>
              <th class="col-right"><span class="th-km">Total</span><span class="th-en">Amount</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($bySupplier as $supplier)
              <tr>
                <td style="font-weight:600;font-size:.85rem">{{ $supplier->supplier_name }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.85rem">{{ $supplier->total_orders }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.85rem;font-weight:700;color:var(--primary)">
                  ${{ number_format($supplier->total_amount, 2) }}
                </td>
              </tr>
            @empty
              <tr><td colspan="3">
                <div class="empty-state" style="padding:2rem">
                  <p style="font-size:.82rem;margin:0">No supplier data yet</p>
                </div>
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- By Category --}}
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-grid"></i></div>
          <span>By Category</span>
        </div>
      </div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-km">Category</span><span class="th-en">Type</span></th>
              <th class="col-right"><span class="th-km">Count</span><span class="th-en">Requests</span></th>
              <th class="col-right"><span class="th-km">Total</span><span class="th-en">Amount</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($byCategory as $cat)
              @php
                $catLabels = ['consumable'=>'Consumable','spare_part'=>'Spare Part','component'=>'Component','service'=>'Service','equipment'=>'Equipment','other'=>'Other'];
              @endphp
              <tr>
                <td style="font-weight:600;font-size:.85rem">{{ $catLabels[$cat->category] ?? ucfirst($cat->category) }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.85rem">{{ $cat->total_orders }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.85rem;font-weight:700;color:var(--primary)">
                  ${{ number_format($cat->total_amount, 2) }}
                </td>
              </tr>
            @empty
              <tr><td colspan="3">
                <div class="empty-state" style="padding:2rem">
                  <p style="font-size:.82rem;margin:0">No category data yet</p>
                </div>
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Top Items --}}
  <div class="col-12">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-star"></i></div>
          <span>Top Requested Items</span>
        </div>
      </div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-km">Item</span><span class="th-en">Name</span></th>
              <th><span class="th-km">Category</span><span class="th-en">Type</span></th>
              <th class="col-right"><span class="th-km">Times Ordered</span><span class="th-en">Frequency</span></th>
              <th class="col-right"><span class="th-km">Total Qty</span><span class="th-en">Quantity</span></th>
              <th class="col-right"><span class="th-km">Total Spent</span><span class="th-en">Amount</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($topItems as $item)
              @php
                $catLabels = ['consumable'=>'Consumable','spare_part'=>'Spare Part','component'=>'Component','service'=>'Service','equipment'=>'Equipment','other'=>'Other'];
              @endphp
              <tr>
                <td style="font-weight:700;font-size:.88rem">{{ $item->item_name }}</td>
                <td>
                  <span class="badge badge-binding">{{ $catLabels[$item->category] ?? ucfirst($item->category) }}</span>
                </td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.85rem">{{ $item->order_count }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.85rem">{{ number_format($item->total_qty) }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.88rem;font-weight:700;color:var(--primary)">
                  ${{ number_format($item->total_amount, 2) }}
                </td>
              </tr>
            @empty
              <tr><td colspan="5">
                <div class="empty-state" style="padding:2rem">
                  <p style="font-size:.82rem;margin:0">No item data yet</p>
                </div>
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('monthlyChart');
  if (!ctx || typeof Chart === 'undefined') return;

  const monthly = @json($monthly);
  const labels = monthly.map(m => m.month);
  const amounts = monthly.map(m => parseFloat(m.total_amount));
  const counts = monthly.map(m => parseInt(m.total_orders));

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Spending ($)',
          data: amounts,
          backgroundColor: 'rgba(79, 70, 229, 0.7)',
          borderColor: 'rgba(79, 70, 229, 1)',
          borderWidth: 1,
          borderRadius: 6,
          yAxisID: 'y'
        },
        {
          label: 'Requests',
          data: counts,
          type: 'line',
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: '#10b981',
          fill: true,
          tension: 0.3,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: { position: 'top', labels: { font: { family: "'Poppins', sans-serif", size: 12 } } }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { family: "'Poppins', sans-serif", size: 11 } } },
        y: {
          position: 'left',
          title: { display: true, text: 'Amount ($)', font: { family: "'Poppins', sans-serif" } },
          grid: { color: 'rgba(0,0,0,.05)' },
          ticks: { font: { family: "'Poppins', sans-serif" } }
        },
        y1: {
          position: 'right',
          title: { display: true, text: 'Requests', font: { family: "'Poppins', sans-serif" } },
          grid: { display: false },
          ticks: { stepSize: 1, font: { family: "'Poppins', sans-serif" } }
        }
      }
    }
  });
});
</script>
@endpush
