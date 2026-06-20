@extends('layouts.app')
@section('title','កែប្រែ — '.$request->request_code)
@section('page-title','Edit Print Request')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="section-title">កែប្រែ: {{ $request->request_code }}</h1>
    <p class="section-sub">{{ $request->title }}</p>
  </div>
  <a href="{{ route('requests.show',$request) }}"
     class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> ត្រឡប់
  </a>
</div>

<form action="{{ route('requests.update',$request) }}"
      method="POST" enctype="multipart/form-data" id="reqForm">
@csrf @method('PUT')

<div class="row g-4">

  {{-- LEFT --}}
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
          <label class="form-label">ចំណងជើង *</label>
          <input type="text" name="title" class="form-control"
                 value="{{ old('title',$request->title) }}" required>
        </div>
        <div>
          <label class="form-label">ឈ្មោះអ្នកស្នើ *</label>
          <input type="text" name="requester_name" class="form-control"
                 value="{{ old('requester_name',$request->requester_name) }}" required>
        </div>
        <div>
          <label class="form-label">នាយកដ្ឋាន</label>
          <input type="text" name="department" class="form-control"
                 value="{{ old('department',$request->department) }}">
        </div>
        <div>
          <label class="form-label">អាទិភាព *</label>
          <select name="priority" class="form-select" required>
            @foreach(['normal'=>'🟡 ធម្មតា','low'=>'⚪ ទាប','high'=>'🟠 ខ្ពស់','urgent'=>'🔴 បន្ទាន់'] as $v=>$l)
              <option value="{{ $v }}" {{ old('priority',$request->priority)==$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label">ត្រូវការមុន</label>
          <input type="date" name="required_by" class="form-control"
                 value="{{ old('required_by',$request->required_by?->format('Y-m-d')) }}"
                 style="font-family:var(--font-latin)">
        </div>
        <div>
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" rows="3">{{ old('notes',$request->notes) }}</textarea>
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

        {{-- Existing --}}
        @if(!empty($request->attachments))
          <div class="d-flex flex-column gap-2 mb-3">
            @foreach($request->attachments as $i => $att)
              @php
                $isImg = str_starts_with($att['mime']??'','image/');
                $icon  = $isImg?'bi-file-earmark-image':(str_contains($att['mime']??'','pdf')?'bi-file-earmark-pdf':'bi-file-earmark');
              @endphp
              <div style="display:flex;align-items:center;gap:.6rem;padding:.5rem .75rem;
                          background:var(--surface-2);border:1px solid var(--border);
                          border-radius:var(--radius-sm)">
                <i class="bi {{ $icon }}" style="font-size:1rem;color:var(--primary);flex-shrink:0"></i>
                <div style="flex:1;min-width:0">
                  <div style="font-size:.78rem;font-weight:600;white-space:nowrap;
                              overflow:hidden;text-overflow:ellipsis">{{ $att['original_name'] }}</div>
                  <div style="font-size:.68rem;color:var(--text-muted);font-family:var(--font-latin)">
                    {{ round(($att['size']??0)/1024,1) }} KB
                  </div>
                </div>
                <form action="{{ route('requests.remove-attachment',$request) }}" method="POST"
                      onsubmit="return confirm('លុបឯកសារ?')">
                  @csrf @method('DELETE')
                  <input type="hidden" name="index" value="{{ $i }}">
                  <button class="btn btn-ghost btn-sm" style="color:var(--danger);padding:.15rem .35rem">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </form>
              </div>
            @endforeach
          </div>
        @endif

        {{-- Add more --}}
        <div id="dropZone"
             style="border:2px dashed var(--border-dark);border-radius:var(--radius);
                    padding:1.25rem;text-align:center;cursor:pointer;
                    background:var(--surface-2);transition:all var(--ease)">
          <i class="bi bi-plus-circle"
             style="font-size:1.4rem;color:var(--text-muted);display:block;margin-bottom:.3rem"></i>
          <p style="font-size:.8rem;color:var(--text-muted);margin:0">
            <label for="fileInput" style="color:var(--primary);cursor:pointer;text-decoration:underline">
              ជ្រើសឯកសារ
            </label> ឬ Drag &amp; Drop
          </p>
          <input type="file" id="fileInput" name="attachments[]"
                 accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                 multiple style="display:none">
        </div>
        <div id="filePreviewList" class="d-flex flex-column gap-2 mt-2"></div>
      </div>
    </div>
  </div>

  {{-- RIGHT: books table --}}
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-journals"></i></div>
          <span>បញ្ជីសៀវភៅ</span>
          <span id="rowCount"
                style="font-family:var(--font-latin);font-size:.72rem;background:#dbeafe;
                       color:#1e40af;padding:.1em .55em;border-radius:999px;font-weight:700">
            0 ចំណងជើង
          </span>
        </div>
        <div class="d-flex gap-2">
          <span style="font-size:.72rem;color:var(--text-muted);align-self:center">
            <i class="bi bi-clipboard me-1"></i>Paste from Excel
          </span>
          <button type="button" id="addRowBtn" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-lg"></i> បន្ថែម
          </button>
        </div>
      </div>

      <div style="padding:.6rem 1.25rem;background:#fffbeb;border-bottom:1px solid #fde68a;
                  font-size:.78rem;color:#92400e;display:flex;align-items:center;gap:.5rem">
        <i class="bi bi-lightbulb-fill"></i>
        <span>Tip: Copy ពី Excel → <kbd style="background:#fde68a;border-radius:3px;padding:.05em .35em;font-family:var(--font-latin)">Ctrl+V</kbd></span>
      </div>

      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse" id="booksTable">
          <thead>
            <tr style="background:var(--surface-2);border-bottom:2px solid var(--border)">
              <th style="padding:.6rem .75rem;width:32px;text-align:center;font-size:.72rem;color:var(--text-muted)">#</th>
              <th style="padding:.6rem .75rem;font-size:.82rem;font-weight:700;min-width:200px">ឈ្មោះសៀវភៅ *</th>
              <th style="padding:.6rem .75rem;font-size:.82rem;font-weight:700;width:90px">ថ្នាក់</th>
              <th style="padding:.6rem .75rem;font-size:.82rem;font-weight:700;width:130px">ប្រភេទ</th>
              <th style="padding:.6rem .75rem;font-size:.82rem;font-weight:700;width:100px;text-align:right">ចំនួន *</th>
              <th style="padding:.6rem .75rem;font-size:.82rem;font-weight:700">Notes</th>
              <th style="padding:.6rem .75rem;width:40px"></th>
            </tr>
          </thead>
          <tbody id="booksBody"></tbody>
          <tfoot>
            <tr style="background:var(--surface-2);border-top:2px solid var(--border)">
              <td colspan="4" style="padding:.65rem 1rem;font-weight:700;font-size:.85rem;color:var(--text-secondary)">
                សរុប
              </td>
              <td style="padding:.65rem 1rem;text-align:right;font-family:var(--font-latin);
                         font-weight:800;font-size:.95rem;color:var(--primary)" id="totalQty">0</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="panel-body" style="padding-top:.75rem;border-top:1px solid var(--border)">
        <div class="d-flex gap-2 justify-content-end">
          <a href="{{ route('requests.show',$request) }}" class="btn btn-outline-secondary">
            បោះបង់
          </a>
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-lg"></i> រក្សាទុក
          </button>
        </div>
      </div>
    </div>
  </div>

</div>
</form>

{{-- Existing items as JSON for pre-fill --}}
@php
  $existingItemsJson = $request->items->map(fn($it) => [
      'id'       => $it->book_id,
      'title'    => $it->book_title,
      'grade'    => $it->grade ?? '',
      'category' => $it->category ?? '',
      'qty'      => $it->quantity_requested,
      'notes'    => $it->notes ?? '',
  ])->values();
  $editBooksJson = $books->map(fn($b) => [
      'id'       => $b->id,
      'title'    => $b->title,
      'grade'    => $b->grade ?? '',
      'category' => $b->category,
      'catLabel' => $b->category === 'perfect_binding' ? 'បិតក្បាល' : 'កិបកណ្ដាល',
  ])->values();
@endphp
<script>
const EXISTING_ITEMS = {!! json_encode($existingItemsJson) !!};
</script>

@endsection

@push('scripts')
<script>
const BOOKS = {!! json_encode($editBooksJson) !!};

let rowIdx = 0;

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function makeRow(data = {}) {
  const i  = rowIdx++;
  const tr = document.createElement('tr');
  tr.className = 'book-row';
  tr.style.cssText = 'border-bottom:1px solid var(--surface-2)';
  tr.dataset.idx = i;

  tr.innerHTML = `
    <td style="padding:.4rem .6rem;text-align:center;font-family:var(--font-latin);
               font-size:.75rem;color:var(--text-muted);font-weight:600" class="row-num"></td>
    <td style="padding:.3rem .4rem;position:relative">
      <input type="text" name="books[${i}][book_title]"
             class="form-control form-control-sm book-title-input"
             placeholder="ឈ្មោះ..." value="${escHtml(data.title||'')}"
             autocomplete="off" required style="font-size:.84rem">
      <input type="hidden" name="books[${i}][book_id]"
             class="book-id-input" value="${data.id||''}">
      <div class="ac-dropdown"
           style="display:none;position:absolute;left:0;right:0;top:100%;z-index:200;
                  background:#fff;border:1px solid var(--border);border-radius:var(--radius);
                  box-shadow:0 8px 24px rgba(0,0,0,.12);max-height:200px;overflow-y:auto"></div>
    </td>
    <td style="padding:.3rem .4rem">
      <input type="text" name="books[${i}][grade]"
             class="form-control form-control-sm"
             value="${escHtml(data.grade||'')}"
             style="font-size:.84rem;font-family:var(--font-latin)">
    </td>
    <td style="padding:.3rem .4rem">
      <select name="books[${i}][category]" class="form-select form-select-sm" style="font-size:.82rem">
        <option value="" ${!data.category?'selected':''}>—</option>
        <option value="perfect_binding" ${data.category==='perfect_binding'?'selected':''}>បិតក្បាល</option>
        <option value="staple" ${data.category==='staple'?'selected':''}>កិបកណ្ដាល</option>
      </select>
    </td>
    <td style="padding:.3rem .4rem">
      <input type="number" name="books[${i}][quantity_requested]"
             class="form-control form-control-sm qty-input"
             min="1" value="${data.qty||1}" required
             style="text-align:right;font-family:var(--font-latin);font-weight:700">
    </td>
    <td style="padding:.3rem .4rem">
      <input type="text" name="books[${i}][notes]"
             class="form-control form-control-sm"
             value="${escHtml(data.notes||'')}" style="font-size:.82rem">
    </td>
    <td style="padding:.3rem .4rem;text-align:center">
      <button type="button" class="btn btn-ghost btn-sm remove-row"
              style="color:var(--danger);padding:.15rem .35rem">
        <i class="bi bi-x-lg"></i>
      </button>
    </td>
  `;

  tr.querySelector('.remove-row').addEventListener('click', () => {
    tr.remove(); reIndex(); updateTotals();
  });
  tr.querySelector('.qty-input').addEventListener('input', updateTotals);
  setupAC(tr);
  return tr;
}

function setupAC(row) {
  const inp    = row.querySelector('.book-title-input');
  const hidden = row.querySelector('.book-id-input');
  const grade  = row.querySelectorAll('input')[1];
  const cat    = row.querySelector('select');
  const drop   = row.querySelector('.ac-dropdown');

  function show(matches) {
    drop.innerHTML = '';
    if (!matches.length) { drop.style.display='none'; return; }
    matches.slice(0,10).forEach(b => {
      const d = document.createElement('div');
      d.style.cssText = 'padding:.45rem .8rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid var(--border)';
      d.innerHTML = `<span style="font-weight:600">${escHtml(b.title)}</span>` +
        (b.grade ? `<span style="margin-left:.35rem;font-family:var(--font-latin);font-size:.7rem;background:#f0fdf4;color:#15803d;padding:.05em .4em;border-radius:4px">ថ្នាក់ ${b.grade}</span>` : '') +
        `<span style="float:right;font-size:.7rem;color:var(--text-muted)">${escHtml(b.catLabel)}</span>`;
      d.addEventListener('mousedown', e => {
        e.preventDefault();
        inp.value=b.title; hidden.value=b.id; grade.value=b.grade; cat.value=b.category;
        drop.style.display='none';
      });
      d.addEventListener('mouseover', () => d.style.background='var(--surface-2)');
      d.addEventListener('mouseout',  () => d.style.background='');
      drop.appendChild(d);
    });
    drop.style.display='block';
  }

  inp.addEventListener('input', () => {
    hidden.value='';
    const q = inp.value.toLowerCase().trim();
    if (!q) { drop.style.display='none'; return; }
    show(BOOKS.filter(b => b.title.toLowerCase().includes(q)));
  });
  inp.addEventListener('blur', () => setTimeout(() => drop.style.display='none', 150));
  inp.addEventListener('focus', () => {
    const q = inp.value.toLowerCase().trim();
    if (q) show(BOOKS.filter(b => b.title.toLowerCase().includes(q)));
  });
}

function reIndex() {
  document.querySelectorAll('#booksBody .book-row').forEach((r,i) => {
    const n = r.querySelector('.row-num');
    if (n) n.textContent = i+1;
  });
  const cnt = document.querySelectorAll('#booksBody .book-row').length;
  document.getElementById('rowCount').textContent = cnt + ' ចំណងជើង';
}

function updateTotals() {
  let t = 0;
  document.querySelectorAll('.qty-input').forEach(inp => t += parseInt(inp.value,10)||0);
  document.getElementById('totalQty').textContent = t.toLocaleString();
}

function addRow(data={}) {
  document.getElementById('booksBody').appendChild(makeRow(data));
  reIndex(); updateTotals();
}

// Pre-fill existing items
document.addEventListener('DOMContentLoaded', () => {
  (EXISTING_ITEMS||[]).forEach(item => addRow(item));
  if (!EXISTING_ITEMS || !EXISTING_ITEMS.length) addRow();
});

document.getElementById('addRowBtn').addEventListener('click', () => addRow());

// Paste from Excel
document.addEventListener('paste', e => {
  if (!document.activeElement?.closest('#booksTable')) return;
  e.preventDefault();
  const text = (e.clipboardData||window.clipboardData).getData('text');
  if (!text.trim()) return;
  let added = 0;
  text.trim().split(/\r?\n/).forEach(line => {
    const cols = line.split('\t');
    if (!cols[0]?.trim()) return;
    const title = cols[0].trim();
    const grade = cols[1]?.trim()||'';
    let cat = (cols[2]?.trim()||'').toLowerCase();
    const qty = parseInt(cols[3]?.trim()||'1',10)||1;
    const notes = cols[4]?.trim()||'';
    if (cat.includes('perfect')||cat.includes('bind')||cat.includes('បិត')) cat='perfect_binding';
    else if (cat.includes('staple')||cat.includes('kib')||cat.includes('កិប')) cat='staple';
    else cat='';
    const match = BOOKS.find(b=>b.title.toLowerCase()===title.toLowerCase());
    addRow({id:match?match.id:'',title:match?match.title:title,
            grade:grade||(match?match.grade:''),category:cat||(match?match.category:''),qty,notes});
    added++;
  });
  if (added) showToast('success', `Paste ជោគជ័យ — ${added} ជួរ`);
});

// File upload
const dz   = document.getElementById('dropZone');
const fi   = document.getElementById('fileInput');
const pv   = document.getElementById('filePreviewList');
let dt = new DataTransfer();

function addFiles(files) {
  for (const f of files) {
    if (dt.files.length >= 10) { showToast('warning','Max 10 files'); break; }
    if (f.size > 10*1024*1024) { showToast('warning',`"${f.name}" ធំលើស 10MB`); continue; }
    dt.items.add(f);
  }
  renderPreviews();
}

function renderPreviews() {
  fi.files = dt.files;
  pv.innerHTML = '';
  [...dt.files].forEach((f,i) => {
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:.45rem .65rem;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm)';
    div.innerHTML = `
      <i class="bi bi-file-earmark" style="color:var(--primary);flex-shrink:0"></i>
      <div style="flex:1;min-width:0">
        <div style="font-size:.78rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(f.name)}</div>
        <div style="font-size:.68rem;color:var(--text-muted);font-family:var(--font-latin)">${(f.size/1024).toFixed(1)} KB</div>
      </div>
      <button type="button" data-idx="${i}" class="btn btn-ghost btn-sm rm-file" style="color:var(--danger);padding:.1rem .3rem">
        <i class="bi bi-x-lg"></i>
      </button>`;
    div.querySelector('.rm-file').addEventListener('click', () => {
      const nd = new DataTransfer();
      [...dt.files].forEach((ff,ii) => { if(ii!==i) nd.items.add(ff); });
      dt=nd; renderPreviews();
    });
    pv.appendChild(div);
  });
}

dz.addEventListener('click', () => fi.click());
fi.addEventListener('change', () => addFiles(fi.files));
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor='var(--primary)'; });
dz.addEventListener('dragleave', () => dz.style.borderColor='');
dz.addEventListener('drop', e => { e.preventDefault(); dz.style.borderColor=''; addFiles(e.dataTransfer.files); });
</script>

<style>.ac-dropdown div:last-child{border-bottom:none!important}</style>
@endpush
