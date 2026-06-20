@extends('layouts.app')
@section('title','កែប្រែ — '.$machine->name)
@section('page-title','Edit Machine')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">កែប្រែ: {{ $machine->name }}</h1>
  <a href="{{ route('machines.show',$machine) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
</div>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-body">
        <form action="{{ route('machines.update',$machine) }}" method="POST">
          @csrf @method('PUT')
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">ឈ្មោះ *</label>
              <input type="text" name="name" class="form-control" value="{{ old('name',$machine->name) }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">ប្រភេទ *</label>
              <select name="type" class="form-select" required>
                @foreach(['offset'=>'Offset','digital'=>'Digital','binding'=>'Binding','cutting'=>'Cutting','folding'=>'Folding','other'=>'Other'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('type',$machine->type)==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">ក្រុមហ៊ុន</label>
              <input type="text" name="manufacturer" class="form-control" value="{{ old('manufacturer',$machine->manufacturer) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Model</label>
              <input type="text" name="model" class="form-control" value="{{ old('model',$machine->model) }}" style="font-family:var(--font-latin)">
            </div>
            <div class="col-md-6">
              <label class="form-label">Serial Number</label>
              <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number',$machine->serial_number) }}" style="font-family:var(--font-latin)">
            </div>
            <div class="col-md-6">
              <label class="form-label">ស្ថានភាព *</label>
              <select name="status" class="form-select" required>
                @foreach(['operational'=>'Operational','maintenance'=>'Maintenance','breakdown'=>'Breakdown','idle'=>'Idle','retired'=>'Retired'] as $v=>$l)
                  <option value="{{ $v }}" {{ old('status',$machine->status)==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">ថ្ងៃទិញ</label>
              <input type="date" name="purchased_date" class="form-control" value="{{ old('purchased_date',$machine->purchased_date?->format('Y-m-d')) }}" style="font-family:var(--font-latin)">
            </div>
            <div class="col-md-6">
              <label class="form-label">ថែទាំចុងក្រោយ</label>
              <input type="date" name="last_maintenance" class="form-control" value="{{ old('last_maintenance',$machine->last_maintenance?->format('Y-m-d')) }}" style="font-family:var(--font-latin)">
            </div>
            <div class="col-md-6">
              <label class="form-label">រយៈថែទាំ (ថ្ងៃ) *</label>
              <input type="number" name="maintenance_interval_days" class="form-control" value="{{ old('maintenance_interval_days',$machine->maintenance_interval_days) }}" min="1" style="font-family:var(--font-latin)" required>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" class="form-control" rows="2">{{ old('notes',$machine->notes) }}</textarea>
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> រក្សាទុក</button>
            <a href="{{ route('machines.show',$machine) }}" class="btn btn-outline-secondary">បោះបង់</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
