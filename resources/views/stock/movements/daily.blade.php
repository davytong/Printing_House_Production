@extends('layouts.app')
@php
  // Get category labels from settings for consistency
  $catMeta = [
    'paper'      => ['❖', '<i class="fa-solid fa-file-lines"></i>', \App\Models\Setting::get('category_label_paper', 'ក្រដាស (Paper)'), 'background:#dbeafe;color:#1d4ed8','kpi-blue'],
    'film'       => ['❖', '<i class="fa-solid fa-tape"></i>', \App\Models\Setting::get('category_label_film', 'Lamination Film (ស្គុត)'), 'background:#f5f3ff;color:#7c3aed','kpi-purple'],
    'consumable' => ['❖', '<i class="fa-solid fa-bottle-droplet"></i>', \App\Models\Setting::get('category_label_consumable', 'Consumable (សម្ភារៈប្រើប្រាស់)'), 'background:#dcfce7;color:#15803d','kpi-green'],
  ];
  // Fall back if an unknown category comes in
  if (!isset($catMeta[$category])) {
    $catMeta[$category] = ['📦', '<i class="bi bi-box-seam-fill"></i>', ucfirst($category), 'background:#f1f5f9;color:#475569', 'kpi-blue'];
  }
  [$catEmoji,$catIcon,$catLabel,$catStyle,$catCls] = $catMeta[$category];
@endphp
@section('title',"បច្ចុប្បន្នភាព {$catLabel}")
@section('page-title','Daily Stock Update')

@section('breadcrumbs')
<div class="breadcrumbs">
  <a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <a href="{{ route('stock.movements.index') }}">Stock</a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <span class="bc-active">Daily Report</span>
</div>
@endsection

@section('content')

{{-- Category switcher --}}
<div class="panel mb-4">
  <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;padding:.75rem 1rem">
    <span style="font-size:.85rem;font-weight:700;color:var(--text-muted)">ជ្រើសរើសផ្នែក៖</span>
    @foreach($catMeta as $val => [$e,$i,$l,,])
      <a href="{{ route('stock.movements.daily') }}?category={{ $val }}"
         class="btn btn-sm {{ $category===$val ? 'btn-primary' : 'btn-outline-secondary' }}" style="font-size:.85rem">
        {!! $i !!} {{ $l }}
      </a>
    @endforeach
  </div>
</div>

{{-- Header --}}
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title" style="display:flex;align-items:center;gap:.5rem">
      <span style="font-size:1.5rem">{!! $catIcon !!}</span>
      <span>រាយការណ៍ {{ $catLabel }}</span>
    </h1>
    <p class="section-sub">បំពេញ​ចំនួន​ Stock​ ដែល​នៅ​សល់​ ហើយ​ចុច​ "រក្សាទុក &amp; ផ្ញើ"</p>
  </div>
</div>

@if(session('success'))
  <div style="background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);padding:.9rem 1.2rem;margin-bottom:1.2rem;color:#15803d;font-weight:600;display:flex;gap:.6rem;align-items:center">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
  </div>
@endif

@if($materials->isEmpty())
  <div class="panel">
    <div class="panel-body">
      <div class="empty-state">
        <div class="empty-icon">{!! $catIcon !!}</div>
        <p style="font-weight:600">មិនទាន់មានទំនិញ {{ $catLabel }}</p>
        <a href="{{ route('stock.materials.create') }}?category={{ $category }}" class="btn btn-primary btn-sm mt-2">
          <i class="bi bi-plus-lg"></i> បន្ថែម Material
        </a>
      </div>
    </div>
  </div>
@else

<form id="stockDailyForm" action="{{ route('stock.movements.daily-store') }}" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="category" value="{{ $category }}">

  <style>
    .qty-box { display:flex; align-items:center; gap:.35rem; justify-content:center; }
    .qty-step {
      width:40px; height:44px; flex-shrink:0;
      border:1.5px solid var(--border); background:var(--surface-2);
      border-radius:10px; font-size:1.4rem; font-weight:700; line-height:1;
      color:var(--text-secondary); cursor:pointer; user-select:none;
      display:flex; align-items:center; justify-content:center;
      transition:transform .08s, background .15s, border-color .15s, color .15s;
    }
    .qty-step:active { transform:scale(.9); }
    .qty-step.dec:hover { border-color:#f87171; background:#fef2f2; color:#dc2626; }
    .qty-step.inc:hover { border-color:#34d399; background:#ecfdf5; color:#059669; }
    .qty-result .pill { font-weight:700; }
  </style>

  <div class="row g-4">
    {{-- LEFT: qty inputs --}}
    <div class="col-lg-8">

      {{-- Unsaved draft recovery banner --}}
      <div id="draftRecoveryBanner" class="alert alert-warning alert-dismissible fade show d-none align-items-center justify-content-between mb-3 shadow-sm border-warning" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-clock-history fs-4 text-warning"></i>
          <div>
            <strong style="font-size:.88rem">រកឃើញទិន្នន័យព្រាងដែលមិនទាន់រក្សាទុក!</strong>
            <div style="font-size:.76rem;color:var(--text-secondary)">អ្នកមានទិន្នន័យដែលបានបញ្ចូលលើទូរស័ព្ទនេះពីមុន។ តើអ្នកចង់ស្តារឡើងវិញទេ?</div>
          </div>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-warning fw-bold" id="btnRestoreDraft"><i class="bi bi-arrow-repeat"></i> ស្តារឡើងវិញ</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDiscardDraft">លុបចោល</button>
        </div>
      </div>

      {{-- Reporter info bar --}}
      <div class="panel mb-4">
        <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;padding:1rem">
          <div style="flex:1;min-width:160px">
            <label class="form-label" style="font-size:.8rem"><i class="bi bi-calendar3"></i> ថ្ងៃខែ</label>
            <input type="date" name="update_date" class="form-control form-control-sm"
                   value="{{ $updateDate ?? now()->format('Y-m-d') }}" style="font-family:var(--font-latin)" required>
          </div>
          <div style="flex:2;min-width:160px">
            <label class="form-label" style="font-size:.8rem"><i class="bi bi-person-fill"></i> ឈ្មោះអ្នករាយការណ៍</label>
            <input type="text" name="performed_by" class="form-control form-control-sm"
                   placeholder="ឧ. លោក សុខ" value="{{ old('performed_by') }}">
          </div>
        </div>
      </div>

      {{-- Simple hint --}}
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius);
                  padding:.55rem .9rem;margin-bottom:1rem;font-size:.82rem;color:#1e40af;
                  display:flex;gap:.5rem;align-items:center">
        <i class="bi bi-hand-index-thumb" style="font-size:1rem"></i>
        <span>ចុច <strong>−</strong> ឬ <strong>+</strong> ដើម្បីកែចំនួន ឬធ្វើប្រមាណវិធីផ្ទាល់ក្នុងប្រអប់ — ប្រព័ន្ធបង្ហាញ «ស្តុកថ្មី» ភ្លាមៗ</span>
      </div>

      {{-- Materials grouped by sub_type --}}
      @php
        $grouped = $materials->groupBy('sub_type');
      @endphp

      @foreach($grouped as $subType => $items)
        <div class="panel mb-3">
          <div class="panel-header" style="padding:.65rem 1rem">
            <div class="ph-title">
              <div class="ph-icon" style="{{ $catStyle }};width:30px;height:30px;font-size:.9rem">{!! $catIcon !!}</div>
              <span style="font-size:.88rem;font-weight:700">{{ $subType ?: $catLabel }}</span>
              <span class="badge badge-binding" style="font-family:var(--font-latin)">{{ $items->count() }}</span>
            </div>
          </div>
          <div class="panel-body" style="padding:.5rem 1rem">
            @foreach($items as $idx => $m)
              @php
                $stock = $m->calculated_stock;
                $isLow = $m->is_low ?? $stock <= (float)$m->min_stock;
                $stockColor = $stock <= 0 ? '#dc2626' : ($isLow ? '#d97706' : '#15803d');
              @endphp
              <div style="display:flex;align-items:center;gap:.75rem;padding:.55rem 0;
                          border-bottom:1px solid var(--surface-2);flex-wrap:wrap">
                <input type="hidden" name="items[{{ $loop->parent->index * 100 + $loop->index }}][material_id]"
                       value="{{ $m->id }}">

                {{-- Name --}}
                <div style="flex:1;min-width:140px">
                  <div style="font-weight:700;font-size:.9rem" data-name="{{ $m->name }}" data-name-km="{{ $m->name_km }}" data-size="{{ $m->size }}">
                    {{ $m->name }}
                    @if($m->size)
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size:.7rem;vertical-align:middle;font-family:var(--font-latin)">{{ $m->size }}</span>
                    @endif
                    @if($m->name_km)
                      <span style="display:block;font-size:.78rem;font-weight:500;color:var(--text-secondary);font-family:var(--font-khmer)">{{ $m->name_km }}</span>
                    @endif
                  </div>
                  @if($isLow && $stock > 0)
                    <div style="font-size:.7rem;color:#d97706"><i class="bi bi-exclamation-triangle-fill"></i> Stock ទាប (Min: {{ number_format($m->min_stock,0) }})</div>
                  @elseif($stock <= 0)
                    <div style="font-size:.7rem;color:#dc2626"><i class="bi bi-x-circle-fill"></i> អស់ Stock</div>
                  @endif
                </div>

                {{-- Current stock (read-only display) --}}
                <div style="text-align:center;min-width:80px">
                  <div style="font-size:.68rem;color:var(--text-muted);margin-bottom:.1rem">Stock ចាស់</div>
                  <div style="font-family:var(--font-latin);font-weight:700;font-size:.95rem;color:{{ $stockColor }}">
                    {{ number_format($stock, 0) }}
                    <span style="font-size:.7rem;font-weight:400;color:var(--text-muted)">{{ $m->unit }}</span>
                  </div>
                </div>

                {{-- Arrow --}}
                <div style="color:var(--text-muted);font-size:1rem">→</div>

                {{-- New qty — tap −/+ or type (calc supported) --}}
                <div style="min-width:170px">
                  <div style="font-size:.68rem;color:var(--text-muted);margin-bottom:.2rem;text-align:center">Stock ថ្មី ({{ $m->unit }})</div>
                  <div class="qty-box">
                    <button type="button" class="qty-step dec" tabindex="-1" aria-label="ដក">−</button>
                    <input type="text"
                           class="form-control qty-input"
                           value="{{ number_format($stock, 0, '.', '') }}"
                           data-original="{{ number_format($stock, 0, '.', '') }}"
                           data-value="{{ number_format($stock, 0, '.', '') }}"
                           data-today-in="{{ (float)($m->today_in ?? 0) }}"
                           data-today-out="{{ (float)($m->today_out ?? 0) }}"
                           inputmode="text"
                           autocomplete="off"
                           spellcheck="false"
                           style="font-family:var(--font-latin);font-weight:800;font-size:1.15rem;text-align:center;width:84px;padding-left:.3rem;padding-right:.3rem">
                    <button type="button" class="qty-step inc" tabindex="-1" aria-label="បន្ថែម">+</button>
                  </div>
                  <input type="hidden"
                         name="items[{{ $loop->parent->index * 100 + $loop->index }}][current_stock]"
                         class="qty-value"
                         value="{{ number_format($stock, 0, '.', '') }}">
                  <div class="qty-result"
                       style="font-size:.74rem;text-align:center;margin-top:.3rem;min-height:1.1em;
                              font-family:var(--font-khmer);color:var(--text-muted)"></div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach

    </div>

    {{-- RIGHT: Telegram send options --}}
    <div class="col-lg-4">
      <div class="panel" style="position:sticky;top:1rem">
        <div class="panel-header">
          <div class="ph-title">
            <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-send-fill"></i></div>
            <span>ផ្ញើ Telegram</span>
          </div>
        </div>
        <div class="panel-body">

          {{-- Send toggle --}}
          <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;
                      padding:.75rem;background:var(--surface-2);border-radius:var(--radius)">
            <input type="checkbox" id="sendToggle" name="send_telegram" value="1" class="form-check-input"
                   style="width:1.2rem;height:1.2rem;margin:0" checked>
            <label for="sendToggle" style="font-weight:700;font-size:.88rem;cursor:pointer;margin:0">
              ផ្ញើរបាយការណ៍ទៅ Telegram ផង
            </label>
          </div>

          <div id="telegramOptions">
            @if($telegramGroups->isEmpty())
              <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:var(--radius);
                          padding:.75rem;font-size:.82rem;color:#92400e">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                No Telegram Group configured — <a href="{{ route('telegram.setup') }}">Setup</a>
              </div>
            @else
              {{-- Auto-assigned destination based on category --}}
              @php
                $activeGroup = $defaultGroup ?? $telegramGroups->first();
              @endphp
              @if($activeGroup)
                <div style="background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);
                            padding:.7rem .9rem;margin-bottom:.75rem;font-size:.82rem;color:#14532d">
                  <i class="bi bi-check-circle-fill me-1"></i>
                  <strong>Send to:</strong> <span id="selectedGroupLabel">{{ $activeGroup->displayLabel() }}</span>
                </div>
                <input type="hidden" name="chat_id" id="dailyChatId" value="{{ $activeGroup->chat_id }}">
                <input type="hidden" name="message_thread_id" id="dailyThreadId" value="{{ $activeGroup->message_thread_id ?? '' }}">
              @endif

              {{-- Optional override --}}
              <details style="margin-top:.5rem">
                <summary style="font-size:.75rem;color:var(--text-muted);cursor:pointer;user-select:none">
                  <i class="bi bi-gear"></i> Change destination
                </summary>
                <div style="margin-top:.5rem">
                  <select id="dailyGroupSelect" class="form-select form-select-sm">
                    @foreach($telegramGroups as $g)
                      <option value="{{ $g->chat_id }}|{{ $g->message_thread_id ?? '' }}"
                        {{ $activeGroup && $g->id === $activeGroup->id ? 'selected' : '' }}>
                        {{ $g->displayLabel() }}
                        @if($g->purpose) [{{ $g->purpose }}] @endif
                      </option>
                    @endforeach
                  </select>
                </div>
              </details>
            @endif

            {{-- Multi-image upload --}}
            <div class="mb-3">
              <label class="form-label" style="font-size:.82rem">📸 ភ្ជាប់រូបភាព (អាចជ្រើសច្រើន)</label>
              <div id="imageDropZone"
                   style="border:2px dashed var(--border);border-radius:var(--radius);padding:1.2rem;
                          text-align:center;cursor:pointer;transition:.15s;background:var(--surface-2)"
                   onclick="document.getElementById('imageInput').click()"
                   ondragover="event.preventDefault();this.style.borderColor='var(--primary)'"
                   ondragleave="this.style.borderColor='var(--border)'"
                   ondrop="handleDrop(event)">
                <i class="bi bi-images" style="font-size:1.5rem;color:var(--text-muted)"></i>
                <p style="font-size:.78rem;color:var(--text-muted);margin:.3rem 0 0">
                  ចុចដើម្បីជ្រើស / ថតរូប ឬ Drag &amp; Drop រូបភាព<br>
                  <span style="font-size:.7rem" class="text-success"><i class="bi bi-magic"></i> បង្រួមទំហំស្វ័យប្រវត្ត · ផ្ញើបានរហ័សលើទូរស័ព្ទ (Auto-compressed)</span>
                </p>
              </div>
              <input type="file" id="imageInput" name="images[]" multiple accept="image/*"
                     style="display:none" onchange="previewImages(this.files)">

              {{-- Thumbnail previews --}}
              <div id="imagePreviews" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.5rem"></div>
            </div>

            {{-- Preview of what will be sent --}}
            <div style="background:var(--surface-2);border-radius:var(--radius);padding:.8rem;
                        font-size:.78rem;color:var(--text-secondary);white-space:pre-line;
                        font-family:monospace;max-height:220px;overflow-y:auto"
                 id="telegramPreview">
              {{-- filled by JS --}}
              <span style="color:var(--text-muted)">ពិនិត្យ​ preview ក្រោយ​បំពេញ​ qty...</span>
            </div>
          </div>

        </div>
        <div class="panel-body" style="border-top:1px solid var(--border);display:flex;flex-direction:column;gap:.6rem">
          <button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-check-circle-fill"></i> រក្សាទុក &amp; ផ្ញើ
          </button>
          <button type="submit" formaction="{{ route('stock.movements.daily-store') }}" class="btn btn-outline-secondary w-100"
                  id="saveOnlyBtn">
            <i class="bi bi-check-lg"></i> រក្សាទុកតែ (មិនផ្ញើ)
          </button>
        </div>
      </div>
    </div>

  </div>
</form>
@endif

{{-- Low Stock Alert Confirmation Modal --}}
<div class="modal fade" id="lowStockAlertModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content border-warning shadow-lg">
      <div class="modal-header bg-warning-subtle text-dark p-3">
        <h5 class="modal-title d-flex align-items-center gap-2 fw-bold fs-6 fs-sm-5 mb-0">
          <i class="bi bi-exclamation-triangle-fill text-warning fs-4 flex-shrink-0"></i>
          <span>Low Stock Alert — របាយការណ៍ស្តុក ជិតអស់</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3 p-sm-4">
        <p class="fs-6 fw-bold text-danger mb-3">
          <i class="bi bi-info-circle-fill me-1"></i> មានសម្ភារៈមួយចំនួនមានស្តុកជិតអស់ ឬអស់ពីស្តុក។
        </p>

        {{-- Low stock items preview table --}}
        <div class="table-responsive mb-3 rounded border">
          <table class="table table-sm table-bordered align-middle mb-0" style="font-size:.85rem; min-width: 460px;">
            <thead class="table-light">
              <tr>
                <th>ឈ្មោះទំនិញ</th>
                <th>ផ្នែក</th>
                <th>Stock ថ្មី</th>
                <th>Low Threshold</th>
                <th>ស្ថានភាព (Status)</th>
              </tr>
            </thead>
            <tbody id="lowStockModalItemsTable">
              {{-- Filled dynamically by JS --}}
            </tbody>
          </table>
        </div>

        <div class="p-3 bg-light border rounded text-center my-2">
          <p class="fs-6 fw-bold mb-1" style="color:var(--text-main)">
            តើអ្នកចង់ផ្ញើរសារទៅអ្នកដឹកនាំអំពី "របាយការណ៍ស្តុក ជិតអស់" ដែរឬទេ?
          </p>
          <span class="text-muted fs-8 d-block">(Do you want to send a Low Stock report notification to the Leader Group?)</span>
        </div>
      </div>
      <div class="modal-footer p-3 d-flex flex-column flex-sm-row justify-content-between gap-2">
        <button type="button" class="btn btn-outline-secondary w-100 w-sm-auto px-4 order-2 order-sm-1" id="btnSkipLeaderAlert">
          <i class="bi bi-x-circle me-1"></i> មិនផ្ញើ (No)
        </button>
        <button type="button" class="btn btn-primary w-100 w-sm-auto px-4 fw-bold order-1 order-sm-2" id="btnProceedLeaderAlert">
          <i class="bi bi-send-fill me-1"></i> ផ្ញើសារ (Yes, Send)
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const category = '{{ $category }}';
const catLabel = @json($catLabel);
const catEmoji = '{{ $catEmoji }}';
const nameFormat = '{{ $nameFormat }}'; // 'both', 'khmer', or 'english'
const catTags  = {
  paper: '#Paper_Stock', film: '#Film_Stock',
  consumable: '#Consumable_Stock'
};
const catTag = catTags[category] || '#Stock';

// ── Group selector → hidden fields ────────────────────────
function syncGroupSelection() {
  const sel      = document.getElementById('dailyGroupSelect');
  const chatIn   = document.getElementById('dailyChatId');
  const threadIn = document.getElementById('dailyThreadId');
  if (!sel || !chatIn) return;
  const [chatId, threadId] = (sel.value || '').split('|');
  chatIn.value   = chatId   || '';
  threadIn.value = threadId || '';
  updatePreview();
}
// Sync on change
document.getElementById('dailyGroupSelect')?.addEventListener('change', syncGroupSelection);
// Sync on page load (set defaults from first option)
syncGroupSelection();

// ── Calculator-style stock input ───────────────────────────
// Accepts: "-5" (subtract from current), "-1,-2" (subtract 1 then 2),
//          "100-5-3" or "100" (absolute). Shows live result + delta.
function evalStockExpr(expr, original) {
  expr = (expr || '').trim();
  if (expr === '') return { ok: false };

  // Comma list → sum of signed deltas relative to the original stock
  if (expr.includes(',')) {
    const parts = expr.split(',').map(s => s.trim()).filter(s => s !== '');
    let sum = 0;
    for (const p of parts) {
      if (!/^[-+]?\d+(\.\d+)?$/.test(p)) return { ok: false };
      sum += parseFloat(p);
    }
    return { ok: true, value: Math.max(0, Math.round(original + sum)) };
  }

  // Only allow safe math characters
  if (!/^[-+*/().\d\s]+$/.test(expr)) return { ok: false };

  // Leading + or - means "relative to current stock"
  let toEval = /^[+\-]/.test(expr) ? (original + '+(' + expr + ')') : expr;

  try {
    const val = Function('"use strict"; return (' + toEval + ');')();
    if (typeof val !== 'number' || !isFinite(val)) return { ok: false };
    return { ok: true, value: Math.max(0, Math.round(val)) };
  } catch (e) {
    return { ok: false };
  }
}

document.querySelectorAll('.qty-input').forEach(inp => {
  const original = parseFloat(inp.dataset.original) || 0;
  const box      = inp.parentElement;               // .qty-box
  const wrap     = box.parentElement;               // outer cell
  const hidden   = wrap.querySelector('.qty-value');
  const result   = wrap.querySelector('.qty-result');

  function render() {
    const r = evalStockExpr(inp.value, original);

    if (!r.ok) {
      inp.style.borderColor = '#ef4444';
      inp.style.background  = '#fef2f2';
      inp.style.setProperty('color', '#b91c1c', 'important'); // Force dark red text
      if (result) { result.style.color = '#ef4444'; result.innerHTML = 'សូមវាយលេខ'; }
      return null;
    }

    const val   = r.value;
    const delta = val - original;
    if (hidden) hidden.value = val;
    inp.dataset.value = val;

    const todayInRecorded  = parseFloat(inp.dataset.todayIn)  || 0;
    const todayOutRecorded = parseFloat(inp.dataset.todayOut) || 0;
    const totalOut = todayOutRecorded + (delta < 0 ? Math.abs(delta) : 0);
    const totalIn  = todayInRecorded  + (delta > 0 ? delta : 0);

    if (delta < 0 || totalOut > 0) {
      inp.style.background = '#fffbeb'; inp.style.borderColor = '#fbbf24';
      inp.style.setProperty('color', '#b45309', 'important'); // Force dark amber text
      result.style.color = '#b45309';
      result.innerHTML = `ស្តុកថ្មី <span class="pill">${val.toLocaleString()}</span> · បានប្រើ ${totalOut.toLocaleString()}`;
    } else if (delta > 0 || totalIn > 0) {
      inp.style.background = '#ecfdf5'; inp.style.borderColor = '#34d399';
      inp.style.setProperty('color', '#047857', 'important'); // Force dark emerald text
      result.style.color = '#15803d';
      result.innerHTML = `ស្តុកថ្មី <span class="pill">${val.toLocaleString()}</span> · ចូលស្តុក ${totalIn.toLocaleString()}`;
    } else {
      inp.style.background = ''; inp.style.borderColor = '';
      inp.style.removeProperty('color'); // Reset to theme default
      result.style.color = 'var(--text-muted)';
      result.innerHTML = `ស្តុកថ្មី <span class="pill">${val.toLocaleString()}</span>`;
    }
    updatePreview();
    return val;
  }

  function setAbsolute(v) { inp.value = Math.max(0, v); render(); }
  function currentVal() { return parseFloat(hidden.value) || 0; }

  // −/+ stepper buttons
  box.querySelector('.dec')?.addEventListener('click', () => setAbsolute(currentVal() - 1));
  box.querySelector('.inc')?.addEventListener('click', () => setAbsolute(currentVal() + 1));

  inp.addEventListener('input', render);
  // On leaving the field, resolve any expression to the final number (e.g. "-5" → 95)
  inp.addEventListener('blur', () => { const v = render(); if (v !== null) inp.value = v; });
  inp.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); const v = render(); if (v !== null) inp.value = v; inp.blur(); }
  });

  render(); // initialise
});

// "Save only" — uncheck telegram before submit
document.getElementById('saveOnlyBtn')?.addEventListener('click', () => {
  document.getElementById('sendToggle').checked = false;
});

// ── CSRF Keep-Alive & Auto-Refresh ─────────────────────────
let activeCsrfToken = '{{ csrf_token() }}';

async function refreshCsrfToken() {
  try {
    const res = await fetch('{{ route("ping") }}', { credentials: 'same-origin' });
    if (res.ok) {
      const data = await res.json();
      if (data.csrf_token) {
        activeCsrfToken = data.csrf_token;
        document.querySelectorAll('input[name="_token"]').forEach(el => el.value = activeCsrfToken);
        const metaCsrf = document.querySelector('meta[name="csrf-token"]');
        if (metaCsrf) metaCsrf.setAttribute('content', activeCsrfToken);
      }
    }
  } catch (e) {
    console.warn('CSRF ping failed:', e);
  }
}

// Background heartbeat every 4 minutes while tab is active
setInterval(refreshCsrfToken, 4 * 60 * 1000);

// ── Low Stock Alert & Leader Notification Workflow ─────────────
(function () {
  const form = document.getElementById('stockDailyForm');
  if (!form) return;

  let allowDirectSubmit = false;
  let detectedLowStockItems = [];

  form.addEventListener('submit', async function (e) {
    if (allowDirectSubmit) {
      if (typeof clearDraft === 'function') clearDraft();
      // Show loading overlay
      if (typeof showLoading === 'function') showLoading(true, 'កំពុងរក្សាទុក និងផ្ញើ...');
      return;
    }

    e.preventDefault();

    // Refresh CSRF token right before submit to prevent 419 expired error on mobile
    await refreshCsrfToken();

    // Gather item values from form
    const items = [];
    document.querySelectorAll('.qty-input').forEach(inp => {
      const wrap = inp.closest('div[style*="display:flex"]');
      const matIdInput = wrap?.querySelector('input[name*="[material_id]"]');
      if (matIdInput) {
        items.push({
          material_id: parseInt(matIdInput.value),
          current_stock: parseFloat(inp.dataset.value ?? inp.value) || 0,
        });
      }
    });

    if (items.length === 0) {
      allowDirectSubmit = true;
      if (typeof clearDraft === 'function') clearDraft();
      form.submit();
      return;
    }

    // Call check API
    fetch('{{ route("stock.low-stock.check") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': activeCsrfToken
      },
      body: JSON.stringify({ items: items })
    })
    .then(res => res.json())
    .then(data => {
      if (data.has_low_stock && data.items && data.items.length > 0) {
        if (typeof showLoading === 'function') showLoading(false);
        detectedLowStockItems = data.items;
        renderLowStockModalTable(data.items);

        const alertModal = new bootstrap.Modal(document.getElementById('lowStockAlertModal'));
        alertModal.show();
      } else {
        // No low stock items -> direct submit
        allowDirectSubmit = true;
        if (typeof clearDraft === 'function') clearDraft();
        if (typeof showLoading === 'function') showLoading(true, 'កំពុងរក្សាទុក...');
        form.submit();
      }
    })
    .catch(err => {
      console.error('Low stock check failed:', err);
      allowDirectSubmit = true;
      if (typeof clearDraft === 'function') clearDraft();
      form.submit();
    });
  });

  function renderLowStockModalTable(items) {
    const tbody = document.getElementById('lowStockModalItemsTable');
    if (!tbody) return;
    tbody.innerHTML = '';

    items.forEach(it => {
      const tr = document.createElement('tr');
      const nameStr = it.name_km ? `${it.name} (${it.name_km})` : it.name;
      const sizeStr = it.size ? ` (${it.size})` : '';
      const stockStr = `${it.current_stock} ${it.unit}`;

      tr.innerHTML = `
        <td class="fw-bold">${nameStr}${sizeStr}</td>
        <td><span class="badge bg-light text-dark border">${it.category_label || it.category}</span></td>
        <td class="fw-bold text-danger">${stockStr}</td>
        <td>${it.low_stock_threshold} ${it.unit}</td>
        <td>${it.status_badge}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Handle "មិនផ្ញើ" (No / Skip)
  document.getElementById('btnSkipLeaderAlert')?.addEventListener('click', function () {
    const alertModalEl = document.getElementById('lowStockAlertModal');
    const alertModal = bootstrap.Modal.getInstance(alertModalEl);
    if (alertModal) alertModal.hide();

    allowDirectSubmit = true;
    if (typeof clearDraft === 'function') clearDraft();
    form.submit();
  });

  // Handle "ផ្ញើសារ" (Yes / Send Leader Alert & Submit directly)
  document.getElementById('btnProceedLeaderAlert')?.addEventListener('click', function () {
    const alertModalEl = document.getElementById('lowStockAlertModal');
    const alertModal = bootstrap.Modal.getInstance(alertModalEl);
    if (alertModal) alertModal.hide();

    const updateDate = document.querySelector('[name=update_date]')?.value || '';
    const performedBy = document.querySelector('[name=performed_by]')?.value || '';

    // Send Telegram alert in background (non-blocking)
    fetch('{{ route("stock.low-stock.send") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': activeCsrfToken
      },
      body: JSON.stringify({
        items: detectedLowStockItems,
        report_date: updateDate,
        performed_by: performedBy,
        category: category
      })
    }).catch(err => console.error('Low stock alert error:', err));

    allowDirectSubmit = true;
    if (typeof clearDraft === 'function') clearDraft();
    if (typeof showLoading === 'function') showLoading(true, 'កំពុងរក្សាទុក...');
    form.submit();
  });
})();

// Toggle Telegram section visibility
document.getElementById('sendToggle')?.addEventListener('change', e => {
  const opts = document.getElementById('telegramOptions');
  opts.style.opacity       = e.target.checked ? '1' : '.4';
  opts.style.pointerEvents = e.target.checked ? '' : 'none';
});

// Sync selected group dropdown with hidden inputs and green destination banner
document.getElementById('dailyGroupSelect')?.addEventListener('change', function() {
  const parts = this.value.split('|');
  const chatId = parts[0] || '';
  const threadId = parts[1] || '';
  
  const chatIdInp = document.getElementById('dailyChatId');
  const threadIdInp = document.getElementById('dailyThreadId');
  if (chatIdInp) chatIdInp.value = chatId;
  if (threadIdInp) threadIdInp.value = threadId;

  const selectedOption = this.options[this.selectedIndex];
  if (selectedOption) {
    const rawText = selectedOption.text;
    const labelText = rawText.replace(/\[.*?\]/g, '').trim();
    const labelEl = document.getElementById('selectedGroupLabel');
    if (labelEl) labelEl.textContent = labelText;
  }
});

// ── Build Telegram text preview ────────────────────────────
function updatePreview() {
  const dateVal = document.querySelector('[name=update_date]')?.value || '';
  const d = dateVal ? new Date(dateVal + 'T00:00:00') : new Date();
  const km = ['','មករា','កុម្ភៈ','មីនា','មេសា','ឧសភា','មិថុនា','កក្កដា','សីហា','កញ្ញា','តុលា','វិច្ឆិកា','ធ្នូ'];
  const dateStr = `ថ្ងៃទី ${d.getDate()} ខែ${km[d.getMonth()+1]} ឆ្នាំ ${d.getFullYear()}`;
  const by = document.querySelector('[name=performed_by]')?.value?.trim() || '';

  let lines = [
    'សូមគោរពរាយការណ៍ជូនបង ពូ 📩',
    dateStr, '',
    `${catEmoji} ${catLabel} នៅសល់មានចំនួន:`,
  ];

  let groupedItems = {};

  document.querySelectorAll('.qty-input').forEach(inp => {
    const nameEl = inp.closest('div[style*="display:flex"]')?.querySelector('[data-name]');
    const name   = nameEl?.dataset?.name    || '';
    const nameKm = nameEl?.dataset?.nameKm  || '';
    const size   = nameEl?.dataset?.size    || '';
    const qty    = parseInt(inp.dataset.value ?? inp.value) || 0;
    // Get unit from the "Stock ថ្មី (unit)" label in this cell
    const cell = inp.closest('.qty-box')?.parentElement;
    const unitLabel = cell?.querySelector('div')?.textContent?.match(/\(([^)]+)\)/)?.[1] || '';
    
    // Apply language format setting
    let display;
    if (nameFormat === 'khmer') {
      display = nameKm || name; // Khmer only
    } else if (nameFormat === 'english') {
      display = name; // English only
    } else {
      display = nameKm ? `${name} — ${nameKm}` : name; // Both (default)
    }
    
    if (size !== '') {
      display = display.replace(/ \((Large|Small|ធំ|តូច|Large Roll|Small Roll)\)/gi, '');
    }
    
    const todayInRecorded  = parseFloat(inp.dataset.todayIn)  || 0;
    const todayOutRecorded = parseFloat(inp.dataset.todayOut) || 0;
    const original         = parseFloat(inp.dataset.original) || 0;
    const delta            = qty - original;

    const totalOut = todayOutRecorded + (delta < 0 ? Math.abs(delta) : 0);
    const totalIn  = todayInRecorded  + (delta > 0 ? delta : 0);

    let parts = [];
    if (totalOut > 0) {
      parts.push(`បានប្រើ ${totalOut.toLocaleString()}`);
    }
    if (totalIn > 0) {
      parts.push(`ចូលស្តុក ${totalIn.toLocaleString()}`);
    }
    let usageText = parts.length > 0 ? ` (${parts.join(', ')})` : '';
    
    const itemStr = `- ${display} : ${qty.toLocaleString()}${unitLabel ? ' ' + unitLabel : ''}${usageText}`;
    
    if (!groupedItems[size]) {
      groupedItems[size] = [];
    }
    groupedItems[size].push(itemStr);
  });

  for (const [size, items] of Object.entries(groupedItems)) {
    if (size !== '' && category !== 'paper') {
      lines.push('');
      lines.push(`◎ ${size}:`);
    }
    lines = lines.concat(items);
  }

  if (by) { lines.push(''); lines.push(`👤 ${by}`); }
  lines.push(catTag);

  document.getElementById('telegramPreview').textContent = lines.join('\n');
}

// init preview
updatePreview();
document.querySelector('[name=update_date]')?.addEventListener('change', function() {
  const newDate = this.value;
  if (!newDate) return;
  fetch(`{{ route("stock.movements.daily-stats") }}?category=${category}&date=${newDate}`)
    .then(res => res.json())
    .then(data => {
      if (data.ok && data.stats) {
        document.querySelectorAll('.qty-input').forEach(inp => {
          const wrap = inp.closest('div[style*="display:flex"]');
          const matIdInput = wrap?.querySelector('input[name*="[material_id]"]');
          if (matIdInput && data.stats[matIdInput.value]) {
            inp.dataset.todayIn  = data.stats[matIdInput.value].today_in  || 0;
            inp.dataset.todayOut = data.stats[matIdInput.value].today_out || 0;
          } else {
            inp.dataset.todayIn  = 0;
            inp.dataset.todayOut = 0;
          }
          // trigger re-render
          inp.dispatchEvent(new Event('input'));
        });
      }
    })
    .catch(err => console.error('Failed to fetch daily stats:', err));
  updatePreview();
});
document.querySelector('[name=performed_by]')?.addEventListener('input', () => {
  updatePreview();
  saveDraftDebounced();
});

// ── LocalStorage Draft Auto-Save & Recovery ──────────────────
const DRAFT_KEY = `pt_stock_draft_${category}_${document.querySelector('[name=update_date]')?.value || 'today'}`;

function saveDraft() {
  const data = {
    performed_by: document.querySelector('[name=performed_by]')?.value || '',
    items: {},
    timestamp: Date.now()
  };
  document.querySelectorAll('.qty-input').forEach(inp => {
    const wrap = inp.closest('div[style*="display:flex"]');
    const matIdInput = wrap?.querySelector('input[name*="[material_id]"]');
    if (matIdInput) {
      data.items[matIdInput.value] = inp.value;
    }
  });
  try {
    localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
  } catch (e) {}
}

let draftTimer = null;
function saveDraftDebounced() {
  clearTimeout(draftTimer);
  draftTimer = setTimeout(saveDraft, 400);
}

// Attach auto-save to every qty input
document.querySelectorAll('.qty-input').forEach(inp => {
  inp.addEventListener('input', saveDraftDebounced);
});

function clearDraft() {
  try {
    localStorage.removeItem(DRAFT_KEY);
  } catch (e) {}
}

function checkAndOfferDraftRestore() {
  try {
    const raw = localStorage.getItem(DRAFT_KEY);
    if (!raw) return;
    const data = JSON.parse(raw);
    if (!data || !data.items) return;

    let hasDifferences = false;
    document.querySelectorAll('.qty-input').forEach(inp => {
      const wrap = inp.closest('div[style*="display:flex"]');
      const matIdInput = wrap?.querySelector('input[name*="[material_id]"]');
      if (matIdInput && data.items[matIdInput.value] !== undefined) {
        if (data.items[matIdInput.value] !== inp.value) {
          hasDifferences = true;
        }
      }
    });

    if (hasDifferences) {
      const banner = document.getElementById('draftRecoveryBanner');
      if (banner) {
        banner.classList.remove('d-none');
        banner.classList.add('d-flex');
      }
    }
  } catch (e) {}
}

function restoreDraft() {
  try {
    const raw = localStorage.getItem(DRAFT_KEY);
    if (!raw) return;
    const data = JSON.parse(raw);
    if (data.performed_by) {
      const byInp = document.querySelector('[name=performed_by]');
      if (byInp && !byInp.value) byInp.value = data.performed_by;
    }
    if (data.items) {
      document.querySelectorAll('.qty-input').forEach(inp => {
        const wrap = inp.closest('div[style*="display:flex"]');
        const matIdInput = wrap?.querySelector('input[name*="[material_id]"]');
        if (matIdInput && data.items[matIdInput.value] !== undefined) {
          inp.value = data.items[matIdInput.value];
          inp.dispatchEvent(new Event('input'));
        }
      });
    }
    const banner = document.getElementById('draftRecoveryBanner');
    if (banner) {
      banner.classList.add('d-none');
      banner.classList.remove('d-flex');
    }
    updatePreview();
  } catch (e) {}
}

document.getElementById('btnRestoreDraft')?.addEventListener('click', restoreDraft);
document.getElementById('btnDiscardDraft')?.addEventListener('click', () => {
  clearDraft();
  const banner = document.getElementById('draftRecoveryBanner');
  if (banner) {
    banner.classList.add('d-none');
    banner.classList.remove('d-flex');
  }
});

// Check draft on startup
checkAndOfferDraftRestore();

// ── Multi-image handling with Client-Side Canvas Compression ─
let selectedFiles = new DataTransfer();

async function compressImageFile(file, maxWidth = 1600, maxHeight = 1600, quality = 0.82) {
  if (!file.type.startsWith('image/') || file.type === 'image/svg+xml' || file.type === 'image/gif') {
    return file;
  }

  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        let width = img.width;
        let height = img.height;

        if (width > maxWidth || height > maxHeight) {
          if (width > height) {
            height = Math.round((height * maxWidth) / width);
            width = maxWidth;
          } else {
            width = Math.round((width * maxHeight) / height);
            height = maxHeight;
          }
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        canvas.toBlob(
          (blob) => {
            if (!blob || blob.size >= file.size) {
              resolve(file);
              return;
            }
            const cleanName = (file.name || 'photo').replace(/\.[^/.]+$/, "") + ".jpg";
            const compressedFile = new File([blob], cleanName, {
              type: 'image/jpeg',
              lastModified: Date.now(),
            });
            resolve(compressedFile);
          },
          'image/jpeg',
          quality
        );
      };
      img.onerror = () => resolve(file);
      img.src = e.target.result;
    };
    reader.onerror = () => resolve(file);
    reader.readAsDataURL(file);
  });
}

async function previewImages(files) {
  if (!files || files.length === 0) return;

  const dropZone = document.getElementById('imageDropZone');
  const originalHtml = dropZone.innerHTML;
  dropZone.innerHTML = `
    <div class="spinner-border spinner-border-sm text-primary mb-1" role="status"></div>
    <div style="font-size:.82rem;font-weight:600;color:var(--primary)">កំពុងរៀបចំរូបភាព... (Optimizing image...)</div>
    <div style="font-size:.7rem;color:var(--text-muted)">បង្រួមទំហំស្វ័យប្រវត្ត ដើម្បីផ្ញើបានលឿន និងមិនគាំង</div>
  `;

  try {
    for (const f of files) {
      if (selectedFiles.files.length >= 10) {
        alert('អាចភ្ជាប់រូបភាពបានអតិបរមា 10 ប៉ុណ្ណោះ / Max 10 photos.');
        break;
      }
      const optimized = await compressImageFile(f);
      selectedFiles.items.add(optimized);
    }
    document.getElementById('imageInput').files = selectedFiles.files;
    renderPreviews();
  } catch (err) {
    console.error('Image compression error:', err);
  } finally {
    dropZone.innerHTML = originalHtml;
    const count = selectedFiles.files.length;
    dropZone.style.borderColor = count > 0 ? 'var(--primary)' : 'var(--border)';
  }
}

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('imageDropZone').style.borderColor = 'var(--border)';
  previewImages(e.dataTransfer.files);
}

function removeImage(idx) {
  const newDT = new DataTransfer();
  Array.from(selectedFiles.files).forEach((f, i) => { if (i !== idx) newDT.items.add(f); });
  selectedFiles = newDT;
  document.getElementById('imageInput').files = selectedFiles.files;
  renderPreviews();
}

function renderPreviews() {
  const container = document.getElementById('imagePreviews');
  container.innerHTML = '';
  Array.from(selectedFiles.files).forEach((f, i) => {
    const url   = URL.createObjectURL(f);
    const wrap  = document.createElement('div');
    const sizeKb = Math.round(f.size / 1024);
    wrap.style  = 'position:relative;display:inline-block';
    wrap.innerHTML = `
      <img src="${url}" style="width:68px;height:68px;object-fit:cover;border-radius:8px;border:2px solid var(--border)">
      <span style="position:absolute;bottom:2px;left:2px;background:rgba(0,0,0,0.65);color:#fff;font-size:.58rem;padding:1px 4px;border-radius:4px;font-family:var(--font-latin)">${sizeKb}KB</span>
      <button type="button" onclick="removeImage(${i})"
        style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;
               background:#dc2626;color:#fff;border:none;font-size:.7rem;line-height:1;
               display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.2)">✕</button>`;
    container.appendChild(wrap);
  });
  // Update count badge on drop zone
  const count = selectedFiles.files.length;
  document.getElementById('imageDropZone').style.borderColor = count > 0 ? 'var(--primary)' : 'var(--border)';
}
</script>
@endpush
