@extends('layouts.app')
@section('title','បន្ថែម Inventory Item')
@section('page-title','New Inventory Item')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">បន្ថែម Inventory Item</h1>
  <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
</div>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-body">
        <form action="{{ route('inventory.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">ឈ្មោះ Item *</label>
              <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">ប្រភេទ *</label>
              <select name="type" class="form-select" required>
                @foreach(['paper'=>'ក្រដាស','ink'=>'ថ្នាំ','plate'=>'Plate','spare_part'=>'Spare Parts','chemical'=>'Chemical','other'=>'ផ្សេងៗ'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('type')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">ឯកតា (Unit) *</label>
              <select name="unit" class="form-select" required>
                @foreach(['pcs'=>'pcs','kg'=>'kg','liter'=>'liter','ream'=>'ream','box'=>'box','roll'=>'roll','sheet'=>'sheet'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('unit')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">ចំនួន Stock បច្ចុប្បន្ន *</label>
              <input type="number" name="quantity_in_stock" class="form-control" value="{{ old('quantity_in_stock',0) }}" min="0" step="0.01" style="font-family:var(--font-latin)" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">ចំនួន Stock អប្បបរមា *</label>
              <input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock',0) }}" min="0" step="0.01" style="font-family:var(--font-latin)" required>
              <div style="font-size:.73rem;color:var(--text-muted);margin-top:.25rem">ព្រមានពេល Stock ក្រោមចំនួននេះ</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">តម្លៃ/Unit (USD)</label>
              <input type="number" name="unit_cost" class="form-control" value="{{ old('unit_cost',0) }}" min="0" step="0.01" style="font-family:var(--font-latin)">
            </div>
            <div class="col-md-6">
              <label class="form-label">ទីតាំងផ្ទុក</label>
              <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Shelf A-1, Warehouse B...">
            </div>
            <div class="col-md-6">
              <label class="form-label">អ្នកផ្គត់ផ្គង់</label>
              <select name="supplier_id" class="form-select">
                <option value="">— ជ្រើស —</option>
                @foreach($suppliers as $s)
                  <option value="{{ $s->id }}" {{ old('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">ការពិពណ៌នា</label>
              <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg"></i> រក្សាទុក</button>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-lg">បោះបង់</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
