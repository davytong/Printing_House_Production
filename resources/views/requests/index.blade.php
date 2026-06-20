@extends('layouts.app')
@section('title','ស្នើរសុំបោះពុម្ព')
@section('page-title','Print Requests')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">ស្នើរសុំបោះពុម្ព</h1>
    <p class="section-sub">គ្រប់គ្រង និងអនុម័តការស្នើរសុំ</p>
  </div>
  <a href="{{ route('requests.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> ស្នើរសុំថ្មី
  </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
  @foreach([
    ['pending','រង់ចាំ','kpi-amber','bi-hourglass'],
    ['approved','បានអនុម័ត','kpi-green','bi-check-circle'],
    ['in_production','កំពុងផលិត','kpi-blue','bi-printer'],
    ['completed','រួចរាល់','kpi-purple','bi-trophy'],
    ['rejected','បដិសេធ','kpi-rose','bi-x-circle'],
  ] as [$key,$label,$cls,$icon])
  <div class="col-6 col-lg">
    <div class="kpi-card {{ $cls }}" style="padding:1rem 1.1rem;gap:.5rem">
      <div class="kpi-icon" style="width:36px;height:36px;font-size:1rem"><i class="bi {{ $icon }}"></i></div>
      <div>
        <div class="kpi-value" style="font-size:1.6rem">{{ $stats[$key] ?? 0 }}</div>
        <div class="kpi-label" style="font-size:.75rem">{{ $label }}</div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- Table --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-list-ul"></i></div>
      <span>បញ្ជីស្នើរសុំ</span>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th><span class="th-km">លេខ</span><span class="th-en">Code</span></th>
          <th><span class="th-km">ចំណងជើង</span><span class="th-en">Title</span></th>
          <th><span class="th-km">អ្នកស្នើ</span><span class="th-en">Requester</span></th>
          <th class="col-center"><span class="th-km">អាទិភាព</span><span class="th-en">Priority</span></th>
          <th class="col-right"><span class="th-km">ចំណងជើង</span><span class="th-en">Books</span></th>
          <th class="col-right"><span class="th-km">ចំនួន</span><span class="th-en">Total Qty</span></th>
          <th><span class="th-km">កំណត់ថ្ងៃ</span><span class="th-en">Required By</span></th>
          <th class="col-center"><span class="th-km">ស្ថានភាព</span><span class="th-en">Status</span></th>
          <th class="col-center"><span class="th-km">សកម្មភាព</span><span class="th-en">Action</span></th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $req)
          @php
            $priMap = ['low'=>['badge-binding','ទាប'],'normal'=>['badge-staple','ធម្មតា'],'high'=>['badge-progress','ខ្ពស់'],'urgent'=>['badge-pending','បន្ទាន់']];
            $stMap  = ['pending'=>'badge-progress','approved'=>'badge-done','rejected'=>'badge-pending','in_production'=>'badge-binding','completed'=>'badge-done','cancelled'=>'badge-pending'];
            [$priClass,$priLbl] = $priMap[$req->priority] ?? ['badge-binding','ធម្មតា'];
            $stClass  = $stMap[$req->status] ?? 'badge-binding';
            $bookCount= $req->total_books_requested ?? $req->items_count ?? 1;
            $hasAtts  = !empty($req->attachments);
          @endphp
          <tr>
            {{-- Code --}}
            <td style="font-family:var(--font-latin);font-size:.8rem;font-weight:700;color:var(--primary)">
              <a href="{{ route('requests.show',$req) }}" style="color:inherit;text-decoration:none">
                {{ $req->request_code }}
              </a>
              @if($hasAtts)
                <i class="bi bi-paperclip ms-1" style="color:var(--text-muted);font-size:.72rem"
                   title="{{ count($req->attachments) }} file(s)"></i>
              @endif
            </td>
            {{-- Title --}}
            <td>
              <div style="font-weight:700;font-size:.88rem">{{ $req->title }}</div>
            </td>
            {{-- Requester --}}
            <td>
              <div style="font-size:.85rem;font-weight:600">{{ $req->requester_name }}</div>
              @if($req->department)
                <div style="font-size:.72rem;color:var(--text-muted)">{{ $req->department }}</div>
              @endif
            </td>
            {{-- Priority --}}
            <td style="text-align:center">
              <span class="badge {{ $priClass }}">{{ $priLbl }}</span>
            </td>
            {{-- Book count --}}
            <td style="text-align:right;font-family:var(--font-latin);font-weight:700;color:var(--primary)">
              {{ $bookCount }}
              <span style="font-weight:400;font-size:.72rem;color:var(--text-muted)">ចំណង</span>
            </td>
            {{-- Total qty --}}
            <td style="text-align:right;font-family:var(--font-latin);font-weight:600">
              {{ number_format($req->quantity_requested) }}
            </td>
            {{-- Required by --}}
            <td style="font-family:var(--font-latin);font-size:.82rem;
                       {{ $req->isOverdue()?'color:var(--danger);font-weight:700':'color:var(--text-muted)' }}">
              {{ $req->required_by?->format('d/m/Y') ?? '—' }}
              @if($req->isOverdue())
                <i class="bi bi-exclamation-circle ms-1"></i>
              @endif
            </td>
            {{-- Status --}}
            <td style="text-align:center">
              <span class="badge {{ $stClass }}">{{ $req->status }}</span>
            </td>
            {{-- Actions --}}
            <td style="text-align:center">
              <div class="d-flex gap-1 justify-content-center">
                <a href="{{ route('requests.show',$req) }}" class="btn btn-ghost btn-sm" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                @if(in_array($req->status,['pending','approved']))
                  <a href="{{ route('requests.edit',$req) }}" class="btn btn-ghost btn-sm" title="Edit">
                    <i class="bi bi-pencil" style="color:var(--primary)"></i>
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9">
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-inbox"></i></div>
              <p style="font-weight:600;margin:0">មិនទាន់មានស្នើរសុំ</p>
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($requests->hasPages())
    <div class="panel-body" style="padding:.75rem 1.5rem">{{ $requests->links() }}</div>
  @endif
</div>
@endsection
