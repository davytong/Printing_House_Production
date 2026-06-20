@extends('layouts.app')
@section('title','ស្នើរសុំបោះពុម្ពថ្មី')
@section('page-title','New Print Request')

@section('content')

{{-- Page header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="section-title">ស្នើរសុំបោះពុម្ពថ្មី</h1>
    <p class="section-sub">ស្នើរសុំបោះពុម្ពសៀវភៅច្រើនចំណងជើងក្នុងពេលតែមួយ</p>
  </div>
  <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> ត្រឡប់
  </a>
</div>

<form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data" id="reqForm">
@csrf

<div class="row g-4">

  {{-- ── LEFT: Header info ── --}}
  <div class="col-lg-4">

    <div class="panel mb-4">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#eff6ff;color:#1d4ed8">
            <i class="bi bi-person-fill"></i>
          </div>
          <span>ព័ត៌មានស្នើរសុំ</span>
        </div>
      </div>
      <div class="panel-body d-flex flex-column gap-3">

        <div>
          <label class="form-label">ចំណងជើង / Title *</label>
          <input type="text" name="title" class="form-control"
                 value="{{ old('title') }}"
                 placeholder="ឧ. ស្នើរសុំបោះពុម្ព ឆ្នាំ 2026" required>
        </div>

        <div>
          <label class="form-label">ឈ្មោះអ្នកស្នើ *</label>
          <input type="text" name="requester_name" class="form-control"
                 value="{{ old('requester_name') }}" required>
        </div>

        <div>
          <label class="form-label">នាយកដ្ឋាន</label>
          <input type="text" name="department" class="form-control"
                 value="{{ old('department') }}">
        </div>

        <div>
          <label class="form-label">អាទិភាព *</label>
          <select name="priority" class="form-select" required>
            <option value="normal"  {{ old('priority','normal')=='normal' ?'selected':'' }}>🟡 ធម្មតា</option>
            <option value="low"     {{ old('priority')=='low'    ?'selected':'' }}>⚪ ទាប</option>
            <option value="high"    {{ old('priority')=='high'   ?'selected':'' }}>🟠 ខ្ពស់</option>
            <option value="urgent"  {{ old('priority')=='urgent' ?'selected':'' }}>🔴 បន្ទាន់</option>
          </select>
        </div>

        <div>
          <label class="form-label">ត្រូវការមុន</label>
          <input type="date" name="required_by" class="form-control"
                 value="{{ old('required_by') }}"
                 style="font-family:var(--font-latin)">
        </div>

        <div>
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" rows="3"
                    placeholder="ព័ត៌មានបន្ថែម...">{{ old('notes') }}</textarea>
        </div>

      </div>
    </div>

    {{-- Attachments --}}
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#fef3c7;color:#d97706">
            <i class="bi bi-paperclip"></i>
          </div>
          <span>ឯកសារភ្ជាប់</span>
        </div>
      </div>
      <div class="panel-body">

        {{-- Drag-and-drop zone --}}
        <div id="dropZone"
             style="border:2px dashed var(--border-dark);border-radius:var(--radius);
                    padding:1.5rem;text-align:center;cursor:pointer;transition:all var(--ease);
                    background:var(--surface-2)">
          <i class="bi bi-cloud-arrow-up"
             style="font-size:1.8rem;color:var(--text-muted);display:block;margin-bottom:.5rem"></i>
          <p style="font-size:.83rem;color:var(--text-muted);margin:0">
            Drag &amp; drop ឯកសារ ឬ
            <label for="fileInput"
                   style="color:var(--primary);font-weight:600;cursor:pointer;text-decoration:underline">
              ចុចជ្រើស
            </label>
          </p>
          <p style="font-size:.72rem;color:var(--text-muted);margin:.35rem 0 0">
            JPG, PNG, PDF, DOC, DOCX, XLS, XLSX — max 10 MB each · max 10 files
          </p>
          <input type="file" id="fileInput" name="attachments[]"
                 accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                 multiple style="display:none">
        </div>

        {{-- Preview list --}}
        <div id="filePreviewList" class="d-flex flex-column gap-2 mt-3"></div>

      </div>
    </div>

  </div>{{-- /left --}}

  {{-- ── RIGHT: Book rows table ── --}}
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d">
            <i class="bi bi-journals"></i>
          </div>
          <span>បញ្ជីសៀវភៅ</span>
          <span id="rowCount"
                style="font-family:var(--font-latin);font-size:.72rem;background:#dbeafe;
                       color:#1e40af;padding:.1em .55em;border-radius:999px;font-weight:700">
            0 ចំណងជើង
          </span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          {{-- Paste hint --}}
          <span style="font-size:.72rem;color:var(--text-muted);align-self:center">
            <i class="bi bi-clipboard me-1"></i>Paste from Excel
          </span>
          <button type="button" id="addRowBtn" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-lg"></i> បន្ថែម
          </button>
          <button type="button" id="clearAllBtn"
                  class="btn btn-ghost btn-sm" style="color:var(--danger)">
            <i class="bi bi-trash3"></i> Clear
          </button>
        </div>
      </div>

      {{-- Quick paste hint --}}
      <div style="padding:.6rem 1.25rem;background:#fffbeb;border-bottom:1px solid #fde68a;
                  font-size:.78rem;color:#92400e;display:flex;align-items:center;gap:.5rem">
        <i class="bi bi-lightbulb-fill"></i>
        <span>
          <strong>Tip:</strong> Copy rows from Excel / Google Sheets ហើយ
          <kbd style="background:#fde68a;border-radius:3px;padding:.05em .35em;font-family:var(--font-latin)">Ctrl+V</kbd>
          ក្នុង cell ណាមួយ — ជួរដេកនឹង fill ដោយស្វ័យប្រវត្តិ
          (column: ចំណងជើង | ថ្នាក់ | ប្រភេទ | ចំនួន)
        </span>
      </div>

      {{-- Table --}}
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse" id="booksTable">
          <thead>
            <tr style="background:var(--surface-2);border-bottom:2px solid var(--border)">
              <th style="padding:.6rem .75rem;font-family:var(--font-latin);font-size:.68rem;
                         font-weight:600;color:var(--text-muted);text-transform:uppercase;
                         letter-spacing:.06em;width:32px;text-align:center">#</th>
              <th style="padding:.6rem .75rem;font-family:var(--font-khmer);font-size:.82rem;
                         font-weight:700;color:var(--text-primary);min-width:220px">
                ឈ្មោះសៀវភៅ <span style="color:var(--danger)">*</span>
              </th>
              <th style="padding:.6rem .75rem;font-family:var(--font-khmer);font-size:.82rem;
                         font-weight:700;color:var(--text-primary);width:100px">ថ្នាក់</th>
              <th style="padding:.6rem .75rem;font-family:var(--font-khmer);font-size:.82rem;
                         font-weight:700;color:var(--text-primary);width:140px">ប្រភេទ</th>
              <th style="padding:.6rem .75rem;font-family:var(--font-khmer);font-size:.82rem;
                         font-weight:700;color:var(--text-primary);width:110px;text-align:right">
                ចំនួន <span style="color:var(--danger)">*</span>
              </th>
              <th style="padding:.6rem .75rem;font-family:var(--font-khmer);font-size:.82rem;
                         font-weight:700;color:var(--text-primary)">Notes</th>
              <th style="padding:.6rem .75rem;width:44px"></th>
            </tr>
          </thead>
          <tbody id="booksBody">
            {{-- Rows injected by JS --}}
          </tbody>
          <tfoot>
            <tr style="background:var(--surface-2);border-top:2px solid var(--border)">
              <td colspan="4"
                  style="padding:.65rem 1rem;font-size:.82rem;font-weight:700;color:var(--text-secondary)">
                សរុប
              </td>
              <td style="padding:.65rem 1rem;text-align:right;font-family:var(--font-latin);
                         font-weight:800;font-size:.95rem;color:var(--primary)"
                  id="totalQty">0</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- Empty state --}}
      <div id="emptyState"
           style="padding:2.5rem;text-align:center;color:var(--text-muted)">
        <i class="bi bi-journal-plus"
           style="font-size:2rem;display:block;margin-bottom:.5rem;color:var(--border-dark)"></i>
        <p style="margin:0;font-size:.85rem">
          ចុច <strong>+ បន្ថែម</strong> ឬ Paste ពី Excel
        </p>
      </div>

      <div class="panel-body" style="padding-top:.75rem;border-top:1px solid var(--border)">
        <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap">
          <div style="font-size:.78rem;color:var(--text-muted)">
            <i class="bi bi-info-circle me-1"></i>
            ការស្នើរសុំត្រូវឆ្លងការអនុម័តពី Manager
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('requests.index') }}"
               class="btn btn-outline-secondary">បោះបង់</a>
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
              <i class="bi bi-send-fill"></i> ដាក់ស្នើ
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>
</form>

@endsection

@push('scripts')
<script>
// ── Book data for autocomplete ───────────────────────────
@php
  $booksJson = $books->map(fn($b) => [
      'id'       => $b->id,
      'title'    => $b->title,
      'grade'    => $b->grade ?? '',
      'category' => $b->category,
      'catLabel' => $b->category === 'perfect_binding' ? 'បិតក្បាល' : 'កិបកណ្ដាល',
  ])->values();
@endphp
const BOOKS = {!! json_encode($booksJson) !!};

let rowIdx = 0;

// ── Row template ─────────────────────────────────────────
function makeRow(data = {}) {
  const i   = rowIdx++;
  const tr  = document.createElement('tr');
  tr.className = 'book-row';
  tr.style.cssText = 'border-bottom:1px solid var(--surface-2);transition:background var(--ease)';
  tr.dataset.idx = i;

  tr.innerHTML = `
    <td style="padding:.45rem .6rem;text-align:center;font-family:var(--font-latin);
               font-size:.75rem;color:var(--text-muted);font-weight:600" class="row-num"></td>

    <td style="padding:.35rem .5rem;position:relative">
      <input type="text"
             name="books[${i}][book_title]"
             class="form-control form-control-sm book-title-input"
             placeholder="ឈ្មោះសៀវភៅ..."
             value="${escHtml(data.title||'')}"
             autocomplete="off" required
             style="font-size:.85rem">
      <input type="hidden" name="books[${i}][book_id]" class="book-id-input"
             value="${data.id||''}">
      <div class="ac-dropdown"
           style="display:none;position:absolute;left:0;right:0;top:100%;z-index:200;
                  background:#fff;border:1px solid var(--border);border-radius:var(--radius);
                  box-shadow:0 8px 24px rgba(0,0,0,.12);max-height:220px;overflow-y:auto"></div>
    </td>

    <td style="padding:.35rem .5rem">
      <input type="text"
             name="books[${i}][grade]"
             class="form-control form-control-sm"
             placeholder="1–12"
             value="${escHtml(data.grade||'')}"
             style="font-size:.85rem;font-family:var(--font-latin)">
    </td>

    <td style="padding:.35rem .5rem">
      <select name="books[${i}][category]" class="form-select form-select-sm"
              style="font-size:.82rem">
        <option value="" ${!data.category?'selected':''}>—</option>
        <option value="perfect_binding" ${data.category==='perfect_binding'?'selected':''}>បិតក្បាល</option>
        <option value="staple"          ${data.category==='staple'?'selected':''}>កិបកណ្ដាល</option>
      </select>
    </td>

    <td style="padding:.35rem .5rem">
      <input type="number"
             name="books[${i}][quantity_requested]"
             class="form-control form-control-sm qty-input"
             min="1" value="${data.qty||1}" required
             style="text-align:right;font-family:var(--font-latin);font-weight:700;font-size:.88rem">
    </td>

    <td style="padding:.35rem .5rem">
      <input type="text"
             name="books[${i}][notes]"
             class="form-control form-control-sm"
             placeholder="..."
             value="${escHtml(data.notes||'')}"
             style="font-size:.82rem">
    </td>

    <td style="padding:.35rem .5rem;text-align:center">
      <button type="button" class="btn btn-ghost btn-sm remove-row"
              style="color:var(--danger);padding:.2rem .4rem" title="លុប">
        <i class="bi bi-x-lg"></i>
      </button>
    </td>
  `;

  // Remove row
  tr.querySelector('.remove-row').addEventListener('click', () => {
    tr.remove();
    reIndex();
    updateTotals();
  });

  // Qty change
  tr.querySelector('.qty-input').addEventListener('input', updateTotals);

  // Autocomplete
  setupAutocomplete(tr);

  return tr;
}

// ── Autocomplete ─────────────────────────────────────────
function setupAutocomplete(row) {
  const input   = row.querySelector('.book-title-input');
  const hidden  = row.querySelector('.book-id-input');
  const gradeIn = row.querySelectorAll('input')[1]; // grade field
  const catSel  = row.querySelector('select');
  const drop    = row.querySelector('.ac-dropdown');

  function showDrop(matches) {
    drop.innerHTML = '';
    if (!matches.length) { drop.style.display='none'; return; }
    matches.slice(0,10).forEach(b => {
      const div = document.createElement('div');
      div.style.cssText = 'padding:.5rem .85rem;cursor:pointer;font-size:.83rem;' +
                          'border-bottom:1px solid var(--border)';
      div.innerHTML = `<span style="font-weight:600">${escHtml(b.title)}</span>` +
        (b.grade ? `<span style="margin-left:.4rem;font-family:var(--font-latin);font-size:.72rem;
                    background:#f0fdf4;color:#15803d;padding:.05em .45em;border-radius:4px">
                    ថ្នាក់ ${escHtml(b.grade)}</span>` : '') +
        `<span style="float:right;font-size:.72rem;color:var(--text-muted)">${escHtml(b.catLabel)}</span>`;
      div.addEventListener('mousedown', e => {
        e.preventDefault();
        input.value   = b.title;
        hidden.value  = b.id;
        gradeIn.value = b.grade;
        catSel.value  = b.category;
        drop.style.display = 'none';
      });
      div.addEventListener('mouseover', () => div.style.background='var(--surface-2)');
      div.addEventListener('mouseout',  () => div.style.background='');
      drop.appendChild(div);
    });
    drop.style.display = 'block';
  }

  input.addEventListener('input', () => {
    const q = input.value.toLowerCase().trim();
    hidden.value = '';
    if (!q) { drop.style.display='none'; return; }
    showDrop(BOOKS.filter(b =>
      b.title.toLowerCase().includes(q) ||
      b.grade.toLowerCase().includes(q)
    ));
  });

  input.addEventListener('blur', () => setTimeout(() => drop.style.display='none', 150));
  input.addEventListener('focus', () => {
    const q = input.value.toLowerCase().trim();
    if (q) {
      showDrop(BOOKS.filter(b => b.title.toLowerCase().includes(q)));
    }
  });

  // Arrow key nav
  input.addEventListener('keydown', e => {
    const items = [...drop.querySelectorAll('div')];
    const active = drop.querySelector('.ac-active');
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      const next = active ? active.nextSibling : items[0];
      if (active) active.classList.remove('ac-active'), active.style.background='';
      if (next) { next.classList.add('ac-active'); next.style.background='var(--surface-2)'; }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      const prev = active ? active.previousSibling : items[items.length-1];
      if (active) active.classList.remove('ac-active'), active.style.background='';
      if (prev) { prev.classList.add('ac-active'); prev.style.background='var(--surface-2)'; }
    } else if (e.key === 'Enter' && active) {
      e.preventDefault();
      active.dispatchEvent(new Event('mousedown'));
    } else if (e.key === 'Escape') {
      drop.style.display = 'none';
    }
  });
}

// ── Helpers ──────────────────────────────────────────────
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function reIndex() {
  document.querySelectorAll('#booksBody .book-row').forEach((row, i) => {
    const num = row.querySelector('.row-num');
    if (num) num.textContent = i + 1;
  });
  // update row count badge
  const cnt = document.querySelectorAll('#booksBody .book-row').length;
  document.getElementById('rowCount').textContent = cnt + ' ចំណងជើង';
  document.getElementById('emptyState').style.display = cnt === 0 ? 'block' : 'none';
}

function updateTotals() {
  let total = 0;
  document.querySelectorAll('.qty-input').forEach(inp => {
    total += parseInt(inp.value, 10) || 0;
  });
  document.getElementById('totalQty').textContent = total.toLocaleString();
}

function addRow(data = {}) {
  const body = document.getElementById('booksBody');
  const row  = makeRow(data);
  body.appendChild(row);
  reIndex();
  updateTotals();
  row.querySelector('.book-title-input').focus();
}

// ── Add / Clear ──────────────────────────────────────────
document.getElementById('addRowBtn').addEventListener('click', () => addRow());
document.getElementById('clearAllBtn').addEventListener('click', () => {
  if (confirm('ចង់លុបជួរទាំងអស់?')) {
    document.getElementById('booksBody').innerHTML = '';
    reIndex();
    updateTotals();
  }
});

// ── Paste from Excel ─────────────────────────────────────
// Expected columns: Title | Grade | Category | Quantity (| Notes optional)
document.addEventListener('paste', e => {
  const active = document.activeElement;
  const inTable = active && active.closest('#booksTable');
  if (!inTable) return;
  e.preventDefault();

  const text = (e.clipboardData || window.clipboardData).getData('text');
  if (!text.trim()) return;

  const lines = text.trim().split(/\r?\n/);
  let added = 0;
  lines.forEach(line => {
    const cols = line.split('\t');
    if (!cols[0]?.trim()) return;

    const title = cols[0]?.trim() ?? '';
    const grade = cols[1]?.trim() ?? '';
    let   cat   = (cols[2]?.trim() ?? '').toLowerCase();
    const qty   = parseInt(cols[3]?.trim() ?? '1', 10) || 1;
    const notes = cols[4]?.trim() ?? '';

    // Normalise category
    if (cat.includes('perfect') || cat.includes('bind') || cat.includes('បិត')) {
      cat = 'perfect_binding';
    } else if (cat.includes('staple') || cat.includes('kib') || cat.includes('កិប')) {
      cat = 'staple';
    } else {
      cat = '';
    }

    // Match existing book
    const match = BOOKS.find(b => b.title.toLowerCase() === title.toLowerCase());

    addRow({
      id:       match ? match.id : '',
      title:    match ? match.title : title,
      grade:    grade || (match ? match.grade : ''),
      category: cat   || (match ? match.category : ''),
      qty,
      notes,
    });
    added++;
  });

  if (added > 0) {
    showToast('success', `Paste ជោគជ័យ — បានបន្ថែម ${added} ជួរ`);
  }
});

// ── File drag-and-drop ────────────────────────────────────
const dropZone   = document.getElementById('dropZone');
const fileInput  = document.getElementById('fileInput');
const previewList= document.getElementById('filePreviewList');

let selectedFiles = new DataTransfer();

function addFiles(files) {
  const allowed = ['image/jpeg','image/png','image/gif','image/webp',
                   'application/pdf',
                   'application/msword',
                   'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                   'application/vnd.ms-excel',
                   'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                   'text/plain'];
  const maxSize = 10 * 1024 * 1024;
  let added = 0;

  for (const f of files) {
    if (selectedFiles.files.length >= 10) {
      showToast('warning', 'អាចភ្ជាប់ max 10 ឯកសារ'); break;
    }
    if (!allowed.includes(f.type)) {
      showToast('warning', `ប្រភេទឯកសារ "${f.name}" មិនត្រូវបានអនុញ្ញាត`); continue;
    }
    if (f.size > maxSize) {
      showToast('warning', `"${f.name}" ធំលើស 10 MB`); continue;
    }
    selectedFiles.items.add(f);
    added++;
  }
  if (added) renderFilePreviews();
}

function renderFilePreviews() {
  fileInput.files = selectedFiles.files;
  previewList.innerHTML = '';
  [...selectedFiles.files].forEach((f, i) => {
    const isImg = f.type.startsWith('image/');
    const icon  = f.type.includes('pdf') ? 'bi-file-earmark-pdf' :
                  f.type.includes('word') ? 'bi-file-earmark-word' :
                  f.type.includes('excel') || f.type.includes('sheet') ? 'bi-file-earmark-excel' :
                  isImg ? 'bi-file-earmark-image' : 'bi-file-earmark';

    const div = document.createElement('div');
    div.style.cssText = 'display:flex;align-items:center;gap:.65rem;padding:.55rem .75rem;' +
                        'background:var(--surface-2);border-radius:var(--radius-sm);' +
                        'border:1px solid var(--border)';

    div.innerHTML = `
      ${isImg
        ? `<img src="${URL.createObjectURL(f)}"
               style="width:36px;height:36px;object-fit:cover;border-radius:4px;flex-shrink:0">`
        : `<div style="width:36px;height:36px;border-radius:4px;background:#dbeafe;
                       display:flex;align-items:center;justify-content:center;flex-shrink:0">
             <i class="bi ${icon}" style="font-size:1.1rem;color:#1d4ed8"></i>
           </div>`}
      <div style="flex:1;min-width:0">
        <div style="font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;
                    text-overflow:ellipsis">${escHtml(f.name)}</div>
        <div style="font-size:.7rem;color:var(--text-muted);font-family:var(--font-latin)">
          ${(f.size/1024).toFixed(1)} KB
        </div>
      </div>
      <button type="button" class="btn btn-ghost btn-sm remove-file"
              data-idx="${i}" style="color:var(--danger);padding:.15rem .35rem;flex-shrink:0">
        <i class="bi bi-x-lg"></i>
      </button>
    `;

    div.querySelector('.remove-file').addEventListener('click', () => {
      const dt = new DataTransfer();
      [...selectedFiles.files].forEach((ff, ii) => { if (ii !== i) dt.items.add(ff); });
      selectedFiles = dt;
      renderFilePreviews();
    });

    previewList.appendChild(div);
  });
}

dropZone.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', () => addFiles(fileInput.files));

dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.style.borderColor='var(--primary)'; dropZone.style.background='#eff6ff'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor=''; dropZone.style.background='var(--surface-2)'; });
dropZone.addEventListener('drop',      e => {
  e.preventDefault();
  dropZone.style.borderColor=''; dropZone.style.background='var(--surface-2)';
  addFiles(e.dataTransfer.files);
});

// ── Form validation ──────────────────────────────────────
document.getElementById('reqForm').addEventListener('submit', e => {
  const rows = document.querySelectorAll('#booksBody .book-row');
  if (rows.length === 0) {
    e.preventDefault();
    showToast('warning', 'សូមបន្ថែមសៀវភៅយ៉ាងហោចណាស់ 1 ចំណងជើង');
    return;
  }
  let valid = true;
  rows.forEach(row => {
    const t = row.querySelector('.book-title-input');
    const q = row.querySelector('.qty-input');
    if (!t.value.trim()) { t.style.borderColor='var(--danger)'; valid=false; }
    else                   t.style.borderColor='';
    if (!parseInt(q.value,10) || parseInt(q.value,10)<1) { q.style.borderColor='var(--danger)'; valid=false; }
    else q.style.borderColor='';
  });
  if (!valid) {
    e.preventDefault();
    showToast('error', 'សូមបំពេញចំណងជើង និងចំនួន');
  }
});

// ── Start with 1 row ─────────────────────────────────────
addRow();
</script>

<style>
.ac-dropdown div:last-child { border-bottom: none !important; }
</style>
@endpush
