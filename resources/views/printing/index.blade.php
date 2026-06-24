@extends('layouts.app')
@section('title',      'ការបោះពុម្ព')
@section('page-title', 'Production Management')

@section('content')

@php
  $totalBooks     = $books->count();
  $totalPrinted   = $books->sum('total_printed');
  $totalTarget    = $books->sum('target_qty');
  $totalRemaining = max($totalTarget - $totalPrinted, 0);
  $overallPct     = $totalTarget > 0 ? floor($totalPrinted / $totalTarget * 100) : 0;
  $doneCount      = $books->filter(fn($b) => $b->total_printed >= $b->target_qty)->count();
  $inProgress     = $books->filter(fn($b) => $b->total_printed > 0 && $b->total_printed < $b->target_qty)->count();

  function gradeBadge(?string $grade): array {
    if (!$grade) return ['grade-badge', '—'];
    if (is_numeric($grade)) return ['grade-badge grade-num', 'ថ្នាក់ '.$grade];
    return ['grade-badge grade-primary', $grade];
  }
@endphp

{{-- ════  PAGE HEADER  ════ --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">ការបោះពុម្ព</h1>
    <p class="section-sub">គ្រប់គ្រង និងតាមដានការបោះពុម្ពសៀវភៅ</p>
  </div>
  <div class="d-flex gap-2 flex-wrap align-items-center">
    {{-- Current batch indicator --}}
    <span style="display:inline-flex;align-items:center;gap:.4rem;background:#eef2ff;
                 border:1px solid #c7d2fe;color:#4338ca;padding:.35rem .75rem;
                 border-radius:999px;font-size:.8rem;font-weight:700">
      <i class="bi bi-layers-fill"></i>
      {{ $currentBatch->name }}
      <span style="font-weight:400;font-size:.72rem;opacity:.8">(active)</span>
    </span>

    {{-- Batch history dropdown --}}
    @if($allBatches->where('status','completed')->count() > 0)
    <div class="dropdown">
      <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-clock-history"></i> ប្រវត្តិ Batch
      </button>
      <ul class="dropdown-menu dropdown-menu-end" style="min-width:220px">
        <li><h6 class="dropdown-header">Completed Batches — ចុច Switch ដើម្បីត្រឡប់</h6></li>
        @foreach($allBatches->where('status','completed') as $b)
          <li class="px-2 py-1">
            <div class="d-flex align-items-center justify-content-between gap-2">
              <a class="text-decoration-none flex-grow-1" href="{{ route('printing.batch-history', $b) }}"
                 style="font-size:.82rem;color:var(--text-primary)">
                <i class="bi bi-layers me-1"></i>{{ $b->name }}
                <span style="font-size:.68rem;color:#94a3b8">{{ $b->completed_at?->format('d/m/y') }}</span>
              </a>
              <div class="d-flex gap-1">
                <form action="{{ route('printing.batch-restore', $b) }}" method="POST" class="m-0"
                      onsubmit="return confirm('ប្ដូរទៅ {{ $b->name }}? Batch បច្ចុប្បន្ននឹងត្រូវរក្សាទុក។')">
                  @csrf
                  <button class="btn btn-sm btn-outline-success py-0 px-2" type="submit" title="Switch to this batch">
                    <i class="bi bi-box-arrow-in-left"></i> Switch
                  </button>
                </form>
                <form action="{{ route('printing.batch-delete', $b) }}" method="POST" class="m-0"
                      onsubmit="return confirm('⚠️ លុប {{ $b->name }} ជាអចិន្ត្រៃយ៍? សៀវភៅ និងលទ្ធផលរបស់វានឹងបាត់បង់ទាំងស្រុង។')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger py-0 px-2" type="submit" title="Delete this batch">
                    <i class="bi bi-trash3"></i>
                  </button>
                </form>
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    </div>
    @endif

    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#newBatchModal">
      <i class="bi bi-arrow-repeat"></i> ចាប់ផ្ដើម Batch ថ្មី
    </button>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBookModal">
      <i class="bi bi-plus-lg"></i> បន្ថែមសៀវភៅ
    </button>
    <a href="{{ route('printing.report') }}" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-bar-chart-line"></i> របាយការណ៍
    </a>
  </div>
</div>

{{-- ════  KPI CARDS  ════ --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-blue">
      <div class="kpi-icon"><i class="bi bi-journals"></i></div>
      <div>
        <div class="kpi-value">{{ $totalBooks }}</div>
        <div class="kpi-label">ចំនួនសៀវភៅ</div>
        <div class="kpi-sub">Total Books</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-green">
      <div class="kpi-icon"><i class="bi bi-check2-circle"></i></div>
      <div>
        <div class="kpi-value">{{ number_format($totalPrinted) }}</div>
        <div class="kpi-label">បានបោះពុម្ព</div>
        <div class="kpi-sub">{{ $overallPct }}% of target</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-amber">
      <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
      <div>
        <div class="kpi-value">{{ number_format($totalRemaining) }}</div>
        <div class="kpi-label">នៅសល់</div>
        <div class="kpi-sub">Copies remaining</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="kpi-card kpi-purple">
      <div class="kpi-icon"><i class="bi bi-trophy"></i></div>
      <div>
        <div class="kpi-value">{{ $doneCount }}</div>
        <div class="kpi-label">រួចរាល់</div>
        <div class="kpi-sub">{{ $inProgress }} in progress</div>
      </div>
    </div>
  </div>
</div>

{{-- Overall progress bar --}}
<div class="panel mb-4">
  <div class="panel-body" style="padding:1.1rem 1.5rem">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span style="font-weight:700;font-size:.9rem">ដំណើរការបោះពុម្ពរួម</span>
      <span style="font-family:var(--font-latin);font-size:.82rem;font-weight:700;color:var(--primary)">
        {{ number_format($totalPrinted) }} / {{ number_format($totalTarget) }} · {{ $overallPct }}%
      </span>
    </div>
    <div class="prog-track" style="height:12px">
      <div class="prog-fill {{ $overallPct>=80?'green':($overallPct>=40?'':'amber') }}"
           style="width:{{ $overallPct }}%"></div>
    </div>
    <div class="d-flex justify-content-between mt-1"
         style="font-size:.68rem;color:var(--text-muted);font-family:var(--font-latin)">
      <span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span>
    </div>
  </div>
</div>

{{-- ════  TWO COLUMNS: FORM + TABLE  ════ --}}
<div class="row g-4">

  {{-- LEFT: CSV Import + Daily Print --}}
  <div class="col-xl-4 col-lg-5">

    {{-- CSV Import --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706">
            <i class="bi bi-file-earmark-spreadsheet"></i>
          </div>
          <span>Import CSV</span>
        </div>
        <button class="btn btn-ghost btn-icon" type="button"
                data-bs-toggle="collapse" data-bs-target="#csvPanel">
          <i class="bi bi-chevron-down" id="csv-chevron" style="transition:transform .2s"></i>
        </button>
      </div>
      <div class="collapse" id="csvPanel">
        <div class="panel-body">
          <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.8">
            Format:
            <code style="font-family:var(--font-latin);background:#f1f5f9;padding:.1em .4em;border-radius:4px;font-size:.72rem">
              title, category, target_qty, total_printed, grade
            </code>
            <br>
            <span style="font-size:.72rem">
              Category: <code style="font-family:var(--font-latin);background:#f1f5f9;padding:.1em .35em;border-radius:3px">perfect_binding</code>
              ឬ <code style="font-family:var(--font-latin);background:#f1f5f9;padding:.1em .35em;border-radius:3px">staple</code>
              &nbsp;·&nbsp; Max file size: 20 MB
              &nbsp;·&nbsp; Encoding: UTF-8
            </span>
          </p>

          {{-- Skip warnings --}}
          @if(session('csv_skipped'))
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);
                        padding:.75rem 1rem;margin-bottom:.85rem">
              <div style="font-weight:700;font-size:.8rem;color:#92400e;margin-bottom:.35rem">
                <i class="bi bi-exclamation-triangle me-1"></i>
                ជួរខ្លះត្រូវបាន Skip:
              </div>
              @foreach(session('csv_skipped') as $s)
                <div style="font-size:.75rem;color:#b45309;font-family:var(--font-latin)">• {{ $s }}</div>
              @endforeach
            </div>
          @endif

          <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label class="form-label">ជ្រើសឯកសារ (.csv / .txt)</label>
              <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
            </div>
            <button class="btn btn-warning w-100" type="submit">
              <i class="bi bi-upload"></i> Import CSV
            </button>
          </form>
        </div>
      </div>
    </div>

    {{-- Daily Print Form --}}
    <div class="panel" id="dailyPrintPanel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#ede9fe;color:#7c3aed">
            <i class="bi bi-printer-fill"></i>
          </div>
          <span>បញ្ចូលចំនួនបោះពុម្ព</span>
        </div>
        <span style="font-family:var(--font-latin);font-size:.72rem;background:#eff6ff;
                     color:#1d4ed8;border:1px solid #bfdbfe;padding:.25em .65em;
                     border-radius:6px;font-weight:600">
          {{ now()->format('d/m/Y') }}
        </span>
      </div>
      <div class="panel-body">
        <form id="dailyPrintForm" action="{{ route('printing.store') }}" method="POST">
          @csrf

          {{-- Book preview --}}
          <div id="bookPreview"
               style="display:none;background:var(--surface-2);border-radius:var(--radius);
                      border:1px solid var(--border);padding:.85rem 1rem;margin-bottom:1rem">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;
                        gap:.5rem;margin-bottom:.5rem">
              <div style="min-width:0">
                <div id="previewTitle"
                     style="font-weight:700;font-size:.9rem;line-height:1.35;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis"></div>
                <div style="display:flex;align-items:center;gap:.4rem;margin-top:.3rem;flex-wrap:wrap">
                  <span id="previewCatBadge"  class="badge"></span>
                  <span id="previewGradeBadge" class="grade-badge" style="font-size:.72rem"></span>
                </div>
              </div>
              <span id="previewStatusBadge" class="badge" style="flex-shrink:0"></span>
            </div>
            <div class="prog-track" style="height:8px">
              <div class="prog-fill" id="previewBar" style="width:0%"></div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:.35rem;
                        font-family:var(--font-latin);font-size:.7rem;color:var(--text-muted)">
              <span>0</span>
              <span id="previewPct" style="font-weight:700;color:var(--primary)">0%</span>
              <span id="previewTarget">0</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="bookSelect">ឈ្មោះសៀវភៅ</label>
            <select id="bookSelect" name="book_id" class="form-select" required>
              @foreach($books as $book)
                @php
                  $catLbl    = $book->category==='perfect_binding'?'បិតក្បាល':'កិបកណ្ដាល';
                  $rem       = max($book->target_qty - $book->total_printed, 0);
                  $pct       = $book->target_qty > 0 ? floor($book->total_printed/$book->target_qty*100) : 0;
                  $gradeDisp = $book->grade ? 'ថ្នាក់'.$book->grade : '';
                @endphp
                <option value="{{ $book->id }}"
                        data-remaining="{{ $rem }}"
                        data-target="{{ $book->target_qty }}"
                        data-printed="{{ $book->total_printed }}"
                        data-pct="{{ $pct }}"
                        data-cat="{{ $catLbl }}"
                        data-cat-class="{{ $book->category==='perfect_binding'?'badge-binding':'badge-staple' }}"
                        data-grade="{{ $book->grade ?? '' }}"
                        data-grade-disp="{{ $gradeDisp }}"
                        data-done="{{ $book->total_printed>=$book->target_qty?'1':'0' }}"
                        data-title="{{ $book->title }}">
                  {{ $book->title }}{{ $gradeDisp?' · '.$gradeDisp:'' }} · {{ $catLbl }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label" for="printedInput">
              ចំនួនបោះពុម្ពថ្ងៃនេះ
              <span style="float:right;font-weight:400;color:var(--text-muted)">
                នៅសល់:
                <strong id="remainingQty" style="color:var(--primary);font-family:var(--font-latin)">0</strong>
              </span>
            </label>
            <div style="display:flex;gap:.5rem;align-items:center">
              <button type="button" id="decBtn" class="btn btn-ghost btn-icon"
                      style="border:1.5px solid var(--border-dark);flex-shrink:0">
                <i class="bi bi-dash-lg"></i>
              </button>
              <input id="printedInput" type="number" name="printed_today"
                     class="form-control" value="1" min="1"
                     style="text-align:center;font-family:var(--font-latin);
                            font-weight:700;font-size:1.1rem" required>
              <button type="button" id="incBtn" class="btn btn-ghost btn-icon"
                      style="border:1.5px solid var(--border-dark);flex-shrink:0">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>

          <button class="btn btn-primary w-100 btn-lg" type="submit" id="submitBtn">
            <i class="bi bi-save2-fill"></i> រក្សាទុក
          </button>
        </form>
      </div>
    </div>

  </div>{{-- /left --}}

  {{-- RIGHT: Books table --}}
  <div class="col-xl-8 col-lg-7">
    <div class="panel" style="display:flex;flex-direction:column;height:100%">

      {{-- BATCH TOOLBAR --}}
      <div id="batchBar" style="display:none;background:#1e293b;color:#fff;padding:.6rem 1.25rem;
           border-radius:8px;margin:.75rem 1rem;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <span style="font-size:.78rem;font-weight:700;opacity:.85"><i class="bi bi-check2-square me-1"></i>Bulk Update:</span>
        <span id="batchSelCount" style="font-size:.82rem;font-weight:600;">0 selected</span>
        <button class="btn btn-sm btn-success" onclick="openBatchModal('set_done')">
          <i class="bi bi-check-circle-fill me-1"></i>Mark All Done (100%)
        </button>
        <button class="btn btn-sm btn-warning" onclick="openBatchModal('add')">
          <i class="bi bi-plus-circle me-1"></i>Add Copies to Each
        </button>
        <button class="btn btn-sm btn-info text-white" onclick="openBatchModal('set_progress')">
          <i class="bi bi-pencil me-1"></i>Set Exact Printed Qty
        </button>
        <button class="btn btn-sm btn-outline-light ms-auto" onclick="clearBatchSelection()">
          <i class="bi bi-x-lg"></i> Clear
        </button>
      </div>

      <div class="panel-header" style="flex-wrap:wrap;gap:.6rem">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-table"></i></div>
          <span>បញ្ជីសៀវភៅ</span>
          <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ $totalBooks }}</span>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-left:auto">
          {{-- Search --}}
          <div style="position:relative">
            <i class="bi bi-search"
               style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);
                      color:var(--text-muted);font-size:.8rem;pointer-events:none"></i>
            <input id="searchInput" type="text" class="form-control form-control-sm"
                   placeholder="ស្វែងរក..." style="padding-left:2rem;width:140px;border-radius:999px">
          </div>
          <select id="gradeFilter" class="form-select form-select-sm" style="width:auto;border-radius:999px">
            <option value="">ថ្នាក់ទាំងអស់</option>
            @foreach($books->pluck('grade')->filter()->unique()->sort() as $g)
              <option value="{{ $g }}">{{ is_numeric($g)?'ថ្នាក់ '.$g:$g }}</option>
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
            <option value="progress">កំពុងបោះ</option>
            <option value="pending">មិនទាន់</option>
          </select>
        </div>
      </div>

      <div class="tbl-wrap" style="flex:1">
        <table class="data-table" id="booksTable">
          <thead>
            <tr>
              <th style="width:32px;text-align:center;padding:.4rem .3rem">
                <input type="checkbox" id="selectAll" class="form-check-input"
                       style="width:15px;height:15px;cursor:pointer" title="Select all">
              </th>
              <th style="width:44px;text-align:center">
                <span class="th-km">#</span><span class="th-en">No.</span>
              </th>
              <th style="min-width:160px">
                <span class="th-km">ឈ្មោះសៀវភៅ</span><span class="th-en">Book Title</span>
              </th>
              <th class="col-center">
                <span class="th-km">ថ្នាក់</span><span class="th-en">Grade</span>
              </th>
              <th>
                <span class="th-km">ប្រភេទ</span><span class="th-en">Category</span>
              </th>
              <th class="col-right">
                <span class="th-km">គោលដៅ</span><span class="th-en">Target</span>
              </th>
              <th class="col-right">
                <span class="th-km">បោះពុម្ព</span><span class="th-en">Printed</span>
              </th>
              <th class="col-right">
                <span class="th-km">នៅសល់</span><span class="th-en">Remaining</span>
              </th>
              <th style="min-width:150px">
                <span class="th-km">ដំណើរការ</span><span class="th-en">Progress</span>
              </th>
              <th class="col-center">
                <span class="th-km">ស្ថានភាព</span><span class="th-en">Status</span>
              </th>
              <th class="col-center" style="width:70px">
                <span class="th-km">Edit</span><span class="th-en">Action</span>
              </th>
            </tr>
          </thead>
          <tbody>
            @forelse($books as $i => $book)
              @php
                $remaining = max($book->target_qty - $book->total_printed, 0);
                $pct       = $book->target_qty > 0 ? floor($book->total_printed/$book->target_qty*100) : 0;
                $isDone    = $book->total_printed >= $book->target_qty;
                $catLabel  = $book->category==='perfect_binding' ? 'បិតក្បាល' : 'កិបកណ្ដាល';
                $catClass  = $book->category==='perfect_binding' ? 'badge-binding' : 'badge-staple';
                [$gradeClass,$gradeDisp] = gradeBadge($book->grade);

                if ($isDone)                   { $sk='done';     $sl='រួចរាល់';       $sc='badge-done';     $bc='green'; }
                elseif ($book->total_printed>0){ $sk='progress'; $sl='កំពុងបោះពុម្ព'; $sc='badge-progress'; $bc=''; }
                else                           { $sk='pending';  $sl='មិនទាន់បោះ';    $sc='badge-pending';  $bc='amber'; }

                $remStyle = $remaining===0
                  ? 'color:var(--success);font-weight:700'
                  : ($pct<40?'color:var(--danger);font-weight:700'
                    :($pct<70?'color:var(--warning);font-weight:600':'color:var(--text-secondary)'));
              @endphp
              <tr class="row-select"
                  data-book-id="{{ $book->id }}"
                  data-target="{{ $book->target_qty }}"
                  data-printed="{{ $book->total_printed }}"
                  data-title-text="{{ $book->title }}"
                  data-category="{{ $book->category }}"
                  data-status="{{ $sk }}"
                  data-grade="{{ $book->grade ?? '' }}"
                  data-title="{{ strtolower($book->title) }}">

                <td style="text-align:center;padding:.3rem .3rem" onclick="event.stopPropagation()">
                  <input type="checkbox" class="form-check-input row-check"
                         data-id="{{ $book->id }}"
                         data-target="{{ $book->target_qty }}"
                         data-printed="{{ $book->total_printed }}"
                         data-title="{{ $book->title }}"
                         style="width:15px;height:15px;cursor:pointer">
                </td>

                <td style="text-align:center;font-family:var(--font-latin);font-size:.78rem;
                           color:var(--text-muted);font-weight:600">{{ $i+1 }}</td>

                <td>
                  <div style="font-weight:700;font-size:.88rem;line-height:1.3">{{ $book->title }}</div>
                </td>

                <td style="text-align:center">
                  <span class="{{ $gradeClass }}">{{ $gradeDisp }}</span>
                </td>

                <td><span class="badge {{ $catClass }}">{{ $catLabel }}</span></td>

                <td style="text-align:right;font-family:var(--font-latin);font-weight:600;font-size:.88rem">
                  {{ number_format($book->target_qty) }}
                </td>

                <td style="text-align:right;font-family:var(--font-latin);font-weight:600;
                           font-size:.88rem;color:var(--success)">
                  {{ number_format($book->total_printed) }}
                </td>

                <td style="text-align:right;font-family:var(--font-latin);font-size:.88rem;{{ $remStyle }}">
                  {{ number_format($remaining) }}
                </td>

                <td>
                  <div class="prog-cell">
                    <div class="prog-track">
                      <div class="prog-fill {{ $bc }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="prog-num">{{ $pct }}%</span>
                  </div>
                </td>

                <td style="text-align:center">
                  <span class="badge {{ $sc }}">{{ $sl }}</span>
                </td>

                <td style="text-align:center">
                  <div class="d-flex gap-1 justify-content-center">
                    {{-- Edit --}}
                    <button class="btn btn-ghost btn-sm edit-book-btn"
                            data-id="{{ $book->id }}"
                            data-title="{{ $book->title }}"
                            data-category="{{ $book->category }}"
                            data-grade="{{ $book->grade ?? '' }}"
                            data-target="{{ $book->target_qty }}"
                            title="Edit">
                      <i class="bi bi-pencil" style="color:var(--primary)"></i>
                    </button>
                    {{-- Delete --}}
                    <form action="{{ route('books.destroy', $book) }}" method="POST"
                          onsubmit="return confirm('លុបសៀវភៅ « {{ addslashes($book->title) }} »?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-ghost btn-sm" type="submit" title="Delete">
                        <i class="bi bi-trash3" style="color:var(--danger)"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10">
                  <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                    <p style="font-weight:700;margin:0">មិនទាន់មានសៀវភៅ</p>
                    <p class="text-sm text-muted" style="margin:0">
                      Import CSV ឬចុច «បន្ថែមសៀវភៅ»
                    </p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div style="padding:.6rem 1.5rem;border-top:1px solid var(--border);background:var(--surface-2)">
        <span style="font-size:.74rem;color:var(--text-muted)">
          <i class="bi bi-hand-index me-1"></i>ចុចលើជួរដើម្បីជ្រើសសៀវភៅ
        </span>
      </div>
    </div>
  </div>

</div>{{-- /row --}}

{{-- ════  ADD BOOK MODAL  ════ --}}
<div class="modal fade" id="addBookModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form action="{{ route('books.store') }}" method="POST">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">
            <i class="bi bi-book me-2" style="color:var(--primary)"></i>បន្ថែមសៀវភៅថ្មី
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">ចំណងជើងសៀវភៅ *</label>
              <input type="text" name="title" class="form-control"
                     placeholder="ឈ្មោះសៀវភៅ" required>
            </div>
            <div class="col-6">
              <label class="form-label">ប្រភេទ *</label>
              <select name="category" class="form-select" required>
                <option value="perfect_binding">សៀវភៅ បិតក្បាល</option>
                <option value="staple">សៀវភៅ កិបកណ្ដាល</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">ថ្នាក់ / Grade</label>
              <input type="text" name="grade" class="form-control"
                     placeholder="1, 2, ... 12, មត្តេយ្យ">
            </div>
            <div class="col-12">
              <label class="form-label">គោលដៅ (Target Qty) *</label>
              <input type="number" name="target_qty" class="form-control"
                     placeholder="0" min="1"
                     style="font-family:var(--font-latin)" required>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm"
                  data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> បន្ថែម
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ════  EDIT BOOK MODAL  ════ --}}
<div class="modal fade" id="editBookModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form id="editBookForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">
            <i class="bi bi-pencil-square me-2" style="color:var(--primary)"></i>កែប្រែសៀវភៅ
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">ចំណងជើង *</label>
              <input type="text" id="editTitle" name="title" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">ប្រភេទ *</label>
              <select id="editCategory" name="category" class="form-select" required>
                <option value="perfect_binding">សៀវភៅ បិតក្បាល</option>
                <option value="staple">សៀវភៅ កិបកណ្ដាល</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">ថ្នាក់ / Grade</label>
              <input type="text" id="editGrade" name="grade" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">គោលដៅ *</label>
              <input type="number" id="editTarget" name="target_qty" class="form-control"
                     min="1" style="font-family:var(--font-latin)" required>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm"
                  data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-check-lg"></i> រក្សាទុក
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- BATCH CONFIRM MODAL --}}
<div class="modal fade" id="batchModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <div class="modal-header" style="background:#f0fdf4;border-bottom:1px solid var(--border)">
        <h6 class="modal-title" id="batchModalTitle">
          <i class="bi bi-check2-square text-success me-2"></i>Batch Update
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="batchModeDesc" class="alert alert-info mb-3" style="font-size:.82rem;"></div>
        {{-- add / set_progress modes show amount input --}}
        <div id="batchAmountWrap" style="display:none">
          <label class="form-label">
            <span id="batchAmountLabel">Amount per book</span>
          </label>
          <input type="number" id="batchAmount" class="form-control" min="0" value="100"
                 style="font-family:var(--font-latin);font-size:1.1rem;font-weight:700;width:140px">
        </div>
        <div id="batchPreviewList" style="max-height:220px;overflow-y:auto;margin-top:1rem;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary" id="batchConfirmBtn" onclick="submitBatch()">
          <i class="bi bi-check-lg me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ════  START NEW BATCH MODAL  ════ --}}
<div class="modal fade" id="newBatchModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form action="{{ route('printing.new-batch') }}" method="POST">
        @csrf
        <div class="modal-header" style="background:#f0fdf4;border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">
            <i class="bi bi-arrow-repeat me-2 text-success"></i>ចាប់ផ្ដើម Batch ថ្មី
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning" style="font-size:.82rem">
            <i class="bi bi-info-circle me-1"></i>
            <strong>{{ $currentBatch->name }}</strong> បច្ចុប្បន្ននឹងត្រូវបានបិទ ហើយរក្សាទុកក្នុងប្រវត្តិ
            ({{ number_format($books->sum('total_printed')) }} ក្បាលបានបោះពុម្ព)។
            បន្ទាប់មក Batch ថ្មីនឹងចាប់ផ្ដើមឡើងវិញពីដើម។
            <br><strong><i class="bi bi-info-circle me-1"></i>អ្នកអាចប្ដូរត្រឡប់ទៅ Batch ចាស់វិញបានគ្រប់ពេល</strong> តាមរយៈ «ប្រវត្តិ Batch»។
          </div>
          <div class="mb-3">
            <label class="form-label">ឈ្មោះ Batch ថ្មី</label>
            <input type="text" name="name" class="form-control"
                   placeholder="Batch {{ $allBatches->count() + 1 }}"
                   value="Batch {{ $allBatches->count() + 1 }}">
          </div>
          <div class="mb-3">
            <label class="form-label">របៀបចាប់ផ្ដើម</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reset_mode" id="rm1" value="keep_targets" checked>
              <label class="form-check-label" for="rm1" style="font-size:.85rem">
                <strong>រក្សាគោលដៅដដែល</strong> — បោះពុម្ពសៀវភៅដដែលម្ដងទៀត (printed reset to 0)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reset_mode" id="rm2" value="keep_targets_zero">
              <label class="form-check-label" for="rm2" style="font-size:.85rem">
                <strong>កំណត់គោលដៅឡើងវិញ</strong> — សៀវភៅដដែល តែគោលដៅ = 0 (set new targets after)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="reset_mode" id="rm3" value="fresh">
              <label class="form-check-label" for="rm3" style="font-size:.85rem">
                <strong>ចាប់ផ្ដើមទទេ (សៀវភៅថ្មី)</strong> — គ្មានសៀវភៅ បន្ថែមសៀវភៅថ្មីដោយខ្លួនឯង (start empty / new books)
              </label>
            </div>
          </div>
          <div class="mb-0">
            <label class="form-label">កំណត់ចំណាំ (Notes)</label>
            <input type="text" name="notes" class="form-control" placeholder="optional">
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-success btn-sm">
            <i class="bi bi-arrow-repeat"></i> ចាប់ផ្ដើម Batch ថ្មី
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Hidden batch form --}}
<form id="batchForm" method="POST" action="{{ route('printing.batch') }}">
  @csrf
  <input type="hidden" name="mode" id="batchModeInput">
  <div id="batchFormFields"></div>
</form>

@endsection

@push('scripts')
<script>
(function () {
  const booksData    = @json($books->keyBy('id'));
  const bookSelect   = document.getElementById('bookSelect');
  const printedInput = document.getElementById('printedInput');
  const remainingEl  = document.getElementById('remainingQty');
  const submitBtn    = document.getElementById('submitBtn');

  /* ── Book preview ────────────────── */
  function updatePreview() {
    const opt = bookSelect?.selectedOptions[0];
    if (!opt || !opt.value) return;

    const rem     = parseInt(opt.dataset.remaining, 10);
    const done    = opt.dataset.done === '1';
    const pct     = parseInt(opt.dataset.pct, 10);
    const printed = parseInt(opt.dataset.printed, 10);

    remainingEl.textContent = rem.toLocaleString();
    printedInput.max        = rem;

    document.getElementById('bookPreview').style.display = 'block';
    document.getElementById('previewTitle').textContent  = opt.dataset.title;
    document.getElementById('previewPct').textContent    = pct + '%';
    document.getElementById('previewTarget').textContent = parseInt(opt.dataset.target, 10).toLocaleString();

    const cb = document.getElementById('previewCatBadge');
    cb.textContent = opt.dataset.cat;
    cb.className   = 'badge ' + opt.dataset.catClass;

    const gb = document.getElementById('previewGradeBadge');
    gb.textContent = opt.dataset.gradeDisp || '—';

    const bar = document.getElementById('previewBar');
    bar.style.width = pct + '%';
    bar.className   = 'prog-fill ' + (done ? 'green' : pct >= 50 ? '' : 'amber');

    const sb = document.getElementById('previewStatusBadge');
    if (done)            { sb.textContent='រួចរាល់';       sb.className='badge badge-done'; }
    else if (printed > 0){ sb.textContent='កំពុងបោះពុម្ព'; sb.className='badge badge-progress'; }
    else                 { sb.textContent='មិនទាន់បោះ';    sb.className='badge badge-pending'; }

    printedInput.disabled = done;
    submitBtn.disabled    = done;
    if (!done) {
      const cur = parseInt(printedInput.value, 10);
      if (cur < 1 || cur > rem) printedInput.value = Math.min(1, rem);
    }
  }

  bookSelect?.addEventListener('change', updatePreview);
  updatePreview();

  /* ── Stepper ─────────────────────── */
  document.getElementById('decBtn')?.addEventListener('click', () => {
    const v = parseInt(printedInput.value, 10);
    if (v > 1) printedInput.value = v - 1;
  });
  document.getElementById('incBtn')?.addEventListener('click', () => {
    const v  = parseInt(printedInput.value, 10);
    const mx = parseInt(printedInput.max,   10);
    if (v < mx) printedInput.value = v + 1;
    else showToast('warning', 'ចំនួនមិនអាចលើស ' + mx.toLocaleString() + ' ក្បាល');
  });
  printedInput?.addEventListener('input', () => {
    const mx = parseInt(printedInput.max, 10);
    if (parseInt(printedInput.value, 10) > mx) {
      printedInput.value = mx;
      showToast('warning', 'ចំនួនមិនអាចលើស ' + mx.toLocaleString() + ' ក្បាល');
    }
  });

  /* ── Row click → select book ─────── */
  document.querySelectorAll('#booksTable tbody tr.row-select').forEach(row => {
    row.addEventListener('click', e => {
      if (e.target.closest('.edit-book-btn, form, .row-check')) return; // don't trigger on action buttons
      document.querySelectorAll('#booksTable tbody tr.row-select')
              .forEach(r => r.classList.remove('row-selected'));
      row.classList.add('row-selected');
      bookSelect.value = row.dataset.bookId;
      updatePreview();
      document.getElementById('dailyPrintPanel')
              .scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });

  /* ── Edit book modal ─────────────── */
  document.querySelectorAll('.edit-book-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      document.getElementById('editBookForm').action =
        '/books/' + btn.dataset.id;
      document.getElementById('editTitle').value    = btn.dataset.title;
      document.getElementById('editCategory').value = btn.dataset.category;
      document.getElementById('editGrade').value    = btn.dataset.grade;
      document.getElementById('editTarget').value   = btn.dataset.target;
      new bootstrap.Modal(document.getElementById('editBookModal')).show();
    });
  });

  /* ── CSV chevron ─────────────────── */
  const csvPanel = document.getElementById('csvPanel');
  const csvChev  = document.getElementById('csv-chevron');
  csvPanel?.addEventListener('show.bs.collapse', () => csvChev.style.transform = 'rotate(180deg)');
  csvPanel?.addEventListener('hide.bs.collapse', () => csvChev.style.transform = 'rotate(0deg)');

  /* ── Filters ─────────────────────── */
  const rows         = document.querySelectorAll('#booksTable tbody tr[data-book-id]');
  const gradeFilter  = document.getElementById('gradeFilter');
  const catFilter    = document.getElementById('categoryFilter');
  const statusFilter = document.getElementById('statusFilter');
  const searchInput  = document.getElementById('searchInput');

  function applyFilters() {
    const grade  = gradeFilter.value;
    const cat    = catFilter.value;
    const status = statusFilter.value;
    const q      = searchInput.value.toLowerCase().trim();
    rows.forEach(row => {
      const ok = (!grade  || row.dataset.grade    === grade)
              && (!cat    || row.dataset.category === cat)
              && (!status || row.dataset.status   === status)
              && (!q      || (row.dataset.title   || '').includes(q));
      row.style.display = ok ? '' : 'none';
    });
    Array.from(bookSelect.options).forEach(opt => {
      if (!opt.value) return;
      const bk = booksData[opt.value];
      const ok = (!grade || (bk && (bk.grade ?? '') === grade))
              && (!cat   || (bk && bk.category === cat));
      opt.style.display = ok ? '' : 'none';
    });
  }

  gradeFilter?.addEventListener('change',  applyFilters);
  catFilter?.addEventListener('change',    applyFilters);
  statusFilter?.addEventListener('change', applyFilters);
  searchInput?.addEventListener('input',   applyFilters);

  /* ── Select-all checkbox ─────────── */
  document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('#booksTable tbody .row-check').forEach(cb => {
      // only affect visible rows
      const row = cb.closest('tr');
      if (row && row.style.display !== 'none') cb.checked = this.checked;
    });
    updateBatchBar();
  });

  /* ── Individual row checkbox ─────── */
  document.querySelectorAll('#booksTable tbody').forEach(tbody => {
    tbody.addEventListener('change', e => {
      if (e.target.classList.contains('row-check')) updateBatchBar();
    });
  });
})();

/* ── Batch helpers ──────────────────────────────────────────────────────── */
let _batchMode = '';

function getSelectedBooks() {
  return Array.from(document.querySelectorAll('.row-check:checked')).map(cb => ({
    id:      cb.dataset.id,
    title:   cb.dataset.title,
    target:  parseInt(cb.dataset.target, 10),
    printed: parseInt(cb.dataset.printed, 10),
  }));
}

function updateBatchBar() {
  const n   = document.querySelectorAll('.row-check:checked').length;
  const bar = document.getElementById('batchBar');
  bar.style.display = n > 0 ? 'flex' : 'none';
  document.getElementById('batchSelCount').textContent = n + ' selected';
}

function clearBatchSelection() {
  document.querySelectorAll('.row-check, #selectAll').forEach(cb => cb.checked = false);
  updateBatchBar();
}

function openBatchModal(mode) {
  const books = getSelectedBooks();
  if (!books.length) { showToast('warning', 'Select at least one book first'); return; }

  _batchMode = mode;
  document.getElementById('batchModeInput').value = mode;

  const titleEl = document.getElementById('batchModalTitle');
  const descEl  = document.getElementById('batchModeDesc');
  const amtWrap = document.getElementById('batchAmountWrap');
  const amtLbl  = document.getElementById('batchAmountLabel');

  if (mode === 'set_done') {
    titleEl.innerHTML = '<i class="bi bi-check-circle-fill text-success me-2"></i>Mark All Selected as Done';
    descEl.className  = 'alert alert-success mb-3';
    descEl.innerHTML  = '<strong>Mark Done:</strong> Sets printed = target for all selected books. Logs the difference as today\'s print.';
    amtWrap.style.display = 'none';
  } else if (mode === 'add') {
    titleEl.innerHTML = '<i class="bi bi-plus-circle text-warning me-2"></i>Add Copies to Each Book';
    descEl.className  = 'alert alert-warning mb-3';
    descEl.innerHTML  = '<strong>Add Copies:</strong> Adds the same quantity to each selected book\'s printed count.';
    amtWrap.style.display = '';
    amtLbl.textContent = 'Copies to add (per book)';
    document.getElementById('batchAmount').value = 100;
  } else {
    titleEl.innerHTML = '<i class="bi bi-pencil text-info me-2"></i>Set Exact Printed Quantity';
    descEl.className  = 'alert alert-info mb-3';
    descEl.innerHTML  = '<strong>Set Printed:</strong> Sets total_printed to the number you enter for each book (capped at target).';
    amtWrap.style.display = '';
    amtLbl.textContent = 'Set total printed to';
    document.getElementById('batchAmount').value = 500;
  }

  // Preview list
  const listEl = document.getElementById('batchPreviewList');
  listEl.innerHTML = books.map(b => {
    const pct = b.target > 0 ? Math.round(b.printed / b.target * 100) : 0;
    const rem = b.target - b.printed;
    return `<div style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;border-bottom:1px solid #f1f5f9;">
      <div style="flex:1;min-width:0;">
        <div style="font-size:.82rem;font-weight:600;">${b.title}</div>
        <div style="font-size:.72rem;color:#64748b;">${b.printed.toLocaleString()} / ${b.target.toLocaleString()} (${pct}%)</div>
      </div>
      ${mode==='set_done'
        ? `<span style="background:#dcfce7;color:#15803d;border-radius:4px;padding:.1rem .4rem;font-size:.7rem;">+${rem.toLocaleString()} → Done</span>`
        : ''}
    </div>`;
  }).join('');

  new bootstrap.Modal(document.getElementById('batchModal')).show();
}

function submitBatch() {
  const books  = getSelectedBooks();
  const mode   = _batchMode;
  const amount = parseInt(document.getElementById('batchAmount').value, 10) || 0;
  const fields = document.getElementById('batchFormFields');

  fields.innerHTML = '';
  books.forEach((b, i) => {
    const makeInput = (name, val) => {
      const inp = document.createElement('input');
      inp.type = 'hidden'; inp.name = name; inp.value = val;
      fields.appendChild(inp);
    };
    makeInput(`updates[${i}][id]`, b.id);
    makeInput(`updates[${i}][amount]`, mode === 'set_done' ? b.target : amount);
  });

  const bm = bootstrap.Modal.getInstance(document.getElementById('batchModal'));
  if (bm) bm.hide();
  setTimeout(() => document.getElementById('batchForm').submit(), 350);
}
</script>
@endpush




