@extends('layouts.app')
@section('title','បន្ថែម Material')
@section('page-title','New Material')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">បន្ថែម Material ថ្មី</h1>
  <a href="{{ route('stock.materials.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-body">
        <form action="{{ route('stock.materials.store') }}" method="POST" id="matForm">
          @csrf
          <div class="row g-3">

            {{-- Name (bilingual) --}}
            <div class="col-md-6">
              <label class="form-label">ឈ្មោះ English *</label>
              <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                     placeholder="e.g. Plate Cleaner, Yellow Ink" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">ឈ្មោះ ខ្មែរ <span style="font-size:.72rem;color:var(--text-muted)">(ស្រេចចិត្ត)</span></label>
              <input type="text" name="name_km" class="form-control" value="{{ old('name_km') }}"
                     placeholder="ឧ. សាប៊ូជូតប្លាក, ទឹកថ្នាំលឿង"
                     style="font-family:var(--font-khmer)">
            </div>

            {{-- Category --}}
            <div class="col-md-4">
              <label class="form-label">ប្រភេទ *</label>
              <select name="category" id="catSelect" class="form-select" required>
                <option value="paper"      {{ old('category')==='paper'?'selected':'' }}>📄 ក្រដាស (Paper)</option>
                <option value="film"       {{ old('category')==='film'?'selected':'' }}>🎞️ Film (ហ្វីម)</option>
                <option value="consumable" {{ old('category')==='consumable'?'selected':'' }}>🧴 Consumable (សម្ភារៈប្រើប្រាស់)</option>
                <option value="other"      {{ old('category')==='other'?'selected':'' }}>➕ ផ្សេងទៀត (Other)</option>
              </select>
            </div>

            {{-- Other category custom input --}}
            <div class="col-12" id="otherCatRow" style="{{ old('category')==='other' ? '' : 'display:none' }}">
              <label class="form-label">ប្រភេទផ្សេងទៀត (Other Category)</label>
              <input type="text" name="category_other" id="otherCatInput" class="form-control"
                     value="{{ old('category_other') }}" placeholder="ឧ. Spare Parts, Packaging...">
            </div>

            {{-- Sub-Type --}}
            <div class="col-md-6">
              <label class="form-label">Sub-Type <span style="font-size:.72rem;color:var(--text-muted)">(ប្រភេទរង)</span></label>
              <input type="text" name="sub_type" class="form-control" value="{{ old('sub_type') }}"
                     placeholder="ឧ. Woodfree, CMYK Ink, Press Parts...">
            </div>

            {{-- Size --}}
            <div class="col-md-6">
              <label class="form-label">Size / Spec</label>
              <input type="text" name="size" class="form-control" value="{{ old('size') }}"
                     placeholder="A1, 65×90cm, Roll...">
            </div>

            {{-- Icon picker --}}
            <div class="col-12">
              <label class="form-label">រូបតំណាង (Icon) <span style="font-size:.72rem;color:var(--text-muted)">— ជ្រើស Font Awesome icon</span></label>
              <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem" id="iconGrid">
                @php
                  $icons = [
                    'fa-solid fa-file'                  => 'ក្រដាស',
                    'fa-solid fa-film'                  => 'Film',
                    'fa-solid fa-droplet'               => 'ទឹកថ្នាំ',
                    'fa-solid fa-spray-can-sparkles'    => 'Cleaner',
                    'fa-solid fa-box'                   => 'Box',
                    'fa-solid fa-circle-half-stroke'    => 'Blanket',
                    'fa-solid fa-sponge'                => 'Sponge',
                    'fa-solid fa-print'                 => 'Print',
                    'fa-solid fa-layer-group'           => 'Layers',
                    'fa-solid fa-palette'               => 'Color',
                    'fa-solid fa-wrench'                => 'Tool',
                    'fa-solid fa-bolt'                  => 'Electric',
                    'fa-solid fa-cube'                  => 'Cube',
                    'fa-solid fa-scissors'              => 'Cut',
                    'fa-solid fa-star'                  => 'Other',
                  ];
                  $selIcon = old('icon', '');
                @endphp
                @foreach($icons as $cls => $lbl)
                  <button type="button" class="icon-btn {{ $selIcon===$cls ? 'icon-btn-active' : '' }}"
                          onclick="pickIcon('{{ $cls }}')" title="{{ $lbl }}"
                          style="width:44px;height:44px;border:2px solid {{ $selIcon===$cls ? 'var(--primary)' : 'var(--border)' }};
                                 border-radius:var(--radius);background:{{ $selIcon===$cls ? '#eff6ff' : 'var(--surface-2)' }};
                                 cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:.15s">
                    <i class="{{ $cls }}"></i>
                  </button>
                @endforeach
              </div>
              <input type="hidden" name="icon" id="iconInput" value="{{ $selIcon }}">
              <div style="font-size:.72rem;color:var(--text-muted)">
                ឬ​វាយ FA class ផ្ទាល់:
                <input type="text" id="iconManual" class="form-control form-control-sm d-inline-block" style="width:220px;font-family:monospace"
                       placeholder="fa-solid fa-..." value="{{ $selIcon }}"
                       oninput="syncIcon(this.value)">
                <span id="iconPreview" style="margin-left:.5rem;font-size:1.3rem"><i class="{{ $selIcon }}"></i></span>
              </div>
            </div>

            {{-- Unit --}}
            <div class="col-md-4">
              <label class="form-label">ឯកតា (Unit) *</label>
              <select name="unit" id="unitSelect" class="form-select" required>
                @php
                  $units = [
                    'pack'=>'Pack (កញ្ចប់)', 'roll'=>'Roll (រំពត់)',
                    'can'=>'Can (កំប៉ុង)', 'bottle'=>'Bottle (ដប)',
                    'sheet'=>'Sheet (សន្លឹក)', 'ream'=>'Ream',
                    'pcs'=>'Pcs (ដ៉ំ/គ្រាប់)', 'box'=>'Box (ប្រអប់)',
                    'kg'=>'Kg', 'liter'=>'Liter',
                    'other'=>'ផ្សេងទៀត...',
                  ];
                @endphp
                @foreach($units as $v => $l)
                  <option value="{{ $v }}" {{ old('unit')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>

            {{-- Custom unit --}}
            <div class="col-md-4" id="otherUnitRow" style="{{ old('unit')==='other' ? '' : 'display:none' }}">
              <label class="form-label">ឯកតាផ្សេង</label>
              <input type="text" name="unit_other" id="otherUnitInput" class="form-control"
                     value="{{ old('unit_other') }}" placeholder="ឧ. tube, sachet...">
            </div>

            {{-- Initial stock --}}
            <div class="col-md-4">
              <label class="form-label">Stock ចាប់ផ្ដើម</label>
              <input type="number" name="initial_stock" class="form-control"
                     value="{{ old('initial_stock', 0) }}" min="0" step="1"
                     style="font-family:var(--font-latin)">
            </div>

            {{-- Min stock --}}
            <div class="col-md-4">
              <label class="form-label">Min Stock (ព្រមាន) *</label>
              <input type="number" name="min_stock" class="form-control"
                     value="{{ old('min_stock', 5) }}" min="0" step="1"
                     style="font-family:var(--font-latin)" required>
              <div style="font-size:.7rem;color:var(--text-muted);margin-top:.2rem">ព្រមាន​ if stock ≤ ចំនួននេះ</div>
            </div>

            {{-- Location --}}
            <div class="col-md-4">
              <label class="form-label">ទីតាំង</label>
              <input type="text" name="location" class="form-control"
                     value="{{ old('location') }}" placeholder="Shelf A1, Store Room...">
            </div>

            {{-- Notes --}}
            <div class="col-12">
              <label class="form-label">Notes</label>
              <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
            </div>

          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg"></i> រក្សាទុក</button>
            <a href="{{ route('stock.materials.index') }}" class="btn btn-outline-secondary btn-lg">បោះបង់</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Category "Other" toggle
document.getElementById('catSelect').addEventListener('change', function() {
  document.getElementById('otherCatRow').style.display = this.value === 'other' ? '' : 'none';
  if (this.value === 'other') document.getElementById('otherCatInput').focus();
});

// Unit "Other" toggle
document.getElementById('unitSelect').addEventListener('change', function() {
  document.getElementById('otherUnitRow').style.display = this.value === 'other' ? '' : 'none';
  if (this.value === 'other') document.getElementById('otherUnitInput').focus();
});

// If "other" selected, substitute real value before submit
document.getElementById('matForm').addEventListener('submit', function() {
  const cs = document.getElementById('catSelect');
  if (cs.value === 'other') {
    const custom = document.getElementById('otherCatInput').value.trim().toLowerCase().replace(/\s+/g,'-');
    if (custom) { cs.value = custom; cs.removeAttribute('required'); }
  }
  const us = document.getElementById('unitSelect');
  if (us.value === 'other') {
    const custom = document.getElementById('otherUnitInput').value.trim().toLowerCase();
    if (custom) { us.value = custom; us.removeAttribute('required'); }
  }
});

// Icon picker
function pickIcon(cls) {
  document.getElementById('iconInput').value = cls;
  document.getElementById('iconManual').value = cls;
  document.getElementById('iconPreview').innerHTML = '<i class="'+cls+'"></i>';
  document.querySelectorAll('.icon-btn').forEach(b => {
    const active = b.getAttribute('onclick') === "pickIcon('"+cls+"')";
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
