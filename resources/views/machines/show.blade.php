@extends('layouts.app')
@section('title',$machine->name)
@section('page-title','Machine Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">{{ $machine->name }}</h1>
    <p class="section-sub" style="font-family:var(--font-latin)">{{ $machine->code }}</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleModal">
      <i class="bi bi-calendar-plus"></i> កំណត់ការថែទាំ
    </button>
    <a href="{{ route('machines.edit', $machine) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
    <form action="{{ route('machines.destroy',$machine) }}" method="POST"
          onsubmit="return confirm('{{ $machine->maintenanceSchedules()->exists() ? 'ម៉ាស៊ីននឹង Retire (មានប្រវត្តិថែទាំ)' : 'លុបម៉ាស៊ីននេះ?' }}')">
      @csrf @method('DELETE')
      <button class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none">
        <i class="bi bi-trash3"></i>
      </button>
    </form>
    <a href="{{ route('machines.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-info-circle"></i></div>
          <span>ព័ត៌មានម៉ាស៊ីន</span>
        </div>
      </div>
      <div class="panel-body d-flex flex-column gap-3">
        @php
          $stColor = match($machine->status){
            'operational'=>'#10b981','maintenance'=>'#f59e0b','breakdown'=>'#ef4444','idle'=>'#94a3b8',default=>'#6366f1'
          };
          $typeMap = ['offset'=>'Offset','digital'=>'Digital','binding'=>'Binding','cutting'=>'Cutting','folding'=>'Folding','other'=>'Other'];
          $days    = $machine->daysUntilMaintenance();
        @endphp
        <div style="display:flex;align-items:center;gap:.5rem">
          <span style="width:12px;height:12px;border-radius:50%;background:{{ $stColor }};flex-shrink:0"></span>
          <span style="font-weight:700;font-size:.95rem">{{ ucfirst($machine->status) }}</span>
        </div>
        @foreach([
          ['ប្រភេទ', $typeMap[$machine->type]??$machine->type],
          ['ក្រុមហ៊ុន', $machine->manufacturer??'—'],
          ['Model', $machine->model??'—', true],
          ['Serial No.', $machine->serial_number??'—', true],
          ['ថ្ងៃទិញ', $machine->purchased_date?->format('d/m/Y')??'—', true],
          ['ថែទាំចុងក្រោយ', $machine->last_maintenance?->format('d/m/Y')??'—', true],
          ['ថែទាំបន្ទាប់', $machine->next_maintenance?->format('d/m/Y')??'—', true],
          ['រយៈថែទាំ', $machine->maintenance_interval_days.' days', true],
        ] as [$lbl,$val,$latin??false])
          <div>
            <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">{{ $lbl }}</div>
            <div style="font-size:.88rem;margin-top:.1rem;{{ $latin?'font-family:var(--font-latin)':'' }}
              {{ $lbl==='ថែទាំបន្ទាប់'&&$days<0?';color:var(--danger);font-weight:700':'' }}">
              {{ $val }}
              @if($lbl==='ថែទាំបន្ទាប់'&&$machine->next_maintenance)
                <span style="font-size:.72rem">({{ $days<0?abs($days).'d overdue':$days.'d away' }})</span>
              @endif
            </div>
          </div>
        @endforeach
        @if($machine->notes)
          <div>
            <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Notes</div>
            <div style="font-size:.85rem;color:var(--text-secondary);margin-top:.1rem">{{ $machine->notes }}</div>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-tools"></i></div>
          <span>ប្រវត្តិការថែទាំ</span>
        </div>
      </div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th><span class="th-km">ថ្ងៃ</span><span class="th-en">Date</span></th>
              <th><span class="th-km">ប្រភេទ</span><span class="th-en">Type</span></th>
              <th class="col-center"><span class="th-km">ស្ថានភាព</span><span class="th-en">Status</span></th>
              <th><span class="th-km">អ្នកបច្ចេកទេស</span><span class="th-en">Technician</span></th>
              <th class="col-right"><span class="th-km">Downtime</span><span class="th-en">Hours</span></th>
              <th class="col-right"><span class="th-km">ចំណាយ</span><span class="th-en">Cost</span></th>
              <th class="col-center"></th>
            </tr>
          </thead>
          <tbody>
            @forelse($maintenances as $ms)
              <tr>
                <td style="font-family:var(--font-latin);font-size:.82rem">{{ $ms->scheduled_date->format('d/m/Y') }}</td>
                <td><span class="badge badge-binding" style="font-size:.72rem;font-family:var(--font-latin)">{{ ucfirst($ms->type) }}</span></td>
                <td style="text-align:center">
                  @php $stMap=['scheduled'=>'badge-progress','in_progress'=>'badge-binding','completed'=>'badge-done','overdue'=>'badge-pending','cancelled'=>'badge-pending']; @endphp
                  <span class="badge {{ $stMap[$ms->status]??'badge-binding' }}" style="font-size:.7rem;font-family:var(--font-latin)">{{ ucfirst($ms->status) }}</span>
                </td>
                <td style="font-size:.82rem">{{ $ms->technician ?? '—' }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.82rem">{{ $ms->downtime_hours }}h</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.82rem">${{ number_format($ms->cost,2) }}</td>
                <td style="text-align:center">
                  @if(in_array($ms->status,['scheduled','in_progress']))
                    <button class="btn btn-ghost btn-sm complete-btn"
                            data-id="{{ $ms->id }}"
                            data-url="{{ route('machines.complete', $ms) }}"
                            title="Mark Complete">
                      <i class="bi bi-check-circle text-success"></i>
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="7" style="text-align:center;padding:1.5rem;color:var(--text-muted)">មិនទាន់មានប្រវត្តិថែទាំ</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($maintenances->hasPages())<div class="panel-body">{{ $maintenances->links() }}</div>@endif
    </div>
  </div>
</div>

{{-- Schedule Modal --}}
<div class="modal fade" id="scheduleModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form action="{{ route('machines.schedule',$machine) }}" method="POST">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">កំណត់ការថែទាំ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">ប្រភេទ *</label>
              <select name="type" class="form-select" required>
                @foreach(['preventive'=>'Preventive','corrective'=>'Corrective','inspection'=>'Inspection','breakdown'=>'Breakdown'] as $v=>$l)
                  <option value="{{ $v }}">{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">ថ្ងៃ *</label>
              <input type="date" name="scheduled_date" class="form-control" required style="font-family:var(--font-latin)">
            </div>
            <div class="col-12">
              <label class="form-label">អ្នកបច្ចេកទេស</label>
              <input type="text" name="technician" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">ការពិពណ៌នា</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-calendar-plus"></i> រក្សាទុក</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Complete Maintenance Modal --}}
<div class="modal fade" id="completeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form id="completeForm" method="POST">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">បញ្ចប់ការថែទាំ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">ថ្ងៃបញ្ចប់ *</label>
              <input type="date" name="completed_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required style="font-family:var(--font-latin)">
            </div>
            <div class="col-6">
              <label class="form-label">Downtime (ម៉ោង)</label>
              <input type="number" name="downtime_hours" class="form-control" value="0" min="0" style="font-family:var(--font-latin)">
            </div>
            <div class="col-6">
              <label class="form-label">ចំណាយ (USD)</label>
              <input type="number" name="cost" class="form-control" value="0" min="0" step="0.01" style="font-family:var(--font-latin)">
            </div>
            <div class="col-6">
              <label class="form-label">Spare Parts ប្រើ</label>
              <input type="text" name="parts_used" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Findings / Notes</label>
              <textarea name="findings" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> បញ្ជាក់បញ្ចប់</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.complete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('completeForm').action = btn.dataset.url;
    new bootstrap.Modal(document.getElementById('completeModal')).show();
  });
});
</script>
@endpush
