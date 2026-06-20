@extends('layouts.app')
@section('title','Record Stock Movement')
@section('page-title','New Movement')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="section-title">Record Stock Movement</h1>
  <a href="{{ route('stock.movements.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់ក្រោយ</a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="panel">
      <div class="panel-body">
        <form action="{{ route('stock.movements.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Material *</label>
              <select name="material_id" class="form-select" required>
                <option value="">— ជ្រើសរើស Material —</option>
                @foreach($materials as $m)
                  <option value="{{ $m->id }}" {{ request('material')==$m->id?'selected':'' }}>
                    {{ $m->name }}{{ $m->sub_type?' · '.$m->sub_type:'' }} (Stock: {{ number_format($m->calculated_stock,1) }} {{ $m->unit }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">ប្រភេទចលនា *</label>
              <select name="type" class="form-select" required>
                <option value="in">📥 Stock IN</option>
                <option value="out">📤 Stock OUT</option>
                <option value="adjust">🔧 Adjust</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">បរិមាណ *</label>
              <input type="number" name="quantity" class="form-control" value="{{ old('quantity') }}" min="0.01" step="0.01" style="font-family:var(--font-latin);font-weight:700;font-size:1.1rem" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">កាលបរិច្ឆេទ *</label>
              <input type="date" name="movement_date" class="form-control" value="{{ old('movement_date', now()->format('Y-m-d')) }}" style="font-family:var(--font-latin)" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">អ្នកធ្វើ</label>
              <input type="text" name="performed_by" class="form-control" value="{{ old('performed_by') }}" placeholder="ឈ្មោះអ្នកធ្វើ">
            </div>
            <div class="col-md-6">
              <label class="form-label">Reference</label>
              <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="PO number, Job name...">
            </div>
            <div class="col-md-6">
              <label class="form-label">Notes</label>
              <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
            </div>
          </div>

          <div style="margin-top:1rem;padding:.75rem 1rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius);font-size:.82rem;color:#1d4ed8">
            <i class="bi bi-info-circle me-1"></i>
            <strong>IN</strong> = បន្ថែមចូល Stock &nbsp;|&nbsp;
            <strong>OUT</strong> = ដកចេញពី Stock &nbsp;|&nbsp;
            <strong>Adjust</strong> = កែតម្រូវ Stock ឱ្យត្រឹមត្រូវ
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg"></i> រក្សាទុក</button>
            <a href="{{ route('stock.movements.index') }}" class="btn btn-outline-secondary btn-lg">បោះបង់</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
