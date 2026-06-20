@extends('layouts.app')
@section('title','អ្នកផ្គត់ផ្គង់')
@section('page-title','Supplier Management')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">អ្នកផ្គត់ផ្គង់</h1>
    <p class="section-sub">គ្រប់គ្រងព័ត៌មានអ្នកផ្គត់ផ្គង់ទំនិញ</p>
  </div>
  <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> បន្ថែម
  </a>
</div>

<div class="panel">
  <div class="tbl-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th><span class="th-km">លេខ</span><span class="th-en">Code</span></th>
          <th><span class="th-km">ឈ្មោះ</span><span class="th-en">Name</span></th>
          <th><span class="th-km">ទំនាក់ទំនង</span><span class="th-en">Contact</span></th>
          <th><span class="th-km">ប្រភេទ</span><span class="th-en">Supply Type</span></th>
          <th class="col-right"><span class="th-km">ការបញ្ជា</span><span class="th-en">Orders</span></th>
          <th class="col-center"><span class="th-km">ស្ថានភាព</span><span class="th-en">Status</span></th>
          <th class="col-center"><span class="th-km">សកម្មភាព</span><span class="th-en">Action</span></th>
        </tr>
      </thead>
      <tbody>
        @forelse($suppliers as $s)
          <tr>
            <td style="font-family:var(--font-latin);font-size:.8rem;font-weight:600;color:var(--primary)">{{ $s->code }}</td>
            <td>
              <div style="font-weight:700">{{ $s->name }}</div>
              @if($s->contact_person)<div style="font-size:.75rem;color:var(--text-muted)">{{ $s->contact_person }}</div>@endif
            </td>
            <td>
              @if($s->phone)<div style="font-size:.82rem;font-family:var(--font-latin)"><i class="bi bi-telephone me-1"></i>{{ $s->phone }}</div>@endif
              @if($s->email)<div style="font-size:.78rem;color:var(--text-muted);font-family:var(--font-latin)">{{ $s->email }}</div>@endif
            </td>
            <td><span class="badge badge-binding">{{ $s->supply_type ?? '—' }}</span></td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:600">{{ $s->purchase_orders_count }}</td>
            <td style="text-align:center">
              <span class="badge {{ $s->status==='active' ? 'badge-done' : 'badge-pending' }}">
                {{ $s->status==='active' ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td style="text-align:center">
              <div class="d-flex gap-1 justify-content-center">
                <a href="{{ route('suppliers.show', $s) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
                <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i></a>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7">
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-building"></i></div>
              <p style="font-weight:600;margin:0">មិនទាន់មានអ្នកផ្គត់ផ្គង់</p>
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($suppliers->hasPages())
    <div class="panel-body">{{ $suppliers->links() }}</div>
  @endif
</div>
@endsection
