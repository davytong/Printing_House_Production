@extends('layouts.app')
@section('title','Create Stock Report')
@section('page-title','New Stock Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">បង្កើតរបាយការណ៍ Stock</h1>
  <a href="{{ route('stock.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
</div>

{{-- Category picker — each responsible person reports their own category --}}
<div class="panel mb-4">
  <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:.6rem;align-items:center">
    <span style="font-weight:700;font-size:.88rem;margin-right:.4rem"><i class="bi bi-people-fill"></i> ផ្នែកទទួលបន្ទុក៖</span>
    @php
      $cats = [null=>['📦','ទាំងអស់ (All)'],'paper'=>['📄','ក្រដាស (Paper)'],'film'=>['🎞️','Film (ហ្វីល)'],'consumable'=>['🧴','Consumable (សម្ភារៈប្រើប្រាស់)']];
    @endphp
    @foreach($cats as $val => [$emoji,$label])
      @php $active = ($category === $val) || ($val === null && !$category); @endphp
      <a href="{{ route('stock.reports.create') }}{{ $val ? '?category='.$val : '' }}"
         class="btn btn-sm {{ $active ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ $emoji }} {{ $label }}
      </a>
    @endforeach
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <form action="{{ route('stock.reports.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="category" value="{{ $category }}">
      <div class="panel mb-4">
        <div class="panel-header">
          <div class="ph-title"><div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-file-earmark-plus"></i></div><span>ព័ត៌មានរបាយការណ៍ @if($category)— {{ $cats[$category][1] }}@endif</span></div>
        </div>
        <div class="panel-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">កាលបរិច្ឆេទ *</label>
              <input type="date" name="report_date" class="form-control" value="{{ now()->format('Y-m-d') }}" style="font-family:var(--font-latin)" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">បង្កើតដោយ</label>
              <input type="text" name="created_by" class="form-control" placeholder="ឈ្មោះអ្នករាយការណ៍">
            </div>
            <div class="col-12">
              <label class="form-label">ចំណងជើង</label>
              <input type="text" name="title" class="form-control" placeholder="ឧ. របាយការណ៍ {{ $category ? $cats[$category][1].' ' : '' }}Stock ប្រចាំថ្ងៃ {{ now()->format('d/m/Y') }}">
            </div>
            <div class="col-12">
              <label class="form-label">កំណត់ចំណាំ (Notes)</label>
              <textarea name="notes" class="form-control" rows="3" placeholder="ព័ត៌មានបន្ថែម..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">រូបភាពភ្ជាប់ (ស្រេចចិត្ត)</label>
              <input type="file" name="image" class="form-control" accept="image/*">
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem">JPG/PNG/WebP · Max 10MB · បង្ហាប់ស្វ័យប្រវត្តិ 1200px</div>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg"></i> បង្កើតរបាយការណ៍</button>
            <a href="{{ route('stock.reports.index') }}" class="btn btn-outline-secondary btn-lg">បោះបង់</a>
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- Live summary preview --}}
  <div class="col-lg-5">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title"><div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-eye"></i></div><span>Stock Summary (Live)</span></div>
      </div>
      <div class="panel-body">
        @if($summary && isset($summary['categories']))
          @foreach($summary['categories'] as $cat => $data)
            @php $emoji = match($cat){'paper'=>'📄','film'=>'🎞️',default=>'🖨️'}; @endphp
            <div style="margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">
              <div style="font-weight:700;font-size:.88rem;margin-bottom:.4rem">{{ $emoji }} {{ $data['label'] }} ({{ $data['count'] }})</div>
              @foreach(array_slice($data['items'], 0, 5) as $item)
                @php $color = $item['is_low'] ? 'var(--danger)' : 'var(--success)'; @endphp
                <div style="display:flex;justify-content:space-between;align-items:center;padding:.2rem 0;font-size:.8rem">
                  <span>{{ $item['name'] }}{{ $item['sub_type']?' · '.$item['sub_type']:'' }}</span>
                  <span style="font-family:var(--font-latin);font-weight:700;color:{{ $color }}">
                    {{ number_format($item['stock'],1) }} {{ $item['unit'] }}
                    @if($item['is_low']) ⚠️ @endif
                  </span>
                </div>
              @endforeach
              @if(count($data['items']) > 5)
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem">+{{ count($data['items'])-5 }} more...</div>
              @endif
            </div>
          @endforeach
        @else
          <p style="color:var(--text-muted);font-size:.85rem">មិនទាន់មាន Materials ដើម្បីបង្ហាញ</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
