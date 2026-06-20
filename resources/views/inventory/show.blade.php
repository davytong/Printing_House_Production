@extends('layouts.app')
@section('title',$inventoryItem->name)
@section('page-title','Inventory Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">{{ $inventoryItem->name }}</h1>
    <p class="section-sub" style="font-family:var(--font-latin)">{{ $inventoryItem->code }}</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adjustModal">
      <i class="bi bi-arrow-left-right"></i> កែ Stock
    </button>
    <a href="{{ route('inventory.edit', $inventoryItem) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
  </div>
</div>

<div class="row g-4">
  {{-- Info card --}}
  <div class="col-lg-4">
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-info-circle"></i></div>
          <span>ព័ត៌មាន</span>
        </div>
      </div>
      <div class="panel-body d-flex flex-column gap-3">
        @php
          $typeMap = ['paper'=>'ក្រដាស','ink'=>'ថ្នាំ','plate'=>'Plate','spare_part'=>'Spare Parts','chemical'=>'Chemical','other'=>'ផ្សេងៗ'];
          $isLow   = $inventoryItem->isLowStock();
        @endphp
        {{-- Stock gauge --}}
        <div style="text-align:center;padding:1rem 0">
          <div style="font-family:var(--font-latin);font-size:2.5rem;font-weight:800;
            color:{{ $inventoryItem->quantity_in_stock<=0?'var(--danger)':($isLow?'var(--warning)':'var(--success)') }}">
            {{ number_format($inventoryItem->quantity_in_stock,1) }}
          </div>
          <div style="font-size:.85rem;color:var(--text-muted)">{{ $inventoryItem->unit }} in stock</div>
          @if($isLow)
            <div style="margin-top:.5rem;padding:.35rem .75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:999px;display:inline-block;font-size:.75rem;color:#92400e;font-weight:600">
              <i class="bi bi-exclamation-triangle me-1"></i> Stock ទាប — Min: {{ number_format($inventoryItem->minimum_stock,1) }}
            </div>
          @endif
        </div>
        <div class="divider" style="margin:0"></div>
        @foreach([
          ['ប្រភេទ', $typeMap[$inventoryItem->type]??$inventoryItem->type],
          ['ឯកតា', $inventoryItem->unit, true],
          ['តម្លៃ/Unit', '$'.number_format($inventoryItem->unit_cost,2), true],
          ['តម្លៃសរុប', '$'.number_format($inventoryItem->totalValue(),2), true],
          ['ទីតាំង', $inventoryItem->location??'—'],
          ['អ្នកផ្គត់ផ្គង់', $inventoryItem->supplier?->name??'—'],
        ] as [$lbl,$val,$latin??false])
          <div>
            <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">{{ $lbl }}</div>
            <div style="font-size:.9rem;margin-top:.1rem;{{ $latin?'font-family:var(--font-latin)':'' }}">{{ $val }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Transaction history --}}
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-clock-history"></i></div>
          <span>ប្រវត្តិ Stock</span>
        </div>
      </div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-km">ថ្ងៃ</span><span class="th-en">Date</span></th>
              <th class="col-center"><span class="th-km">ប្រភេទ</span><span class="th-en">Type</span></th>
              <th class="col-right"><span class="th-km">ចំនួន</span><span class="th-en">Qty</span></th>
              <th class="col-right"><span class="th-km">មុន</span><span class="th-en">Before</span></th>
              <th class="col-right"><span class="th-km">ក្រោយ</span><span class="th-en">After</span></th>
              <th><span class="th-km">ឯកសារ</span><span class="th-en">Reference</span></th>
              <th><span class="th-km">អ្នកធ្វើ</span><span class="th-en">By</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($transactions as $t)
              @php
                $tColor = $t->type==='in'?'var(--success)':($t->type==='out'?'var(--danger)':'var(--primary)');
                $tSign  = $t->type==='in'?'+':($t->type==='out'?'-':'±');
              @endphp
              <tr>
                <td style="font-family:var(--font-latin);font-size:.8rem">{{ $t->transacted_at->format('d/m/Y H:i') }}</td>
                <td style="text-align:center">
                  <span style="font-family:var(--font-latin);font-size:.72rem;font-weight:700;
                    padding:.2em .6em;border-radius:6px;
                    background:{{ $t->type==='in'?'var(--success-light)':($t->type==='out'?'var(--danger-light)':'#ede9fe') }};
                    color:{{ $t->type==='in'?'var(--success-dark)':($t->type==='out'?'var(--danger-dark)':'#5b21b6') }}">
                    {{ strtoupper($t->type) }}
                  </span>
                </td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:700;color:{{ $tColor }}">
                  {{ $tSign }}{{ number_format($t->quantity,1) }}
                </td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.82rem;color:var(--text-muted)">{{ number_format($t->quantity_before,1) }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.82rem;font-weight:600">{{ number_format($t->quantity_after,1) }}</td>
                <td style="font-size:.8rem;color:var(--text-secondary)">{{ $t->reference ?? '—' }}</td>
                <td style="font-size:.8rem;color:var(--text-muted)">{{ $t->performed_by ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="7" style="text-align:center;padding:1.5rem;color:var(--text-muted)">មិនទាន់មានប្រវត្តិ</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($transactions->hasPages())<div class="panel-body">{{ $transactions->links() }}</div>@endif
    </div>
  </div>
</div>

{{-- Adjust Stock Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form action="{{ route('inventory.adjust', $inventoryItem) }}" method="POST">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">កែ Stock — {{ $inventoryItem->name }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">ប្រភេទ *</label>
              <select name="type" class="form-select" required>
                <option value="in">IN — ទទួល</option>
                <option value="out">OUT — ប្រើប្រាស់</option>
                <option value="adjustment">ADJUSTMENT</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">ចំនួន *</label>
              <input type="number" name="quantity" class="form-control" min="0.01" step="0.01" style="font-family:var(--font-latin)" required>
            </div>
            <div class="col-6">
              <label class="form-label">ឯកសារ (Reference)</label>
              <input type="text" name="reference" class="form-control" placeholder="PO-2026-0001...">
            </div>
            <div class="col-6">
              <label class="form-label">ធ្វើដោយ</label>
              <input type="text" name="performed_by" class="form-control" placeholder="ឈ្មោះ">
            </div>
            <div class="col-12">
              <label class="form-label">កំណត់ចំណាំ</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> រក្សាទុក</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
