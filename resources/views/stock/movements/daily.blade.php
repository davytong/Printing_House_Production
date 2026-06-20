@extends('layouts.app')
@php
  $catMeta = [
    'paper'      => ['📄','ក្រដាស','background:#dbeafe;color:#1d4ed8','kpi-blue'],
    'film'       => ['🎞️','Film (ហ្វីម)','background:#f5f3ff;color:#7c3aed','kpi-purple'],
    'consumable' => ['🧴','Consumable (សម្ភារៈប្រើប្រាស់)','background:#dcfce7;color:#15803d','kpi-green'],
  ];
  // Fall back if an unknown category comes in
  if (!isset($catMeta[$category])) {
    $catMeta[$category] = ['📦', ucfirst($category), 'background:#f1f5f9;color:#475569', 'kpi-blue'];
  }
  [$catEmoji,$catLabel,$catStyle,$catCls] = $catMeta[$category];
@endphp
@section('title',"បច្ចុប្បន្នភាព {$catLabel}")
@section('page-title','Daily Stock Update')

@section('content')

{{-- Category switcher --}}
<div class="panel mb-4">
  <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;padding:.75rem 1rem">
    <span style="font-size:.85rem;font-weight:700;color:var(--text-muted)">ជ្រើសរើសផ្នែក៖</span>
    @foreach($catMeta as $val => [$e,$l,,])
      <a href="{{ route('stock.movements.daily') }}?category={{ $val }}"
         class="btn btn-sm {{ $category===$val ? 'btn-primary' : 'btn-outline-secondary' }}" style="font-size:.85rem">
        {{ $e }} {{ $l }}
      </a>
    @endforeach
  </div>
</div>

{{-- Header --}}
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title" style="display:flex;align-items:center;gap:.5rem">
      <span style="font-size:1.5rem">{{ $catEmoji }}</span>
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
        <div class="empty-icon">{{ $catEmoji }}</div>
        <p style="font-weight:600">មិនទាន់មានទំនិញ {{ $catLabel }}</p>
        <a href="{{ route('stock.materials.create') }}?category={{ $category }}" class="btn btn-primary btn-sm mt-2">
          <i class="bi bi-plus-lg"></i> បន្ថែម Material
        </a>
      </div>
    </div>
  </div>
@else

<form action="{{ route('stock.movements.daily-store') }}" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="category" value="{{ $category }}">

  <div class="row g-4">
    {{-- LEFT: qty inputs --}}
    <div class="col-lg-8">

      {{-- Reporter info bar --}}
      <div class="panel mb-4">
        <div class="panel-body" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;padding:1rem">
          <div style="flex:1;min-width:160px">
            <label class="form-label" style="font-size:.8rem">📅 ថ្ងៃខែ</label>
            <input type="date" name="update_date" class="form-control form-control-sm"
                   value="{{ now()->format('Y-m-d') }}" style="font-family:var(--font-latin)" required>
          </div>
          <div style="flex:2;min-width:160px">
            <label class="form-label" style="font-size:.8rem">👤 ឈ្មោះអ្នករាយការណ៍</label>
            <input type="text" name="performed_by" class="form-control form-control-sm"
                   placeholder="ឧ. លោក សុខ" value="{{ old('performed_by') }}">
          </div>
        </div>
      </div>

      {{-- Materials grouped by sub_type --}}
      @php
        $grouped = $materials->groupBy('sub_type');
      @endphp

      @foreach($grouped as $subType => $items)
        <div class="panel mb-3">
          <div class="panel-header" style="padding:.65rem 1rem">
            <div class="ph-title">
              <div class="ph-icon" style="{{ $catStyle }};width:30px;height:30px;font-size:.9rem">{{ $catEmoji }}</div>
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
                  <div style="font-weight:700;font-size:.9rem" data-name="{{ $m->name }}" data-name-km="{{ $m->name_km }}">
                    {{ $m->name }}
                    @if($m->name_km)
                      <span style="display:block;font-size:.78rem;font-weight:500;color:var(--text-secondary);font-family:var(--font-khmer)">{{ $m->name_km }}</span>
                    @endif
                  </div>
                  @if($isLow && $stock > 0)
                    <div style="font-size:.7rem;color:#d97706">⚠️ Stock ទាប (Min: {{ number_format($m->min_stock,0) }})</div>
                  @elseif($stock <= 0)
                    <div style="font-size:.7rem;color:#dc2626">🔴 អស់ Stock</div>
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

                {{-- New qty input --}}
                <div style="min-width:110px">
                  <div style="font-size:.68rem;color:var(--text-muted);margin-bottom:.1rem;text-align:center">Stock ថ្មី ({{ $m->unit }})</div>
                  <input type="number"
                         name="items[{{ $loop->parent->index * 100 + $loop->index }}][current_stock]"
                         class="form-control qty-input"
                         value="{{ number_format($stock, 0, '.', '') }}"
                         min="0" step="1"
                         data-original="{{ number_format($stock, 0, '.', '') }}"
                         style="font-family:var(--font-latin);font-weight:800;font-size:1.1rem;text-align:center;width:110px"
                         required>
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
              @if($defaultGroup)
                <div style="background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);
                            padding:.7rem .9rem;margin-bottom:.75rem;font-size:.82rem;color:#14532d">
                  <i class="bi bi-check-circle-fill me-1"></i>
                  <strong>Send to:</strong> {{ $defaultGroup->displayLabel() }}
                </div>
                <input type="hidden" name="chat_id" id="dailyChatId" value="{{ $defaultGroup->chat_id }}">
                <input type="hidden" name="message_thread_id" id="dailyThreadId" value="{{ $defaultGroup->message_thread_id ?? '' }}">
              @else
                <input type="hidden" name="chat_id" id="dailyChatId" value="{{ $telegramGroups->first()?->chat_id }}">
                <input type="hidden" name="message_thread_id" id="dailyThreadId" value="{{ $telegramGroups->first()?->message_thread_id ?? '' }}">
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
                        {{ isset($defaultGroup) && $g->id === $defaultGroup->id ? 'selected' : '' }}>
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
                  ចុចដើម្បីជ្រើស ឬ Drag &amp; Drop រូបភាព<br>
                  <span style="font-size:.7rem">JPG / PNG / WebP · Max 10MB each · Max 10 photos</span>
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

@endsection

@push('scripts')
<script>
const category = '{{ $category }}';
const catLabel = @json($catLabel);
const catEmoji = '{{ $catEmoji }}';
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

// ── Highlight changed rows ─────────────────────────────────
document.querySelectorAll('.qty-input').forEach(inp => {
  inp.addEventListener('input', () => {
    const orig = parseFloat(inp.dataset.original) || 0;
    const now  = parseFloat(inp.value) || 0;
    if (Math.abs(now - orig) >= 1) {
      inp.style.background  = '#fffbeb';
      inp.style.borderColor = '#fbbf24';
    } else {
      inp.style.background  = '';
      inp.style.borderColor = '';
    }
    updatePreview();
  });
});

// "Save only" — uncheck telegram before submit
document.getElementById('saveOnlyBtn')?.addEventListener('click', () => {
  document.getElementById('sendToggle').checked = false;
});

// Toggle Telegram section visibility
document.getElementById('sendToggle')?.addEventListener('change', e => {
  const opts = document.getElementById('telegramOptions');
  opts.style.opacity       = e.target.checked ? '1' : '.4';
  opts.style.pointerEvents = e.target.checked ? '' : 'none';
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

  document.querySelectorAll('.qty-input').forEach(inp => {
    const nameEl = inp.closest('div[style*="display:flex"]')?.querySelector('[data-name]');
    const name   = nameEl?.dataset?.name    || '';
    const nameKm = nameEl?.dataset?.nameKm  || '';
    const qty    = parseInt(inp.value) || 0;
    // Get unit from the label above the input
    const unitLabel = inp.previousElementSibling?.textContent?.match(/\(([^)]+)\)/)?.[1] || '';
    const display = nameKm ? `${name} — ${nameKm}` : name;
    lines.push(`- ${display} : ${qty.toLocaleString()}${unitLabel ? ' ' + unitLabel : ''}`);
  });

  if (by) { lines.push(''); lines.push(`👤 ${by}`); }
  lines.push(catTag);

  document.getElementById('telegramPreview').textContent = lines.join('\n');
}

// init preview
updatePreview();
document.querySelector('[name=update_date]')?.addEventListener('change', updatePreview);
document.querySelector('[name=performed_by]')?.addEventListener('input', updatePreview);

// ── Multi-image handling ───────────────────────────────────
let selectedFiles = new DataTransfer();

function previewImages(files) {
  for (const f of files) {
    if (selectedFiles.files.length >= 10) break;
    selectedFiles.items.add(f);
  }
  document.getElementById('imageInput').files = selectedFiles.files;
  renderPreviews();
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
    wrap.style  = 'position:relative;display:inline-block';
    wrap.innerHTML = `
      <img src="${url}" style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:2px solid var(--border)">
      <button type="button" onclick="removeImage(${i})"
        style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;
               background:#dc2626;color:#fff;border:none;font-size:.65rem;line-height:1;
               display:flex;align-items:center;justify-content:center;cursor:pointer">✕</button>`;
    container.appendChild(wrap);
  });
  // Update count badge on drop zone
  const count = selectedFiles.files.length;
  document.getElementById('imageDropZone').style.borderColor = count > 0 ? 'var(--primary)' : 'var(--border)';
}
</script>
@endpush
