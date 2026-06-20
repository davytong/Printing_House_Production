@extends('layouts.app')
@section('title','កែប្រែ — '.$inventoryItem->name)
@section('page-title','Edit Inventory Item')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">កែប្រែ: {{ $inventoryItem->name }}</h1>
  <a href="{{ route('inventory.show',$inventoryItem) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
</div>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-body">
        <form action="{{ route('inventory.update',$inventoryItem) }}" method="POST">
          @csrf @method('PUT')
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">ឈ្មោះ *</label>
              <input type="text" name="name" class="form-control" value="{{ old('name',$inventoryItem->name) }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">ប្រភេទ *</label>
              <select name="type" class="form-select" required>
                @foreach(['paper'=>'ក្រដាស','ink'=>'ថ្នាំ','plate'=>'Plate','spare_part'=>'Spare Parts','chemical'=>'Chemical','other'=>'ផ្សេងៗ'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('type',$inventoryItem->type)==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">ឯកតា *</label>
              <select name="unit" class="form-select" required>
                @foreach(['pcs','kg','liter','ream','box','roll','sheet'] as $u)
                  <option value="{{ $u }}" {{ old('unit',$inventoryItem->unit)==$u?'selected':'' }}>{{ $u }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Stock អប្បបរមា *</label>
              <input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock',$inventoryItem->minimum_stock) }}" min="0" step="0.01" style="font-family:var(--font-latin)" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">តម្លៃ/Unit (USD)</label>
              <input type="number" name="unit_cost" class="form-control" value="{{ old('unit_cost',$inventoryItem->unit_cost) }}" min="0" step="0.01" style="font-family:var(--font-latin)">
            </div>
            <div class="col-md-4">
              <label class="form-label">ស្ថានភាព</label>
              <select name="status" class="form-select">
                <option value="active" {{ old('status',$inventoryItem->status)==='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ old('status',$inventoryItem->status)==='inactive'?'selected':'' }}>Inactive</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">ទីតាំង</label>
              <input type="text" name="location" class="form-control" value="{{ old('location',$inventoryItem->location) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">អ្នកផ្គត់ផ្គង់</label>
              <select name="supplier_id" class="form-select">
                <option value="">— ជ្រើស —</option>
                @foreach($suppliers as $s)
                  <option value="{{ $s->id }}" {{ old('supplier_id',$inventoryItem->supplier_id)==$s->id?'selected':'' }}>{{ $s->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">ការពិពណ៌នា</label>
              <textarea name="description" class="form-control" rows="2">{{ old('description',$inventoryItem->description) }}</textarea>
            </div>
          </div>
          <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);padding:.75rem 1rem;margin-top:1rem;font-size:.82rem;color:#92400e">
            <i class="bi bi-info-circle me-1"></i> ដើម្បីកែចំនួន Stock — ប្រើ <strong>Adjust Stock</strong> ក្នុងទំព័រ Detail
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> រក្សាទុក</button>
            <a href="{{ route('inventory.show',$inventoryItem) }}" class="btn btn-outline-secondary">បោះបង់</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
