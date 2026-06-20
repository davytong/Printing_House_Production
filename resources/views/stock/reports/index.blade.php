@extends('layouts.app')
@section('title','Stock Reports')
@section('page-title','Stock Reports')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">រាយការណ៍ Stock</h1>
    <p class="section-sub">បង្កើតរបាយការណ៍ Stock ប្រចាំថ្ងៃ + ផ្ញើ Telegram</p>
  </div>
  <a href="{{ route('stock.reports.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> បង្កើតរបាយការណ៍</a>
</div>

{{-- Category filter — view reports per responsible section --}}
<div class="panel mb-4">
  <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:.6rem;align-items:center">
    <span style="font-weight:700;font-size:.88rem;margin-right:.4rem"><i class="bi bi-funnel-fill"></i> ផ្នែក៖</span>
    @php $filterCats = [null=>['📦','ទាំងអស់ (All)'],'paper'=>['📄','ក្រដាស (Paper)'],'film'=>['🎞️','Film (ហ្វីល)'],'consumable'=>['🧴','Consumable (សម្ភារៈប្រើប្រាស់)']]; @endphp
    @foreach($filterCats as $val => [$emoji,$label])
      @php $active = ($category === $val) || ($val === null && !$category); @endphp
      <a href="{{ route('stock.reports.index') }}{{ $val ? '?category='.$val : '' }}"
         class="btn btn-sm {{ $active ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $emoji }} {{ $label }}</a>
    @endforeach
    <a href="{{ route('stock.reports.create') }}{{ $category ? '?category='.$category : '' }}" class="btn btn-success btn-sm ms-auto">
      <i class="bi bi-plus-lg"></i> បង្កើតរបាយការណ៍{{ $category ? ' '.$filterCats[$category][1] : '' }}
    </a>
  </div>
</div>

<div class="panel">
  <div class="tbl-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th><span class="th-km">កាលបរិច្ឆេទ</span><span class="th-en">Date</span></th>
          <th><span class="th-km">ផ្នែក</span><span class="th-en">Category</span></th>
          <th><span class="th-km">ចំណងជើង</span><span class="th-en">Title</span></th>
          <th><span class="th-km">បង្កើតដោយ</span><span class="th-en">Created By</span></th>
          <th class="col-center"><span class="th-km">រូបភាព</span><span class="th-en">Image</span></th>
          <th class="col-center"><span class="th-km">Telegram</span><span class="th-en">Sent</span></th>
          <th class="col-center"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($reports as $r)
          <tr>
            <td style="font-family:var(--font-latin);font-weight:600">{{ $r->report_date->format('d/m/Y') }}</td>
            <td><span class="badge {{ match($r->category){'paper'=>'badge-binding','film'=>'badge-staple','offset'=>'badge-progress',default=>'badge-done'} }}">{{ $r->categoryLabel() }}</span></td>
            <td style="font-weight:600">{{ $r->title ?? 'Stock Report' }}</td>
            <td style="font-size:.85rem;color:var(--text-secondary)">{{ $r->created_by ?? '—' }}</td>
            <td style="text-align:center">
              @if($r->image_path)
                <i class="bi bi-image" style="color:var(--success)"></i>
              @else
                <span style="color:var(--text-muted)">—</span>
              @endif
            </td>
            <td style="text-align:center">
              @if($r->telegram_sent)
                <span class="badge badge-done">បានផ្ញើ</span>
              @else
                <span class="badge badge-pending">មិនទាន់ផ្ញើ</span>
              @endif
            </td>
            <td style="text-align:center">
              <a href="{{ route('stock.reports.show', $r) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7">
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
              <p style="font-weight:600;margin:0">មិនទាន់មានរបាយការណ៍</p>
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($reports->hasPages())<div class="panel-body">{{ $reports->links() }}</div>@endif
</div>
@endsection
