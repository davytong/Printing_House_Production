@extends('layouts.app')
@section('title',$supplier->name)
@section('page-title','Supplier Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">{{ $supplier->name }}</h1>
    <p class="section-sub" style="font-family:var(--font-latin)">{{ $supplier->code }}</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> កែប្រែ</a>
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-header"><div class="ph-title"><div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-building"></i></div><span>ព័ត៌មានទំនាក់ទំនង</span></div></div>
      <div class="panel-body d-flex flex-column gap-3">
        @foreach([
          ['label'=>'ឈ្មោះ','value'=>$supplier->name],
          ['label'=>'អ្នកទំនាក់ទំនង','value'=>$supplier->contact_person??'—'],
          ['label'=>'ទូរស័ព្ទ','value'=>$supplier->phone??'—','latin'=>true],
          ['label'=>'Email','value'=>$supplier->email??'—','latin'=>true],
          ['label'=>'ប្រភេទ','value'=>$supplier->supply_type??'—'],
          ['label'=>'ស្ថានភាព','value'=>$supplier->status],
        ] as $f)
          <div>
            <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">{{ $f['label'] }}</div>
            <div style="font-size:.9rem;margin-top:.15rem;{{ isset($f['latin'])?'font-family:var(--font-latin)':'' }}">{{ $f['value'] }}</div>
          </div>
        @endforeach
        @if($supplier->address)
          <div>
            <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">អាសយដ្ឋាន</div>
            <div style="font-size:.85rem;margin-top:.15rem;color:var(--text-secondary)">{{ $supplier->address }}</div>
          </div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-header"><div class="ph-title"><div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-cart3"></i></div><span>ការបញ្ជាទិញចុងក្រោយ</span></div></div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-km">PO</span><span class="th-en">Number</span></th>
              <th><span class="th-km">ថ្ងៃ</span><span class="th-en">Date</span></th>
              <th class="col-right"><span class="th-km">តម្លៃ</span><span class="th-en">Amount</span></th>
              <th class="col-center"><span class="th-km">ស្ថានភាព</span><span class="th-en">Status</span></th>
            </tr>
          </thead>
          <tbody>
            @forelse($supplier->purchaseOrders as $po)
              <tr>
                <td style="font-family:var(--font-latin);font-weight:600;color:var(--primary)">
                  <a href="{{ route('purchase-orders.show',$po) }}" style="color:inherit">{{ $po->po_number }}</a>
                </td>
                <td style="font-family:var(--font-latin);font-size:.82rem">{{ $po->order_date->format('d/m/Y') }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:600">
                  {{ number_format($po->total_amount,2) }} {{ $po->currency }}
                </td>
                <td style="text-align:center"><span class="badge badge-binding" style="font-family:var(--font-latin);font-size:.7rem">{{ $po->status }}</span></td>
              </tr>
            @empty
              <tr><td colspan="4" style="text-align:center;padding:1.5rem;color:var(--text-muted)">មិនទាន់មានការបញ្ជា</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
