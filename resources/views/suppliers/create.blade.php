@extends('layouts.app')
@section('title','បន្ថែមអ្នកផ្គត់ផ្គង់')
@section('page-title','New Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">បន្ថែមអ្នកផ្គត់ផ្គង់ថ្មី</h1>
  <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
</div>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="panel">
      <div class="panel-body">
        <form action="{{ route('suppliers.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">ឈ្មោះ *</label>
              <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">ប្រភេទទំនិញ</label>
              <select name="supply_type" class="form-select">
                <option value="">— ជ្រើស —</option>
                @foreach(['paper'=>'ក្រដាស','ink'=>'ថ្នាំ','plate'=>'Plate','spare_part'=>'Spare Parts','chemical'=>'Chemical','other'=>'ផ្សេងៗ'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('supply_type')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">អ្នកទំនាក់ទំនង</label>
              <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">លេខទូរស័ព្ទ</label>
              <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" style="font-family:var(--font-latin)">
            </div>
            <div class="col-md-6">
              <label class="form-label">អ៊ីម៉ែល</label>
              <input type="email" name="email" class="form-control" value="{{ old('email') }}" style="font-family:var(--font-latin)">
            </div>
            <div class="col-md-6">
              <label class="form-label">ស្ថានភាព</label>
              <select name="status" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">អាសយដ្ឋាន</label>
              <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label">កំណត់ចំណាំ</label>
              <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> រក្សាទុក</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">បោះបង់</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
