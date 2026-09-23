@extends('layouts.app')
@section('title', 'Stock Movements - ប្រវត្តិចលនាស្តុក')
@section('page-title', 'Stock Movements')

@section('breadcrumbs')
<div class="breadcrumbs">
  <a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <a href="{{ route('stock.materials.index') }}">Stock</a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <span class="bc-active">Movements History</span>
</div>
@endsection

@section('content')
<style>
  /* ═══════════════════════════════════════════════════════
     STOCK MOVEMENTS CUSTOM MODERN STYLES
  ═══════════════════════════════════════════════════════ */
  .stock-actions .btn {
    font-size: .84rem;
    font-weight: 600;
    padding: .45rem .9rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: all .2s ease;
  }
  .stock-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }
  .btn-record-main {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    border: none;
    box-shadow: 0 4px 14px var(--primary-glow);
  }
  .btn-record-main:hover {
    color: #fff;
    box-shadow: 0 6px 18px rgba(79,70,229,0.35);
  }

  /* KPI Grid */
  .movement-kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
  }
  .kpi-stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .kpi-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
  }
  .kpi-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
  }
  .kpi-stat-val {
    font-family: var(--font-latin);
    font-size: 1.65rem;
    font-weight: 800;
    line-height: 1.1;
    color: var(--text-primary);
  }
  .kpi-stat-lbl {
    font-size: .8rem;
    font-weight: 700;
    color: var(--text-secondary);
    margin-top: .15rem;
  }
  .kpi-stat-sub {
    font-family: var(--font-latin);
    font-size: .72rem;
    color: var(--text-muted);
  }

  /* Type Badges */
  .badge-mv-type {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .28rem .65rem;
    border-radius: 8px;
    font-family: var(--font-latin);
    font-size: .74rem;
    font-weight: 700;
    letter-spacing: .02em;
    text-transform: uppercase;
  }
  .badge-mv-in {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
  }
  .badge-mv-out {
    background: #fff1f2;
    color: #be123c;
    border: 1px solid #fecdd3;
  }
  .badge-mv-adjust {
    background: #f5f3ff;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
  }

  /* Filter toolbar */
  .filter-bar-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
  }
  .quick-filter-btn {
    font-size: .74rem;
    padding: .25rem .65rem;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--surface-2);
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 600;
    transition: all .15s ease;
  }
  .quick-filter-btn:hover,
  .quick-filter-btn.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
  }

  /* User Avatar Pill */
  .user-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: .2rem .6rem .2rem .3rem;
    font-size: .78rem;
    color: var(--text-secondary);
  }
  .user-avatar-circle {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .62rem;
    font-weight: 700;
    color: var(--text-primary);
  }
</style>

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title mb-1">
      <i class="bi bi-arrow-left-right text-primary me-2"></i>Stock Movements
    </h1>
    <p class="section-sub mb-0">ប្រវត្តិចលនាស្តុកទំនិញ · បញ្ចូល (IN) / ដកប្រើប្រាស់ (OUT) / កែតម្រូវ (ADJUST)</p>
  </div>
  <div class="stock-actions d-flex flex-wrap gap-2">
    <a href="{{ route('stock.movements.export', request()->query()) }}" class="btn btn-success" title="ទាញយកជា Excel តាមលក្ខខណ្ឌចម្រាញ់">
      <i class="bi bi-file-earmark-excel"></i> <span>Export Excel</span>
    </a>
    <a href="{{ route('stock.person-report') }}" class="btn btn-outline-secondary" title="របាយការណ៍ដកសម្ភារៈតាមបុគ្គលិក">
      <i class="bi bi-person-badge"></i> <span>Person Report</span>
    </a>
    <a href="{{ route('stock.movements.bulk') }}" class="btn btn-outline-primary" title="បញ្ចូលចលនាច្រើនក្នុងពេលតែមួយ">
      <i class="bi bi-list-columns"></i> <span>Bulk Entry</span>
    </a>
    <a href="{{ route('stock.movements.create') }}" class="btn btn-record-main">
      <i class="bi bi-plus-lg"></i> <span>Record Movement</span>
    </a>
  </div>
</div>

{{-- KPI Metric Summary Cards --}}
<div class="movement-kpis">
  {{-- Total Today --}}
  <div class="kpi-stat-card">
    <div class="kpi-stat-icon" style="background:#eff6ff;color:#2563eb">
      <i class="bi bi-clock-history"></i>
    </div>
    <div>
      <div class="kpi-stat-val">{{ number_format($stats['today_total']) }}</div>
      <div class="kpi-stat-lbl">Today's Activity</div>
      <div class="kpi-stat-sub">ចលនាសរុបថ្ងៃនេះ ({{ now()->format('d/m/Y') }})</div>
    </div>
  </div>

  {{-- Stock In Today --}}
  <div class="kpi-stat-card">
    <div class="kpi-stat-icon" style="background:#ecfdf5;color:#059669">
      <i class="bi bi-arrow-down-left"></i>
    </div>
    <div>
      <div class="kpi-stat-val text-success">+{{ number_format($stats['today_in_qty'], 1) }}</div>
      <div class="kpi-stat-lbl">Stock Received</div>
      <div class="kpi-stat-sub">{{ $stats['today_in'] }} ប្រតិបត្តិការចូល (IN)</div>
    </div>
  </div>

  {{-- Stock Out Today --}}
  <div class="kpi-stat-card">
    <div class="kpi-stat-icon" style="background:#fff1f2;color:#e11d48">
      <i class="bi bi-arrow-up-right"></i>
    </div>
    <div>
      <div class="kpi-stat-val text-danger">-{{ number_format($stats['today_out_qty'], 1) }}</div>
      <div class="kpi-stat-lbl">Stock Consumed</div>
      <div class="kpi-stat-sub">{{ $stats['today_out'] }} ប្រតិបត្តិការដក (OUT)</div>
    </div>
  </div>

  {{-- Adjustments Today --}}
  <div class="kpi-stat-card">
    <div class="kpi-stat-icon" style="background:#f5f3ff;color:#7c3aed">
      <i class="bi bi-sliders"></i>
    </div>
    <div>
      <div class="kpi-stat-val" style="color:#7c3aed">{{ number_format($stats['today_adjust']) }}</div>
      <div class="kpi-stat-lbl">Adjustments</div>
      <div class="kpi-stat-sub">ការកែតម្រូវស្តុកថ្ងៃនេះ</div>
    </div>
  </div>
</div>

{{-- Search & Filter Toolbar --}}
<div class="filter-bar-card">
  <form method="GET" action="{{ route('stock.movements.index') }}" id="movementFilterForm">
    <div class="row g-2 align-items-center">
      {{-- Keyword Search --}}
      <div class="col-md-3 col-sm-6">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
          <input type="text" name="search" class="form-control border-start-0" 
                 placeholder="Search material, ref, staff..." 
                 value="{{ request('search') }}">
        </div>
      </div>

      {{-- Movement Type --}}
      <div class="col-md-2 col-sm-6">
        <select name="type" class="form-select form-select-sm">
          <option value="">-- All Types (គ្រប់ប្រភេទ) --</option>
          <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>🟢 Stock In (នាំចូល)</option>
          <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>🔴 Stock Out (ដកប្រើ)</option>
          <option value="adjust" {{ request('type') === 'adjust' ? 'selected' : '' }}>🟣 Adjust (កែតម្រូវ)</option>
        </select>
      </div>

      {{-- Category Filter --}}
      <div class="col-md-2 col-sm-6">
        <select name="category" class="form-select form-select-sm">
          <option value="">-- All Categories --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
              {{ ucfirst($cat) }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Date Range --}}
      <div class="col-md-3 col-sm-6">
        <div class="input-group input-group-sm">
          <input type="date" name="start_date" class="form-control" title="ចាប់ពីថ្ងៃ" value="{{ request('start_date') }}">
          <span class="input-group-text bg-light text-muted">to</span>
          <input type="date" name="end_date" class="form-control" title="ដល់ថ្ងៃ" value="{{ request('end_date') }}">
        </div>
      </div>

      {{-- Action Buttons --}}
      <div class="col-md-2 col-sm-12 d-flex gap-1 justify-content-end">
        <button type="submit" class="btn btn-sm btn-primary flex-fill">
          <i class="bi bi-funnel-fill"></i> Filter
        </button>
        @if(request()->hasAny(['search', 'type', 'category', 'start_date', 'end_date']))
          <a href="{{ route('stock.movements.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset all filters">
            <i class="bi bi-x-lg"></i>
          </a>
        @endif
      </div>
    </div>

    {{-- Quick Date Preset Pills --}}
    <div class="d-flex align-items-center gap-1 mt-2 pt-2 border-top flex-wrap" style="font-size:.78rem">
      <span class="text-muted me-1"><i class="bi bi-lightning-charge"></i> Quick:</span>
      @php
        $todayStr = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
      @endphp
      <a href="{{ route('stock.movements.index', array_merge(request()->except(['start_date', 'end_date', 'page']), ['start_date' => $todayStr, 'end_date' => $todayStr])) }}" 
         class="quick-filter-btn {{ request('start_date') === $todayStr && request('end_date') === $todayStr ? 'active' : '' }}">
        Today
      </a>
      <a href="{{ route('stock.movements.index', array_merge(request()->except(['start_date', 'end_date', 'page']), ['start_date' => $weekStart, 'end_date' => $weekEnd])) }}" 
         class="quick-filter-btn {{ request('start_date') === $weekStart && request('end_date') === $weekEnd ? 'active' : '' }}">
        This Week
      </a>
      <a href="{{ route('stock.movements.index', array_merge(request()->except(['start_date', 'end_date', 'page']), ['start_date' => $monthStart, 'end_date' => $monthEnd])) }}" 
         class="quick-filter-btn {{ request('start_date') === $monthStart && request('end_date') === $monthEnd ? 'active' : '' }}">
        This Month
      </a>
      @if(request('start_date') || request('end_date'))
        <a href="{{ route('stock.movements.index', request()->except(['start_date', 'end_date', 'page'])) }}" class="quick-filter-btn text-danger">
          Clear Date
        </a>
      @endif
    </div>
  </form>
</div>

{{-- Today's Recent Summary (Collapsible Modern Accordion Widget) --}}
@if($todayMovements->isNotEmpty() && !request()->hasAny(['search', 'type', 'category', 'start_date']))
<div class="panel mb-4 shadow-sm border-success-subtle">
  <div class="panel-header bg-success-subtle py-2 px-3" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#todayMovementsBody" aria-expanded="true">
    <div class="ph-title text-success">
      <div class="ph-icon" style="background:#dcfce7;color:#15803d">
        <i class="bi bi-calendar-check"></i>
      </div>
      <span class="fw-bold" style="font-size:.9rem">សង្ខេបចលនាថ្ងៃនេះ · Today's Activity ({{ now()->format('d/m/Y') }})</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-family:var(--font-latin);font-size:.76rem;font-weight:700">
        {{ $todayMovements->count() }} movements recorded
      </span>
      <i class="bi bi-chevron-down text-muted"></i>
    </div>
  </div>
  <div class="collapse show" id="todayMovementsBody">
    <div class="tbl-wrap" style="max-height:220px;border-radius:0;border:none">
      <table class="data-table mb-0">
        <tbody>
          @foreach($todayMovements as $mv)
            @php 
              $isOut = $mv->type === 'out';
              $isIn = $mv->type === 'in';
            @endphp
            <tr>
              <td style="width:100px;padding:.5rem .8rem">
                <span class="badge-mv-type {{ $isIn ? 'badge-mv-in' : ($isOut ? 'badge-mv-out' : 'badge-mv-adjust') }}">
                  <i class="bi {{ $isIn ? 'bi-arrow-down-left' : ($isOut ? 'bi-arrow-up-right' : 'bi-sliders') }}"></i>
                  {{ strtoupper($mv->type) }}
                </span>
              </td>
              <td style="padding:.5rem .8rem">
                <div class="fw-bold" style="font-size:.86rem">{{ $mv->material->name ?? '—' }}</div>
                @if($mv->material?->name_km)
                  <div style="font-size:.72rem;color:var(--text-secondary)">{{ $mv->material->name_km }}</div>
                @endif
              </td>
              <td style="font-family:var(--font-latin);font-weight:800;font-size:.92rem;text-align:right;padding:.5rem .8rem;
                         color:{{ $isIn ? '#059669' : ($isOut ? '#dc2626' : '#7c3aed') }}">
                {{ $isOut ? '-' : ($isIn ? '+' : '=') }}{{ number_format($mv->quantity, 1) }}
                <span style="font-size:.72rem;font-weight:500;color:var(--text-muted)">{{ $mv->material->unit ?? '' }}</span>
              </td>
              <td style="font-size:.78rem;color:var(--text-muted);padding:.5rem .8rem;width:200px">
                @if($mv->reference)
                  <div><i class="bi bi-hash"></i> {{ $mv->reference }}</div>
                @endif
                @if($mv->performed_by)
                  <div><i class="bi bi-person"></i> {{ $mv->performed_by }}</div>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

{{-- All Movements Main Data Table --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8">
        <i class="bi bi-journal-text"></i>
      </div>
      <span>ប្រវត្តិចលនាទាំងអស់ · Movements Log</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="text-muted" style="font-family:var(--font-latin);font-size:.78rem">
        {{ $movements->total() }} total movements found
      </span>
    </div>
  </div>

  <div class="tbl-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>
            <span class="th-km">កាលបរិច្ឆេទ</span>
            <span class="th-en">Date &amp; Time</span>
          </th>
          <th class="col-center">
            <span class="th-km">ប្រភេទ</span>
            <span class="th-en">Type</span>
          </th>
          <th>
            <span class="th-km">សម្ភារៈ</span>
            <span class="th-en">Material Name</span>
          </th>
          <th class="col-right">
            <span class="th-km">បរិមាណ</span>
            <span class="th-en">Quantity</span>
          </th>
          <th>
            <span class="th-km">លេខយោង &amp; ចំណាំ</span>
            <span class="th-en">Reference &amp; Notes</span>
          </th>
          <th>
            <span class="th-km">កត់ត្រាដោយ</span>
            <span class="th-en">Recorded By</span>
          </th>
        </tr>
      </thead>
      <tbody>
        @forelse($movements as $mv)
          @php 
            $isOut = $mv->type === 'out';
            $isIn = $mv->type === 'in';
            $qtyColor = $isIn ? '#059669' : ($isOut ? '#dc2626' : '#7c3aed');
          @endphp
          <tr>
            {{-- Date & Time --}}
            <td style="white-space:nowrap">
              <div style="font-family:var(--font-latin);font-weight:700;font-size:.85rem;color:var(--text-primary)">
                {{ $mv->movement_date->format('d M Y') }}
              </div>
              <div style="font-family:var(--font-latin);font-size:.72rem;color:var(--text-muted)">
                {{ $mv->created_at ? $mv->created_at->format('h:i A') : $mv->movement_date->format('d/m/Y') }}
              </div>
            </td>

            {{-- Type Badge --}}
            <td style="text-align:center;white-space:nowrap">
              <span class="badge-mv-type {{ $isIn ? 'badge-mv-in' : ($isOut ? 'badge-mv-out' : 'badge-mv-adjust') }}">
                <i class="bi {{ $isIn ? 'bi-arrow-down-left' : ($isOut ? 'bi-arrow-up-right' : 'bi-sliders') }}"></i>
                {{ strtoupper($mv->type) }}
              </span>
            </td>

            {{-- Material Name & Category --}}
            <td>
              <div class="fw-bold" style="font-size:.88rem">
                <a href="{{ route('stock.materials.show', $mv->material_id) }}" class="text-decoration-none text-dark hover-primary">
                  {{ $mv->material->name ?? '—' }}
                </a>
                @if($mv->material?->category)
                  <span class="badge bg-light text-secondary border ms-1" style="font-size:.66rem;font-weight:600;vertical-align:middle">
                    {{ ucfirst($mv->material->category) }}
                  </span>
                @endif
              </div>
              <div class="d-flex align-items-center gap-2 mt-1">
                @if($mv->material?->name_km)
                  <span style="font-size:.75rem;color:var(--text-secondary)">{{ $mv->material->name_km }}</span>
                @endif
                @if($mv->material?->sub_type)
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.66rem;font-family:var(--font-latin)">
                    {{ $mv->material->sub_type }}
                  </span>
                @endif
                @if($mv->material?->code)
                  <span style="font-family:monospace;font-size:.7rem;color:var(--text-muted)">#{{ $mv->material->code }}</span>
                @endif
              </div>
            </td>

            {{-- Quantity & Unit --}}
            <td style="text-align:right;white-space:nowrap">
              <div style="font-family:var(--font-latin);font-weight:800;font-size:1.05rem;color:{{ $qtyColor }}">
                {{ $isOut ? '-' : ($isIn ? '+' : '=') }}{{ number_format($mv->quantity, 1) }}
                <span style="font-size:.75rem;font-weight:500;color:var(--text-secondary)">{{ $mv->material->unit ?? '' }}</span>
              </div>
            </td>

            {{-- Reference & Notes --}}
            <td>
              @if($mv->reference)
                <div style="font-size:.82rem;font-weight:600;color:var(--text-secondary)">
                  <i class="bi bi-file-text text-muted me-1"></i>{{ $mv->reference }}
                </div>
              @endif
              @if($mv->reason)
                <div style="font-size:.74rem;color:var(--text-muted)">
                  <i class="bi bi-tag me-1"></i>{{ $mv->reason }}
                </div>
              @endif
              @if($mv->notes)
                <div style="font-size:.74rem;color:var(--text-muted);font-style:italic">
                  <i class="bi bi-chat-left-dots me-1"></i>{{ $mv->notes }}
                </div>
              @endif
              @if(!$mv->reference && !$mv->reason && !$mv->notes)
                <span class="text-muted" style="font-size:.8rem">—</span>
              @endif
            </td>

            {{-- Recorded By --}}
            <td style="white-space:nowrap">
              @if($mv->performed_by)
                <span class="user-badge-pill">
                  <span class="user-avatar-circle">
                    {{ strtoupper(mb_substr($mv->performed_by, 0, 1)) }}
                  </span>
                  <span>{{ $mv->performed_by }}</span>
                </span>
              @else
                <span class="text-muted" style="font-size:.8rem">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-5">
              <div class="empty-box my-4">
                <i class="bi bi-inbox text-muted" style="font-size:2.8rem"></i>
                <div class="empty-text mt-2 fw-bold text-secondary" style="font-size:.95rem">
                  មិនមានទិន្នន័យចលនា Stock ត្រូវបានរកឃើញទេ
                </div>
                <p class="text-muted" style="font-size:.82rem">
                  សូមសាកល្បងផ្លាស់ប្តូរលក្ខខណ្ឌចម្រាញ់ ឬកត់ត្រាចលនាថ្មី។
                </p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                  @if(request()->hasAny(['search', 'type', 'category', 'start_date', 'end_date']))
                    <a href="{{ route('stock.movements.index') }}" class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-arrow-repeat"></i> Clear Filters
                    </a>
                  @endif
                  <a href="{{ route('stock.movements.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> Record Movement
                  </a>
                </div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($movements->hasPages())
    <div class="panel-body border-top py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="text-muted" style="font-size:.82rem">
        Showing <strong>{{ $movements->firstItem() }}</strong> to <strong>{{ $movements->lastItem() }}</strong> of <strong>{{ $movements->total() }}</strong> records
      </div>
      <div>
        {{ $movements->links() }}
      </div>
    </div>
  @endif
</div>
@endsection
