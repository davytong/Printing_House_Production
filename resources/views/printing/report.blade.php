@extends('layouts.app')
@section('title',        'របាយការណ៍ការបោះពុម្ព')
@section('page-title',   'របាយការណ៍')
@section('loading-text', 'កំពុងផ្ញើរបាយការណ៍...')

@section('content')

@php
  $totalBooks     = $books->count();
  $totalPrinted   = $books->sum('total_printed');
  $totalTarget    = $books->sum('target_qty');
  $totalRemaining = max($totalTarget - $totalPrinted, 0);
  $overallPct     = $totalTarget > 0 ? round($totalPrinted / $totalTarget * 100) : 0;
  $doneCount      = $books->filter(fn($b) => $b->total_printed >= $b->target_qty)->count();
  $inProgress     = $books->filter(fn($b) => $b->total_printed > 0 && $b->total_printed < $b->target_qty)->count();
  $notStarted     = $books->filter(fn($b) => $b->total_printed === 0)->count();
@endphp

{{-- ════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════ --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">របាយការណ៍ការបោះពុម្ព</h1>
    <p class="section-sub">
      <i class="bi bi-calendar3 me-1"></i>
      <span class="latin">{{ now()->format('d F Y') }}</span>
    </p>
  </div>
  <a href="{{ route('printing.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> ត្រឡប់ទៅគ្រប់គ្រង
  </a>
</div>

{{-- ════════════════════════════════════════════
     KPI CARDS
════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-blue">
      <div class="kpi-icon"><i class="bi bi-journals"></i></div>
      <div>
        <div class="kpi-value">{{ $totalBooks }}</div>
        <div class="kpi-label">សៀវភៅសរុប</div>
        <div class="kpi-sub">ចំណងជើងទាំងអស់</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-green">
      <div class="kpi-icon"><i class="bi bi-check2-all"></i></div>
      <div>
        <div class="kpi-value">{{ number_format($totalPrinted) }}</div>
        <div class="kpi-label">បានបោះពុម្ព</div>
        <div class="kpi-sub">{{ $overallPct }}% នៃគោលដៅ</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-amber">
      <div class="kpi-icon"><i class="bi bi-hourglass"></i></div>
      <div>
        <div class="kpi-value">{{ number_format($totalRemaining) }}</div>
        <div class="kpi-label">នៅសល់</div>
        <div class="kpi-sub">ត្រូវបន្តបោះពុម្ព</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-purple">
      <div class="kpi-icon"><i class="bi bi-trophy"></i></div>
      <div>
        <div class="kpi-value">{{ $doneCount }}</div>
        <div class="kpi-label">រួចរាល់</div>
        <div class="kpi-sub">{{ $inProgress }} កំពុង · {{ $notStarted }} មិនទាន់</div>
      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════
     PROGRESS + BREAKDOWN
════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

  {{-- Overall progress --}}
  <div class="col-lg-7">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-activity"></i></div>
          <span>ដំណើរការបោះពុម្ពរួម</span>
        </div>
      </div>
      <div class="panel-body">
        <div class="d-flex justify-content-between align-items-end mb-3">
          <div>
            <div style="font-family:var(--font-latin);font-size:2.5rem;font-weight:800;color:var(--primary);line-height:1">
              {{ $overallPct }}<span style="font-size:1.2rem">%</span>
            </div>
            <div class="text-sm text-muted mt-1">ការបោះពុម្ពសៀវភៅទាំងអស់</div>
          </div>
          <div style="text-align:right">
            <div class="text-xs text-muted">គោលដៅ</div>
            <div style="font-family:var(--font-latin);font-size:1.1rem;font-weight:700">{{ number_format($totalTarget) }}</div>
          </div>
        </div>
        <div class="prog-track" style="height:14px;border-radius:999px">
          <div class="prog-fill {{ $overallPct >= 80 ? 'green' : ($overallPct >= 40 ? '' : 'amber') }}"
               style="width:{{ $overallPct }}%"></div>
        </div>
        <div class="d-flex justify-content-between mt-3 gap-3 flex-wrap">
          <div style="text-align:center">
            <div style="font-family:var(--font-latin);font-size:1.1rem;font-weight:700;color:var(--success)">
              {{ number_format($totalPrinted) }}
            </div>
            <div class="text-xs text-muted">បានបោះពុម្ព</div>
          </div>
          <div style="text-align:center">
            <div style="font-family:var(--font-latin);font-size:1.1rem;font-weight:700;color:var(--warning)">
              {{ number_format($totalRemaining) }}
            </div>
            <div class="text-xs text-muted">នៅសល់</div>
          </div>
          <div style="text-align:center">
            <div style="font-family:var(--font-latin);font-size:1.1rem;font-weight:700;color:var(--purple)">
              {{ $doneCount }}
            </div>
            <div class="text-xs text-muted">ចំណងជើងរួច</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Breakdown donuts (CSS-only) --}}
  <div class="col-lg-5">
    <div class="panel h-100">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-pie-chart"></i></div>
          <span>សង្ខេបស្ថានភាព</span>
        </div>
      </div>
      <div class="panel-body d-flex flex-column gap-3">
        @php
          $items = [
            ['label'=>'រួចរាល់',   'count'=>$doneCount,  'pct'=> $totalBooks>0?round($doneCount/$totalBooks*100):0,  'color'=>'#10b981', 'bg'=>'#d1fae5', 'text'=>'#065f46'],
            ['label'=>'កំពុងបោះពុម្ព', 'count'=>$inProgress, 'pct'=> $totalBooks>0?round($inProgress/$totalBooks*100):0, 'color'=>'#f59e0b', 'bg'=>'#fef3c7', 'text'=>'#92400e'],
            ['label'=>'មិនទាន់បោះពុម្ព',   'count'=>$notStarted, 'pct'=> $totalBooks>0?round($notStarted/$totalBooks*100):0, 'color'=>'#ef4444', 'bg'=>'#fee2e2', 'text'=>'#991b1b'],
          ];
        @endphp
        @foreach($items as $item)
        <div>
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center gap-2">
              <span style="width:10px;height:10px;border-radius:50%;background:{{ $item['color'] }};flex-shrink:0;display:inline-block"></span>
              <span style="font-size:.85rem;font-weight:500">{{ $item['label'] }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span style="background:{{ $item['bg'] }};color:{{ $item['text'] }};font-family:var(--font-latin);font-size:.72rem;font-weight:700;padding:.15em .6em;border-radius:999px">
                {{ $item['count'] }} ចំណង
              </span>
              <span style="font-family:var(--font-latin);font-size:.8rem;font-weight:600;color:var(--text-muted);min-width:35px;text-align:right">
                {{ $item['pct'] }}%
              </span>
            </div>
          </div>
          <div class="prog-track" style="height:7px">
            <div style="height:100%;width:{{ $item['pct'] }}%;background:{{ $item['color'] }};border-radius:999px;transition:width .6s ease"></div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

</div>

{{-- ════════════════════════════════════════════
     TELEGRAM SEND
════════════════════════════════════════════ --}}
<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-send-fill"></i></div>
      <span>ផ្ញើរបាយការណ៍ទៅ Telegram</span>
    </div>
  </div>
  <div class="panel-body">
    @if($telegramGroups->isEmpty())
      <div class="alert-info-soft">
        <i class="bi bi-exclamation-circle-fill" style="font-size:1.1rem;flex-shrink:0;margin-top:.1rem"></i>
        <div>
          <strong>មិនទាន់មានក្រុម Telegram ។</strong>
          <span> សូម​បន្ថែម Bot ទៅក្នុងក្រុម ហើយ Webhook នឹង register ក្រុមដោយស្វ័យប្រវត្តិ។</span>
        </div>
      </div>
    @else
      <div class="d-flex gap-3 flex-wrap align-items-end">
        <div style="flex:1;min-width:220px;max-width:360px">
          <label class="form-label" for="telegramGroup">ជ្រើសរើសក្រុម</label>
          <select id="telegramGroup" class="form-select">
            <option value="">— ជ្រើសរើសក្រុម —</option>
            @foreach($telegramGroups as $group)
              <option value="{{ $group->chat_id }}">📌 {{ $group->name }}</option>
            @endforeach
          </select>
        </div>
        <button id="sendTelegramBtn" class="btn btn-success btn-lg">
          <i class="bi bi-send-fill"></i> ផ្ញើទៅ Telegram
        </button>
      </div>
      <p class="text-xs text-muted mt-3 mb-0">
        <i class="bi bi-image me-1"></i>
        របាយការណ៍ត្រូវបាន capture ជា PNG ហើយផ្ញើទៅក្រុម Telegram ដែលជ្រើស
      </p>
    @endif
  </div>
</div>

{{-- ════════════════════════════════════════════
     BOOKS TABLE
════════════════════════════════════════════ --}}
<div class="panel">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dbeafe;color:#1e40af"><i class="bi bi-table"></i></div>
      <span>បញ្ជីលម្អិតសៀវភៅ</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <select id="gradeFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
        <option value="">ថ្នាក់ទាំងអស់</option>
        @foreach($books->pluck('grade')->filter()->unique()->sort() as $g)
          <option value="{{ $g }}">{{ is_numeric($g) ? 'ថ្នាក់ '.$g : $g }}</option>
        @endforeach
      </select>
      <select id="categoryFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
        <option value="">ប្រភេទទាំងអស់</option>
        <option value="perfect_binding">បិតក្បាល</option>
        <option value="staple">កិបកណ្ដាល</option>
      </select>
      <select id="statusFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
        <option value="">ស្ថានភាពទាំងអស់</option>
        <option value="done">រួចរាល់</option>
        <option value="progress">កំពុងបោះពុម្ព</option>
        <option value="pending">មិនទាន់បោះពុម្ព</option>
      </select>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="data-table" id="booksTable">
      <thead>
        <tr>
          <th style="width:40px;text-align:center">
            <span class="th-km">#</span>
            <span class="th-en">No.</span>
          </th>
          <th style="min-width:170px">
            <span class="th-km">ឈ្មោះសៀវភៅ</span>
            <span class="th-en">Book Title</span>
          </th>
          <th class="col-center">
            <span class="th-km">ថ្នាក់</span>
            <span class="th-en">Grade</span>
          </th>
          <th>
            <span class="th-km">ប្រភេទ</span>
            <span class="th-en">Category</span>
          </th>
          <th class="col-right">
            <span class="th-km">គោលដៅ</span>
            <span class="th-en">Target Qty</span>
          </th>
          <th class="col-right">
            <span class="th-km">បោះពុម្ព</span>
            <span class="th-en">Printed</span>
          </th>
          <th class="col-right">
            <span class="th-km">នៅសល់</span>
            <span class="th-en">Remaining</span>
          </th>
          <th style="min-width:160px">
            <span class="th-km">ដំណើរការ</span>
            <span class="th-en">Progress</span>
          </th>
          <th class="col-center">
            <span class="th-km">ស្ថានភាព</span>
            <span class="th-en">Status</span>
          </th>
        </tr>
      </thead>
      <tbody>
        @forelse($books as $i => $book)
          @php
            $remaining = max($book->target_qty - $book->total_printed, 0);
            $pct       = $book->target_qty > 0 ? round($book->total_printed / $book->target_qty * 100) : 0;
            $isDone    = $book->total_printed >= $book->target_qty;
            $catLabel  = $book->category === 'perfect_binding' ? 'បិតក្បាល'
                       : ($book->category === 'staple' ? 'កិបកណ្ដាល' : $book->category);
            $catClass  = $book->category === 'perfect_binding' ? 'badge-binding' : 'badge-staple';
            $gradeDisp  = $book->grade
              ? (is_numeric($book->grade) ? 'ថ្នាក់ '.$book->grade : $book->grade)
              : '—';
            $gradeClass = $book->grade
              ? (is_numeric($book->grade) ? 'grade-badge grade-num' : 'grade-badge grade-primary')
              : 'grade-badge';

            if ($isDone) {
              $sk = 'done';     $sl = 'រួចរាល់';          $sc = 'badge-done';     $bc = 'green';
            } elseif ($book->total_printed > 0) {
              $sk = 'progress'; $sl = 'កំពុងបោះពុម្ព';    $sc = 'badge-progress'; $bc = '';
            } else {
              $sk = 'pending';  $sl = 'មិនទាន់បោះពុម្ព';  $sc = 'badge-pending';  $bc = 'amber';
            }
            $remStyle = $remaining === 0
              ? 'color:var(--success);font-weight:700'
              : ($pct < 40 ? 'color:var(--danger);font-weight:700'
                : ($pct < 70 ? 'color:var(--warning);font-weight:600'
                  : 'color:var(--text-secondary)'));
          @endphp
          <tr data-category="{{ $book->category }}"
              data-status="{{ $sk }}"
              data-grade="{{ $book->grade ?? '' }}">
            <td style="text-align:center;font-family:var(--font-latin);font-size:.78rem;color:var(--text-muted);font-weight:600">{{ $i + 1 }}</td>
            <td style="font-weight:700;font-size:.88rem">{{ $book->title }}</td>
            <td style="text-align:center"><span class="{{ $gradeClass }}">{{ $gradeDisp }}</span></td>
            <td><span class="badge {{ $catClass }}">{{ $catLabel }}</span></td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:600">{{ number_format($book->target_qty) }}</td>
            <td style="text-align:right;font-family:var(--font-latin);font-weight:600;color:var(--success)">{{ number_format($book->total_printed) }}</td>
            <td style="text-align:right;font-family:var(--font-latin);{{ $remStyle }}">{{ number_format($remaining) }}</td>
            <td>
              <div class="prog-cell">
                <div class="prog-track"><div class="prog-fill {{ $bc }}" style="width:{{ $pct }}%"></div></div>
                <span class="prog-num">{{ $pct }}%</span>
              </div>
            </td>
            <td style="text-align:center"><span class="badge {{ $sc }}">{{ $sl }}</span></td>
          </tr>
        @empty
          <tr>
            <td colspan="9">
              <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                <p style="font-weight:600;margin:0">មិនទាន់មានទិន្នន័យ</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ── Hidden Telegram snapshot (font: Hanuman) ── --}}
<div id="telegramReport" aria-hidden="true" style="
    position:fixed; left:-9999px; top:0; width:640px;
    padding:28px; background:#f8fafc;
    font-family:'Hanuman','Poppins',sans-serif;
    border-radius:16px; color:#0f172a;">

  <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;
              background:linear-gradient(135deg,#4f46e5,#6366f1);
              border-radius:12px;padding:18px 20px;color:#fff">
    <div style="font-size:2rem">🖨️</div>
    <div>
      <div style="font-size:17px;font-weight:700;margin-bottom:3px;font-family:'Hanuman',sans-serif">
        របាយការណ៍ការបោះពុម្ព
      </div>
      <div style="font-size:12px;opacity:.85;font-family:'Poppins',sans-serif">
        📅 {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; {{ $overallPct }}% Complete
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px">
    <div style="background:#dbeafe;border-radius:10px;padding:12px;text-align:center">
      <div style="font-size:11px;color:#1e40af;margin-bottom:4px;font-family:'Hanuman',sans-serif">📚 សៀវភៅ</div>
      <div style="font-size:24px;font-weight:800;color:#1d4ed8;font-family:'Poppins',sans-serif">{{ $totalBooks }}</div>
    </div>
    <div style="background:#d1fae5;border-radius:10px;padding:12px;text-align:center">
      <div style="font-size:11px;color:#065f46;margin-bottom:4px;font-family:'Hanuman',sans-serif">✅ បោះពុម្ព</div>
      <div style="font-size:24px;font-weight:800;color:#059669;font-family:'Poppins',sans-serif">{{ number_format($totalPrinted) }}</div>
    </div>
    <div style="background:#fef3c7;border-radius:10px;padding:12px;text-align:center">
      <div style="font-size:11px;color:#92400e;margin-bottom:4px;font-family:'Hanuman',sans-serif">⏳ នៅសល់</div>
      <div style="font-size:24px;font-weight:800;color:#d97706;font-family:'Poppins',sans-serif">{{ number_format($totalRemaining) }}</div>
    </div>
  </div>

  <div style="margin-bottom:18px">
    <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px;font-family:'Hanuman',sans-serif">
      <span>ដំណើរការរួម</span>
      <span style="font-family:'Poppins',sans-serif;font-weight:700;color:#4f46e5">{{ $overallPct }}%</span>
    </div>
    <div style="height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden">
      <div style="height:100%;width:{{ $overallPct }}%;background:linear-gradient(90deg,#4f46e5,#6366f1);border-radius:999px"></div>
    </div>
  </div>

  <table style="width:100%;border-collapse:collapse;font-size:12px">
    <thead>
      <tr style="background:#e2e8f0">
        <th style="padding:7px 10px;text-align:left;border-radius:6px 0 0 6px;font-family:'Hanuman',sans-serif;font-size:12px;color:#1e293b">ឈ្មោះសៀវភៅ</th>
        <th style="padding:7px 10px;text-align:center;font-family:'Hanuman',sans-serif;font-size:12px;color:#1e293b">ថ្នាក់</th>
        <th style="padding:7px 10px;text-align:center;font-family:'Poppins',sans-serif;font-size:10px;color:#475569;text-transform:uppercase;letter-spacing:.05em">Target</th>
        <th style="padding:7px 10px;text-align:center;font-family:'Poppins',sans-serif;font-size:10px;color:#475569;text-transform:uppercase;letter-spacing:.05em">Printed</th>
        <th style="padding:7px 10px;text-align:center;font-family:'Poppins',sans-serif;font-size:10px;color:#475569;text-transform:uppercase;letter-spacing:.05em">Left</th>
        <th style="padding:7px 10px;text-align:center;border-radius:0 6px 6px 0;font-family:'Hanuman',sans-serif;font-size:12px;color:#1e293b">ស្ថានភាព</th>
      </tr>
    </thead>
    <tbody>
      @foreach($books as $book)
        @php
          $rem = max($book->target_qty - $book->total_printed, 0);
          $grDisp = $book->grade ? (is_numeric($book->grade) ? 'ថ្នាក់ '.$book->grade : $book->grade) : '—';
          if ($book->total_printed >= $book->target_qty) {
            $st = 'រួចរាល់';          $stBg = '#d1fae5'; $stColor = '#065f46';
          } elseif ($book->total_printed > 0) {
            $st = 'កំពុងបោះពុម្ព';    $stBg = '#fef3c7'; $stColor = '#92400e';
          } else {
            $st = 'មិនទាន់បោះពុម្ព'; $stBg = '#fee2e2'; $stColor = '#991b1b';
          }
        @endphp
        <tr style="border-bottom:1px solid #f1f5f9">
          <td style="padding:6px 10px;font-family:'Hanuman',sans-serif">{{ $book->title }}</td>
          <td style="padding:6px 10px;text-align:center;font-family:'Hanuman',sans-serif;font-size:11px">{{ $grDisp }}</td>
          <td style="padding:6px 10px;text-align:center;font-family:'Poppins',sans-serif">{{ number_format($book->target_qty) }}</td>
          <td style="padding:6px 10px;text-align:center;font-family:'Poppins',sans-serif;color:#059669;font-weight:600">{{ number_format($book->total_printed) }}</td>
          <td style="padding:6px 10px;text-align:center;font-family:'Poppins',sans-serif">{{ number_format($rem) }}</td>
          <td style="padding:6px 10px;text-align:center">
            <span style="background:{{ $stBg }};color:{{ $stColor }};padding:.2em .6em;border-radius:6px;font-size:11px;font-weight:600;font-family:'Hanuman',sans-serif">{{ $st }}</span>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <p style="text-align:center;color:#94a3b8;font-size:11px;margin-top:16px;margin-bottom:0;font-family:'Poppins',sans-serif">
    Generated by PrintTracker System
  </p>
</div>

@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
@endpush

@push('scripts')
<script>
(function () {
  const rows         = document.querySelectorAll('#booksTable tbody tr[data-category]');
  const gradeFilter  = document.getElementById('gradeFilter');
  const catFilter    = document.getElementById('categoryFilter');
  const statusFilter = document.getElementById('statusFilter');

  function applyFilters() {
    const grade  = gradeFilter?.value;
    const cat    = catFilter?.value;
    const status = statusFilter?.value;
    rows.forEach(row => {
      row.style.display = ((!grade  || row.dataset.grade    === grade)  &&
                           (!cat    || row.dataset.category === cat)    &&
                           (!status || row.dataset.status   === status)) ? '' : 'none';
    });
  }

  gradeFilter?.addEventListener('change', applyFilters);
  catFilter?.addEventListener('change', applyFilters);
  statusFilter?.addEventListener('change', applyFilters);

  /* ── Telegram send ── */
  const sendBtn     = document.getElementById('sendTelegramBtn');
  const groupSelect = document.getElementById('telegramGroup');
  const reportEl    = document.getElementById('telegramReport');

  sendBtn?.addEventListener('click', async () => {
    const chatId = groupSelect?.value;
    if (!chatId) { showToast('warning', 'សូមជ្រើសរើសក្រុម Telegram មុន'); return; }

    showLoading(true);

    try {
      const canvas = await html2canvas(reportEl, { scale: 2, useCORS: true, logging: false });

      await new Promise((resolve, reject) => {
        canvas.toBlob(async blob => {
          if (!blob) { reject(new Error('Capture failed')); return; }

          const fd = new FormData();
          fd.append('chat_id', chatId);
          fd.append('photo', blob, 'report.png');
          fd.append('caption', '📄 របាយការណ៍ការបោះពុម្ព\n📅 ' + new Date().toLocaleDateString('km-KH'));
          fd.append('_token', document.querySelector('meta[name=csrf-token]').content);

          const res  = await fetch('{{ route("telegram.send.image") }}', { method: 'POST', body: fd });
          const data = await res.json();
          (res.ok && data.ok) ? resolve() : reject(new Error(data.message || 'Server error'));
        }, 'image/png');
      });

      showLoading(false);
      showToast('success', 'ផ្ញើរបាយការណ៍ទៅ Telegram បានដោយជោគជ័យ 🎉');
    } catch (err) {
      showLoading(false);
      showToast('error', 'ផ្ញើមិនបាន: ' + err.message);
    }
  });
})();
</script>
@endpush
