@extends('layouts.app')
@section('title',      'ប្រព័ន្ធគ្រប់គ្រងការបោះពុម្ព')
@section('page-title', 'ទំព័រគ្រប់គ្រង')

@section('content')

@php
  $totalBooks     = $books->count();
  $totalPrinted   = $books->sum('total_printed');
  $totalTarget    = $books->sum('target_qty');
  $totalRemaining = max($totalTarget - $totalPrinted, 0);
  $overallPct     = $totalTarget > 0 ? round($totalPrinted / $totalTarget * 100) : 0;
  $doneCount      = $books->filter(fn($b) => $b->total_printed >= $b->target_qty)->count();
  $inProgress     = $books->filter(fn($b) => $b->total_printed > 0 && $b->total_printed < $b->target_qty)->count();

  // Grade badge helper
  function gradeBadge(?string $grade): array {
    if (!$grade) return ['grade-badge', '—'];
    if (is_numeric($grade)) return ['grade-badge grade-num', 'ថ្នាក់ '.$grade];
    return ['grade-badge grade-primary', $grade];
  }
@endphp

{{-- ════════════  PAGE HEADER  ════════════ --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">ប្រព័ន្ធគ្រប់គ្រងការបោះពុម្ព</h1>
    <p class="section-sub">គ្រប់គ្រង និងតាមដានការបោះពុម្ពសៀវភៅជារៀងរាល់ថ្ងៃ</p>
  </div>
  <a href="{{ route('printing.report') }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-bar-chart-line"></i> មើលរបាយការណ៍
  </a>
</div>

{{-- ════════════  KPI CARDS  ════════════ --}}
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

{{-- Overall progress --}}
<div class="panel mb-4">
  <div class="panel-body" style="padding:1.1rem 1.5rem">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span style="font-weight:700;font-size:.9rem">ដំណើរការបោះពុម្ពរួម</span>
      <span style="font-family:var(--font-latin);font-size:.82rem;font-weight:700;color:var(--primary)">
        {{ number_format($totalPrinted) }} / {{ number_format($totalTarget) }} · {{ $overallPct }}%
      </span>
    </div>
    <div class="prog-track" style="height:12px">
      <div class="prog-fill {{ $overallPct >= 80 ? 'green' : ($overallPct >= 40 ? '' : 'amber') }}"
           style="width:{{ $overallPct }}%"></div>
    </div>
    <div class="d-flex justify-content-between mt-1" style="font-size:.68rem;color:var(--text-muted);font-family:var(--font-latin)">
      <span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span>
    </div>
  </div>
</div>

{{-- ════════════  TWO COLUMNS  ════════════ --}}
<div class="row g-4">

  {{-- ── LEFT: forms ── --}}
  <div class="col-xl-4 col-lg-5">

    {{-- CSV Import --}}
    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-file-earmark-spreadsheet"></i></div>
          <span>Import CSV</span>
        </div>
        <button class="btn btn-ghost btn-icon" type="button"
                data-bs-toggle="collapse" data-bs-target="#csvPanel" aria-label="toggle">
          <i class="bi bi-chevron-down" id="csv-chevron" style="transition:transform .2s"></i>
        </button>
      </div>
      <div class="collapse" id="csvPanel">
        <div class="panel-body">
          <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.8">
            Format (5 columns):<br>
            <code style="font-family:var(--font-latin);background:#f1f5f9;padding:.15em .45em;border-radius:4px;font-size:.72rem">
              title, category, target_qty, total_printed, grade
            </code><br>
            <span style="margin-top:.4rem;display:block">
              Category: <code style="font-family:var(--font-latin);background:#f1f5f9;padding:.1em .4em;border-radius:4px;font-size:.72rem">perfect_binding</code>
              ឬ <code style="font-family:var(--font-latin);background:#f1f5f9;padding:.1em .4em;border-radius:4px;font-size:.72rem">staple</code>
            </span>
            <span style="display:block">
              Grade: <code style="font-family:var(--font-latin);background:#f1f5f9;padding:.1em .4em;border-radius:4px;font-size:.72rem">1</code>–<code style="font-family:var(--font-latin);background:#f1f5f9;padding:.1em .4em;border-radius:4px;font-size:.72rem">12</code>
              ឬ <code style="font-family:var(--font-latin);background:#f1f5f9;padding:.1em .4em;border-radius:4px;font-size:.72rem">មត្តេយ្យ</code>
            </span>
          </p>
          <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label class="form-label">ជ្រើសរើសឯកសារ</label>
              <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
            </div>
            <button class="btn btn-warning w-100" type="submit">
              <i class="bi bi-upload"></i> បញ្ចូល CSV
            </button>
          </form>
        </div>
      </div>
    </div>

    {{-- Daily Print Form --}}
    <div class="panel" id="dailyPrintPanel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-printer-fill"></i></div>
          <span>បញ្ចូលចំនួនបោះពុម្ព</span>
        </div>
        <span style="font-family:var(--font-latin);font-size:.72rem;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:.25em .65em;border-radius:6px;font-weight:600">
          {{ now()->format('d/m/Y') }}
        </span>
      </div>
      <div class="panel-body">
        <form id="dailyPrintForm" action="{{ route('printing.store') }}" method="POST">
          @csrf

          {{-- Book preview --}}
          <div id="bookPreview" style="display:none;background:var(--surface-2);border-radius:var(--radius);border:1px solid var(--border);padding:.85rem 1rem;margin-bottom:1rem">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;margin-bottom:.5rem">
              <div style="min-width:0">
                <div id="previewTitle" style="font-weight:700;font-size:.9rem;line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"></div>
                <div style="display:flex;align-items:center;gap:.4rem;margin-top:.3rem;flex-wrap:wrap">
                  <span id="previewCatBadge" class="badge"></span>
                  <span id="previewGradeBadge" class="grade-badge" style="font-size:.72rem"></span>
                </div>
              </div>
              <span id="previewStatusBadge" class="badge" style="flex-shrink:0"></span>
            </div>
            <div class="prog-track" style="height:8px">
              <div class="prog-fill" id="previewBar" style="width:0%"></div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:.35rem;font-family:var(--font-latin);font-size:.7rem;color:var(--text-muted)">
              <span>0</span>
              <span style="font-weight:700;color:var(--primary)" id="previewPct">0%</span>
              <span id="previewTarget">0</span>
            </div>
          </div>

          {{-- Book select --}}
          <div class="mb-3">
            <label class="form-label" for="bookSelect">ឈ្មោះសៀវភៅ</label>
            <select id="bookSelect" name="book_id" class="form-select" required>
              @foreach($books as $book)
                @php
                  $catLbl  = $book->category === 'perfect_binding' ? 'បិតក្បាល'
                           : ($book->category === 'staple' ? 'កិបកណ្ដាល' : $book->category);
                  $rem     = max($book->target_qty - $book->total_printed, 0);
                  $pct     = $book->target_qty > 0 ? round($book->total_printed / $book->target_qty * 100) : 0;
                  $gradeDisp = $book->grade ? 'ថ្នាក់'.$book->grade : '';
                @endphp
                <option value="{{ $book->id }}"
                        data-remaining="{{ $rem }}"
                        data-target="{{ $book->target_qty }}"
                        data-printed="{{ $book->total_printed }}"
                        data-pct="{{ $pct }}"
                        data-cat="{{ $catLbl }}"
                        data-cat-class="{{ $book->category === 'perfect_binding' ? 'badge-binding' : 'badge-staple' }}"
                        data-grade="{{ $book->grade ?? '' }}"
                        data-grade-disp="{{ $gradeDisp }}"
                        data-done="{{ $book->total_printed >= $book->target_qty ? '1' : '0' }}"
                        data-title="{{ $book->title }}">
                  {{ $book->title }}{{ $gradeDisp ? ' · '.$gradeDisp : '' }} · {{ $catLbl }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Quantity stepper --}}
          <div class="mb-4">
            <label class="form-label" for="printedInput">
              ចំនួនបោះពុម្ពថ្ងៃនេះ
              <span style="float:right;font-weight:400;color:var(--text-muted)">
                នៅសល់: <strong id="remainingQty" style="color:var(--primary);font-family:var(--font-latin)">0</strong>
              </span>
            </label>
            <div style="display:flex;gap:.5rem;align-items:center">
              <button type="button" id="decBtn" class="btn btn-ghost btn-icon"
                      style="border:1.5px solid var(--border-dark);flex-shrink:0" aria-label="Decrease">
                <i class="bi bi-dash-lg"></i>
              </button>
              <input id="printedInput" type="number" name="printed_today"
                     class="form-control" value="1" min="1"
                     style="text-align:center;font-family:var(--font-latin);font-weight:700;font-size:1.1rem" required>
              <button type="button" id="incBtn" class="btn btn-ghost btn-icon"
                      style="border:1.5px solid var(--border-dark);flex-shrink:0" aria-label="Increase">
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

  {{-- ── RIGHT: table ── --}}
  <div class="col-xl-8 col-lg-7">
    <div class="panel" style="display:flex;flex-direction:column;height:100%">

      <div class="panel-header" style="flex-wrap:wrap;gap:.6rem">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-table"></i></div>
          <span>បញ្ជីសៀវភៅទាំងអស់</span>
          <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ $totalBooks }}</span>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-left:auto">
          <div style="position:relative">
            <i class="bi bi-search" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none"></i>
            <input id="searchInput" type="text" class="form-control form-control-sm"
                   placeholder="ស្វែងរក..."
                   style="padding-left:2rem;width:150px;border-radius:999px">
          </div>
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

      <div class="tbl-wrap" style="flex:1">
        <table class="data-table" id="booksTable">
          <thead>
            <tr>
              <th style="width:44px;text-align:center">
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
                <span class="th-en">Target</span>
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
                [$gradeClass, $gradeDisp] = gradeBadge($book->grade);

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
              <tr class="row-select"
                  data-book-id="{{ $book->id }}"
                  data-category="{{ $book->category }}"
                  data-status="{{ $sk }}"
                  data-grade="{{ $book->grade ?? '' }}"
                  data-title="{{ strtolower($book->title) }}">
                <td style="text-align:center;font-family:var(--font-latin);font-size:.78rem;color:var(--text-muted);font-weight:600">{{ $i + 1 }}</td>
                <td>
                  <div style="font-weight:700;font-size:.88rem;line-height:1.3">{{ $book->title }}</div>
                </td>
                <td style="text-align:center">
                  <span class="{{ $gradeClass }}">{{ $gradeDisp }}</span>
                </td>
                <td><span class="badge {{ $catClass }}">{{ $catLabel }}</span></td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:600;font-size:.88rem">{{ number_format($book->target_qty) }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-weight:600;font-size:.88rem;color:var(--success)">{{ number_format($book->total_printed) }}</td>
                <td style="text-align:right;font-family:var(--font-latin);font-size:.88rem;{{ $remStyle }}">{{ number_format($remaining) }}</td>
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
                    <p style="font-weight:700;margin:0">មិនទាន់មានសៀវភៅ</p>
                    <p class="text-sm text-muted" style="margin:0">Import CSV ដើម្បីបន្ថែមសៀវភៅ</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div style="padding:.6rem 1.5rem;border-top:1px solid var(--border);background:var(--surface-2)">
        <span style="font-size:.74rem;color:var(--text-muted)">
          <i class="bi bi-hand-index me-1"></i>ចុចលើជួរណាមួយ ដើម្បីជ្រើសសៀវភៅ
        </span>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
  const booksData    = @json($books->keyBy('id'));
  const bookSelect   = document.getElementById('bookSelect');
  const printedInput = document.getElementById('printedInput');
  const remainingEl  = document.getElementById('remainingQty');
  const submitBtn    = document.getElementById('submitBtn');

  function updatePreview() {
    const opt = bookSelect?.selectedOptions[0];
    if (!opt || !opt.value) return;

    const rem      = parseInt(opt.dataset.remaining, 10);
    const done     = opt.dataset.done === '1';
    const pct      = parseInt(opt.dataset.pct, 10);
    const printed  = parseInt(opt.dataset.printed, 10);

    remainingEl.textContent = rem.toLocaleString();
    printedInput.max        = rem;

    document.getElementById('bookPreview').style.display = 'block';
    document.getElementById('previewTitle').textContent  = opt.dataset.title;
    document.getElementById('previewPct').textContent    = pct + '%';
    document.getElementById('previewTarget').textContent = parseInt(opt.dataset.target, 10).toLocaleString();

    // category badge
    const cb = document.getElementById('previewCatBadge');
    cb.textContent  = opt.dataset.cat;
    cb.className    = 'badge ' + opt.dataset.catClass;

    // grade badge
    const gb = document.getElementById('previewGradeBadge');
    gb.textContent = opt.dataset.gradeDisp || '—';

    // progress bar
    const bar       = document.getElementById('previewBar');
    bar.style.width = pct + '%';
    bar.className   = 'prog-fill ' + (done ? 'green' : pct >= 50 ? '' : 'amber');

    // status badge
    const sb2 = document.getElementById('previewStatusBadge');
    if (done)           { sb2.textContent = 'រួចរាល់';         sb2.className = 'badge badge-done'; }
    else if (printed > 0) { sb2.textContent = 'កំពុងបោះពុម្ព'; sb2.className = 'badge badge-progress'; }
    else                { sb2.textContent = 'មិនទាន់បោះពុម្ព'; sb2.className = 'badge badge-pending'; }

    printedInput.disabled = done;
    submitBtn.disabled    = done;
    if (!done) {
      const cur = parseInt(printedInput.value, 10);
      if (cur < 1 || cur > rem) printedInput.value = Math.min(1, rem);
    }
  }

  bookSelect?.addEventListener('change', updatePreview);
  updatePreview();

  // Stepper
  document.getElementById('decBtn')?.addEventListener('click', () => {
    const v = parseInt(printedInput.value, 10);
    if (v > 1) printedInput.value = v - 1;
  });
  document.getElementById('incBtn')?.addEventListener('click', () => {
    const v = parseInt(printedInput.value, 10), mx = parseInt(printedInput.max, 10);
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

  // Row click
  document.querySelectorAll('#booksTable tbody tr.row-select').forEach(row => {
    row.addEventListener('click', () => {
      document.querySelectorAll('#booksTable tbody tr.row-select').forEach(r => r.classList.remove('row-selected'));
      row.classList.add('row-selected');
      bookSelect.value = row.dataset.bookId;
      updatePreview();
      document.getElementById('dailyPrintPanel').scrollIntoView({ behavior:'smooth', block:'nearest' });
    });
  });

  // CSV chevron
  const csvPanel = document.getElementById('csvPanel');
  const csvChev  = document.getElementById('csv-chevron');
  csvPanel?.addEventListener('show.bs.collapse', () => csvChev.style.transform = 'rotate(180deg)');
  csvPanel?.addEventListener('hide.bs.collapse', () => csvChev.style.transform = 'rotate(0deg)');

  // Filters
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

  gradeFilter?.addEventListener('change', applyFilters);
  catFilter?.addEventListener('change', applyFilters);
  statusFilter?.addEventListener('change', applyFilters);
  searchInput?.addEventListener('input', applyFilters);
})();
</script>
@endpush
