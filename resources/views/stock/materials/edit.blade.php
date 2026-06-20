@extends('layouts.app')
@section('title','កែប្រែ — '.$material->name)
@section('page-title','Edit Material')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">កែប្រែ: {{ $material->name }}</h1>
  <a href="{{ route('stock.materials.show',$material) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-body">
        <form action="{{ route('stock.materials.update',$material) }}" method="POST" id="editForm">
          @csrf @method('PUT')
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label">ឈ្មោះ English *</label>
              <input type="text" name="name" class="form-control" value="{{ old('name',$material->name) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">ឈ្មោះ ខ្មែរ <span style="font-size:.72rem;color:var(--text-muted)">(ស្រេចចិត្ត)</span></label>
              <input type="text" name="name_km" class="form-control" value="{{ old('name_km',$material->name_km) }}"
                     placeholder="ឧ. សាប៊ូជូតប្លាក" style="font-family:var(--font-khmer)">
            </div>

            <div class="col-md-4">
              <label class="form-label">ប្រភេទ *</label>
              @php
                $knownCats = ['paper','film','consumable','offset'];
                $currentCat = old('category',$material->category);
                $isOtherCat = !in_array($currentCat, $knownCats);
              @endphp
              <select name="category" id="catSelect" class="form-select" required>
                <option value="paper"      {{ $currentCat==='paper'?'selected':'' }}>📄 ក្រដាស (Paper)</option>
                <option value="film"       {{ $currentCat==='film'?'selected':'' }}>🎞️ Film (ហ្វីម)</option>
                <option value="consumable" {{ $currentCat==='consumable'?'selected':'' }}>🧴 Consumable (សម្ភារៈប្រើប្រាស់)</option>
                <option value="other"      {{ $isOtherCat?'selected':'' }}>➕ ផ្សេងទៀត (Other)</option>
              </select>
            </div>

            <div class="col-12" id="otherCatRow" style="{{ $isOtherCat ? '' : 'display:none' }}">
              <label class="form-label">ប្រភេទផ្សេង</label>
              <input type="text" name="category_other" id="otherCatInput" class="form-control"
                     value="{{ $isOtherCat ? $currentCat : '' }}" placeholder="custom category...">
            </div>

            <div class="col-md-6">
              <label class="form-label">Sub-Type</label>
              <input type="text" name="sub_type" class="form-control" value="{{ old('sub_type',$material->sub_type) }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Size</label>
              <input type="text" name="size" class="form-control" value="{{ old('size',$material->size) }}">
            </div>

            {{-- Icon picker --}}
            <div class="col-12">
              <label class="form-label">Icon <span style="font-size:.72rem;color:var(--text-muted)">(Font Awesome class)</span></label>
              @php
                $icons = [
                  'fa-solid fa-file'=>'ក្រដាស','fa-solid fa-film'=>'Film',
                  'fa-solid fa-droplet'=>'Ink','fa-solid fa-spray-can-sparkles'=>'Cleaner',
                  'fa-solid fa-box'=>'Box','fa-solid fa-circle-half-stroke'=>'Blanket',
                  'fa-solid fa-sponge'=>'Sponge','fa-solid fa-print'=>'Print',
                  'fa-solid fa-layer-group'=>'Layers','fa-solid fa-palette'=>'Color',
                  'fa-solid fa-wrench'=>'Tool','fa-solid fa-bolt'=>'Electric',
                  'fa-solid fa-cube'=>'Cube','fa-solid fa-scissors'=>'Cut',
                  'fa-solid fa-star'=>'Other',
                ];
                $selIcon = old('icon', $material->icon ?? '');
              @endphp
              <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
                @foreach($icons as $cls => $lbl)
                  <button type="button" class="icon-btn"
                          onclick="pickIcon('{{ $cls }}')" title="{{ $lbl }}"
                          data-cls="{{ $cls }}"
                          style="width:44px;height:44px;border:2px solid {{ $selIcon===$cls ? 'var(--primary)' : 'var(--border)' }};
                                 border-radius:var(--radius);background:{{ $selIcon===$cls ? '#eff6ff' : 'var(--surface-2)' }};
                                 cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.1rem">
                    <i class="{{ $cls }}"></i>
                  </button>
                @endforeach
              </div>
              <input type="hidden" name="icon" id="iconInput" value="{{ $selIcon }}">
              <div style="font-size:.72rem;color:var(--text-muted)">
                ឬ​វាយ FA class:
                <input type="text" id="iconManual" class="form-control form-control-sm d-inline-block"
                       style="width:220px;font-family:monospace" placeholder="fa-solid fa-..."
                       value="{{ $selIcon }}" oninput="syncIcon(this.value)">
                <span id="iconPreview" style="margin-left:.5rem;font-size:1.3rem">
                  @if($selIcon)<i class="{{ $selIcon }}"></i>@endif
                </span>
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Unit *</label>
              @php
                $knownUnits = ['pack','roll','can','bottle','sheet','ream','pcs','box','kg','liter'];
                $currentUnit = old('unit',$material->unit);
                $isOtherUnit = !in_array($currentUnit, $knownUnits);
              @endphp
              <select name="unit" id="unitSelect" class="form-select" required>
                @php $unitLabels = ['pack'=>'Pack (កញ្ចប់)','roll'=>'Roll','can'=>'Can (កំប៉ុង)','bottle'=>'Bottle (ដប)','sheet'=>'Sheet','ream'=>'Ream','pcs'=>'Pcs','box'=>'Box','kg'=>'Kg','liter'=>'Liter','other'=>'ផ្សេង...']; @endphp
                @foreach($unitLabels as $v=>$l)
                  <option value="{{ $v }}" {{ ($isOtherUnit&&$v==='other')||(!$isOtherUnit&&$currentUnit===$v) ? 'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4" id="otherUnitRow" style="{{ $isOtherUnit ? '' : 'display:none' }}">
              <label class="form-label">ឯកតាផ្សេង</label>
              <input type="text" name="unit_other" id="otherUnitInput" class="form-control"
                     value="{{ $isOtherUnit ? $currentUnit : '' }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">Min Stock *</label>
              <input type="number" name="min_stock" class="form-control"
                     value="{{ old('min_stock',$material->min_stock) }}" min="0" step="1"
                     style="font-family:var(--font-latin)" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">ស្ថានភាព</label>
              <select name="status" class="form-select">
                <option value="active"   {{ old('status',$material->status)==='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ old('status',$material->status)==='inactive'?'selected':'' }}>Inactive</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">ទីតាំង</label>
              <input type="text" name="location" class="form-control" value="{{ old('location',$material->location) }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">Notes</label>
              <input type="text" name="notes" class="form-control" value="{{ old('notes',$material->notes) }}">
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> រក្សាទុក</button>
            <a href="{{ route('stock.materials.show',$material) }}" class="btn btn-outline-secondary">បោះបង់</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('catSelect').addEventListener('change', function() {
  document.getElementById('otherCatRow').style.display = this.value === 'other' ? '' : 'none';
});
document.getElementById('unitSelect').addEventListener('change', function() {
  document.getElementById('otherUnitRow').style.display = this.value === 'other' ? '' : 'none';
});
document.getElementById('editForm').addEventListener('submit', function() {
  const cs = document.getElementById('catSelect');
  if (cs.value === 'other') {
    const v = document.getElementById('otherCatInput').value.trim().toLowerCase().replace(/\s+/g,'-');
    if (v) cs.value = v;
  }
  const us = document.getElementById('unitSelect');
  if (us.value === 'other') {
    const v = document.getElementById('otherUnitInput').value.trim().toLowerCase();
    if (v) us.value = v;
  }
});
function pickIcon(cls) {
  document.getElementById('iconInput').value = cls;
  document.getElementById('iconManual').value = cls;
  document.getElementById('iconPreview').innerHTML = '<i class="'+cls+'"></i>';
  document.querySelectorAll('.icon-btn').forEach(b => {
    const active = b.dataset.cls === cls;
    b.style.borderColor = active ? 'var(--primary)' : 'var(--border)';
    b.style.background  = active ? '#eff6ff' : 'var(--surface-2)';
  });
}
function syncIcon(val) {
  document.getElementById('iconInput').value = val;
  document.getElementById('iconPreview').innerHTML = val ? '<i class="'+val+'"></i>' : '';
}
</script>
@endpush
