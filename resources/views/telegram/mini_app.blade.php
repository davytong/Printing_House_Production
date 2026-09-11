@extends('layouts.app')
@section('title', 'Stock Out Consumable — PrintTracker Pro')
@section('page-title', 'Stock Out Consumable')

@section('content')
<!-- Include Telegram WebApp JS SDK, FontAwesome 6.5 & Inter/Kantumruy Fonts -->
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  /* ── Modern Professional Outline Design System ── */
  .stock-outline-wrapper {
    max-width: 1050px;
    margin: 0 auto;
    padding-bottom: 120px !important;
    font-family: 'Inter', 'Kantumruy Pro', var(--font-kh), sans-serif;
    color: #1e293b;
  }

  /* Professional Header Card */
  .outline-header-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 0.75rem 1.1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    margin-bottom: 1rem;
  }

  /* Mode Switcher Outline Pills */
  .outline-mode-bar {
    display: flex;
    gap: 0.3rem;
    background: #f8fafc;
    padding: 0.25rem;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
  }

  .outline-mode-bar .btn-mode-outline {
    padding: 0.35rem 0.75rem;
    border-radius: 7px;
    border: 1px solid transparent;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.15s ease;
  }

  .outline-mode-bar .btn-mode-outline.active {
    background: #ffffff;
    border-color: #2563eb;
    color: #2563eb;
    font-weight: 700;
    box-shadow: 0 1px 4px rgba(37, 99, 235, 0.12);
  }

  /* Category Filter Chips Outline */
  .chips-outline-scroll {
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    padding-bottom: 0.35rem;
    margin-bottom: 1rem;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
  }
  .chips-outline-scroll::-webkit-scrollbar { display: none; }

  .outline-chip {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.82rem;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    gap: 0.35rem;
  }

  .outline-chip:hover {
    border-color: #2563eb;
    color: #2563eb;
  }

  .outline-chip.active {
    background: #eff6ff;
    border-color: #2563eb;
    color: #2563eb;
    font-weight: 700;
  }

  /* Outline Material Cards Grid */
  .materials-outline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(225px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  @media (max-width: 576px) {
    .materials-outline-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 0.5rem;
    }
  }

  .outline-item-card {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    padding: 0.75rem 0.85rem;
    cursor: pointer;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  .outline-item-card:hover {
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
  }

  .outline-item-card.selected {
    border-color: #2563eb;
    background: #f0f9ff;
    box-shadow: 0 0 0 2px #2563eb;
  }

  /* Outline Icon Boxes */
  .outline-icon-box {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    border: 1.5px solid #cbd5e1;
  }

  .icon-cyan { border-color: #0284c7; background: #f0f9ff; color: #0284c7; }
  .icon-magenta { border-color: #d946ef; background: #fdf4ff; color: #d946ef; }
  .icon-yellow { border-color: #d97706; background: #fffbeb; color: #d97706; }
  .icon-black { border-color: #334155; background: #f8fafc; color: #334155; }
  .icon-sponge { border-color: #ea580c; background: #fff7ed; color: #ea580c; }
  .icon-liquid { border-color: #0d9488; background: #f0fdf4; color: #0d9488; }
  .icon-paper { border-color: #2563eb; background: #eff6ff; color: #2563eb; }
  .icon-film { border-color: #7c3aed; background: #f5f3ff; color: #7c3aed; }

  /* Outline Status Pills */
  .outline-status-pill {
    padding: 0.25rem 0.55rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.78rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
    border: 1px solid #cbd5e1;
  }

  .pill-ok-green { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
  .pill-warn-amber { background: #fffbeb; color: #b45309; border-color: #fde68a; }
  .pill-empty-red { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

  /* Outline Stock Out Form Sheet */
  .outline-form-sheet {
    background: #ffffff;
    border: 2px solid #2563eb;
    border-radius: 16px;
    padding: 1.1rem;
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.12);
    margin-top: 0.85rem;
  }

  .outline-preset-chip {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .outline-preset-chip:hover {
    border-color: #2563eb;
    color: #2563eb;
  }

  .outline-stepper-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    margin: 0.85rem 0;
  }

  .btn-stepper-outline {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    border: 2px solid #2563eb;
    color: #2563eb;
    font-size: 1.5rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .btn-stepper-outline:active {
    background: #2563eb;
    color: #ffffff;
    transform: scale(0.92);
  }

  .qty-input-outline {
    width: 90px;
    height: 44px;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    border-radius: 12px;
    color: #0f172a;
    font-size: 1.5rem;
    font-weight: 800;
    text-align: center;
  }

  .reasons-outline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
    gap: 0.45rem;
    margin-bottom: 1rem;
  }

  .reason-outline-btn {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    padding: 0.55rem 0.3rem;
    border-radius: 10px;
    text-align: center;
    font-weight: 600;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .reason-outline-btn:hover { border-color: #2563eb; color: #2563eb; }

  .reason-outline-btn.selected {
    background: #eff6ff;
    border-color: #2563eb;
    color: #2563eb;
    font-weight: 700;
  }

  .btn-confirm-prof {
    width: 100%;
    padding: 0.85rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    border: none;
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: background 0.15s ease;
  }

  .btn-confirm-prof:hover { background: #1d4ed8; }
  .btn-confirm-prof:active { transform: scale(0.98); }
</style>

<div class="stock-outline-wrapper">

  <!-- Professional Header Card -->
  <div class="outline-header-card d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-box-open text-primary fs-5"></i>
      <div>
        <span class="fw-bold fs-6 text-dark m-0">ដកស្តុក Consumable</span>
        <span class="text-muted small ms-2">Stock Out Warehouse</span>
      </div>
    </div>

    <!-- User Badge & Mode Switcher -->
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-light text-dark border border-secondary-subtle px-2.5 py-1 rounded-pill fw-bold text-xs" id="headerUserBadge">
        👤 {{ session('user_name', 'User') }}
      </span>

      <div class="outline-mode-bar">
        <button class="btn-mode-outline active" onclick="switchViewMode('visual', this)" title="Visual Grid">
          <i class="fa-solid fa-border-all"></i>
          <span>រូបភាព</span>
        </button>
        <button class="btn-mode-outline" onclick="switchViewMode('table', this)" title="Table View">
          <i class="fa-solid fa-table-list"></i>
          <span>តារាង</span>
        </button>
        <button class="btn-mode-outline" onclick="switchViewMode('history', this)" title="Activity Log">
          <i class="fa-solid fa-clock-rotate-left"></i>
          <span>ប្រវត្តិ</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ── MODE 1: VISUAL OUTLINE TOUCH VIEW ── -->
  <div id="modeVisual">

    <!-- Category Filter Chips -->
    <div class="chips-outline-scroll">
      <div class="outline-chip" onclick="filterCat('all', this)"><i class="fa-solid fa-layer-group text-warning me-1"></i> ទាំងអស់ (All)</div>
      <div class="outline-chip active" id="chipConsumable" onclick="filterCat('consumable', this)"><i class="fa-solid fa-droplet text-info me-1"></i> Consumables</div>
      <div class="outline-chip" onclick="filterCat('paper', this)"><i class="fa-solid fa-file-lines text-primary me-1"></i> ក្រដាស (Paper)</div>
      <div class="outline-chip" onclick="filterCat('film', this)"><i class="fa-solid fa-film text-warning me-1"></i> Film / ស្គុត</div>
      <div class="outline-chip" onclick="filterCat('offset', this)"><i class="fa-solid fa-print text-success me-1"></i> Offset</div>
    </div>

    <!-- Search Input -->
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
      <span class="fw-bold text-secondary text-sm">
        <i class="bi bi-1-circle-fill text-primary me-1"></i> ជ្រើសរើសទំនិញ (Select Item):
      </span>
      <div style="min-width: 220px;">
        <input type="text" id="searchInput" class="form-control form-control-sm border-secondary-subtle rounded-pill px-3 py-1" placeholder="🔍 ស្វែងរកទំនិញ..." oninput="searchItems()">
      </div>
    </div>

    <!-- Outline Material Cards Grid -->
    <div class="materials-outline-grid" id="itemsContainer">
      @foreach($materials as $m)
        @php
          $stock = $m->calculated_stock;
          $badgeClass = $stock <= 0 ? 'pill-empty-red' : ($m->isLowStock($stock) ? 'pill-warn-amber' : 'pill-ok-green');
          $statusDot  = $stock <= 0 ? '⚫' : ($m->isLowStock($stock) ? '🟡' : '🟢');

          $iconStr = trim($m->icon ?? '');
          $nameLower = strtolower($m->name . ' ' . ($m->name_km ?? ''));
          $iconClass = 'icon-paper';
          $iconMarkup = '';

          if (str_contains($nameLower, 'cyan')) {
              $iconClass = 'icon-cyan';
              $iconMarkup = '<i class="fa-solid fa-droplet"></i>';
          } elseif (str_contains($nameLower, 'magenta')) {
              $iconClass = 'icon-magenta';
              $iconMarkup = '<i class="fa-solid fa-droplet"></i>';
          } elseif (str_contains($nameLower, 'yellow')) {
              $iconClass = 'icon-yellow';
              $iconMarkup = '<i class="fa-solid fa-droplet"></i>';
          } elseif (str_contains($nameLower, 'black')) {
              $iconClass = 'icon-black';
              $iconMarkup = '<i class="fa-solid fa-droplet"></i>';
          } elseif (str_contains($nameLower, 'sponge') || str_contains($nameLower, 'ប្រឡះ') || str_contains($nameLower, 'ស្បៃ') || str_contains($nameLower, 'អេប៉ុង')) {
              $iconClass = 'icon-sponge';
              $iconMarkup = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 11c0-2.5-2.5-4-5-2-2.5-2-6-1.5-7 1-1 2.5 0 5.5 2.5 6.5s5.5.5 6.5-1.5c1-2 3-1.5 3-4z"/><circle cx="9" cy="11" r="0.9" fill="currentColor"/><circle cx="14" cy="11" r="1.2" fill="currentColor"/><circle cx="12" cy="14" r="0.9" fill="currentColor"/><circle cx="8" cy="14" r="0.7" fill="currentColor"/><circle cx="16" cy="13" r="0.7" fill="currentColor"/></svg>';
          } elseif (str_contains($nameLower, 'liquid') || str_contains($nameLower, 'cleaner') || str_contains($nameLower, 'ទឹក')) {
              $iconClass = 'icon-liquid';
              $iconMarkup = '<i class="fa-solid fa-spray-can-sparkles"></i>';
          } elseif ($m->category === 'paper') {
              $iconClass = 'icon-paper';
              $iconMarkup = '<i class="fa-solid fa-file-lines"></i>';
          } elseif ($m->category === 'film') {
              $iconClass = 'icon-film';
              $iconMarkup = '<i class="fa-solid fa-film"></i>';
          } elseif (!empty($iconStr) && (str_contains($iconStr, 'fa-') || str_contains($iconStr, 'fa '))) {
              $iconClass = 'icon-cyan';
              $iconMarkup = '<i class="' . e($iconStr) . '"></i>';
          } elseif (!empty($iconStr)) {
              $iconClass = 'icon-cyan';
              $iconMarkup = '<span>' . e($iconStr) . '</span>';
          } else {
              $iconClass = 'icon-paper';
              $iconMarkup = '<i class="fa-solid fa-box-archive"></i>';
          }
        @endphp

        <div class="outline-item-card item-row"
             data-category="{{ $m->category }}"
             data-id="{{ $m->id }}"
             data-name="{{ $m->name_km ?: $m->name }}"
             data-rawname="{{ strtolower($m->name . ' ' . $m->name_km) }}"
             data-unit="{{ $m->unit }}"
             data-stock="{{ $stock }}"
             onclick="selectItem(this)">
          <div class="d-flex align-items-center gap-2">
            <div class="outline-icon-box {{ $iconClass }}">{!! $iconMarkup !!}</div>
            <div>
              <div class="fw-bold text-dark" style="font-size:0.85rem;line-height:1.2">{{ $m->name }}</div>
              @if($m->name_km)
                <div class="text-muted" style="font-size:0.72rem">{{ $m->name_km }}</div>
              @endif
            </div>
          </div>
          <div class="outline-status-pill {{ $badgeClass }}">
            <span>{{ $statusDot }}</span>
            <span>{{ $stock + 0 }}</span>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Step 2 & 3: Outline Form Sheet -->
    <div id="formSection" style="display: none;" class="outline-form-sheet">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-bold text-primary text-sm m-0">
          <i class="bi bi-2-circle-fill me-1"></i> ២. ព័ត៌មានដកស្តុក (Stock Out Details):
        </span>
        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2" style="font-size:0.75rem" onclick="deselectItem()">✕ ជ្រើសរើសផ្សេង</button>
      </div>

      <!-- Selected Item Summary Banner -->
      <div class="p-2 mb-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-bold text-dark fs-6" id="selectedName">Cyan Ink</div>
          <div class="text-muted" style="font-size:0.75rem">Stock បច្ចុប្បន្ន:</div>
        </div>
        <div class="text-end">
          <span class="fs-4 fw-extrabold text-success" id="selectedStock">12</span>
          <span class="text-muted" style="font-size:0.8rem" id="selectedUnit">Bottles</span>
        </div>
      </div>

      <!-- DIRECT TAKEN BY NAME INPUT -->
      <div class="mb-2">
        <label class="form-label fw-bold text-dark mb-1 d-flex align-items-center justify-content-between" style="font-size:0.82rem">
          <span><i class="fa-solid fa-user text-primary me-1"></i> ឈ្មោះអ្នកយកស្តុក (Taken By):</span>
          <span id="identityVerifiedBadge" class="badge bg-success-subtle text-success border border-success-subtle d-none" style="font-size:0.7rem"><i class="fa-solid fa-pen-to-square me-1"></i> Editable</span>
        </label>
        <input type="text" id="inputTakenByName" class="form-control form-control-sm fw-bold border-secondary-subtle py-1" placeholder="បញ្ចូលឈ្មោះអ្នកយក (e.g. Chantha...)" oninput="saveTakenByName(this.value)">
        <div id="recorderInfoBadge" class="small text-muted mt-1 d-none" style="font-size:0.75rem;">
          <i class="fa-solid fa-user-shield text-success me-1"></i> <strong>អ្នកកត់ត្រា (Telegram):</strong> <span id="recorderNameDisplay" class="fw-bold text-dark"></span>
        </div>
      </div>

      <!-- Quantity Stepper -->
      <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="form-label text-dark fw-bold m-0" style="font-size:0.82rem">ចំនួនដក (Quantity):</label>
        <div class="d-flex gap-1">
          <span class="outline-preset-chip" onclick="addQtyPreset(1)">+1</span>
          <span class="outline-preset-chip" onclick="addQtyPreset(2)">+2</span>
          <span class="outline-preset-chip" onclick="addQtyPreset(5)">+5</span>
          <span class="outline-preset-chip" onclick="addQtyPreset(10)">+10</span>
        </div>
      </div>

      <div class="outline-stepper-row">
        <button class="btn-stepper-outline" type="button" onclick="adjustQty(-1)">-</button>
        <input type="number" id="inputQty" class="qty-input-outline" value="1" min="1" step="1" oninput="updateRemainingPreview()">
        <button class="btn-stepper-outline" type="button" onclick="adjustQty(1)">+</button>
      </div>

      <div class="text-center mb-3 p-2 rounded-2 bg-light border text-primary fw-bold" style="font-size:0.85rem">
        ស្តុកនៅសល់: <span id="previewRemaining" class="fs-5 text-warning fw-bold">11</span> <span id="previewUnit">Bottles</span>
      </div>

      <!-- Reason Selector Chips -->
      <label class="form-label text-dark fw-bold mb-1" style="font-size:0.82rem">គោលបំណង (Reason):</label>
      <div class="reasons-outline-grid">
        <div class="reason-outline-btn selected" onclick="selectReason('Production', this)"><i class="fa-solid fa-industry text-primary me-1"></i> ការផលិត</div>
        <div class="reason-outline-btn" onclick="selectReason('Machine maintenance', this)"><i class="fa-solid fa-wrench text-warning me-1"></i> ថែទាំម៉ាស៊ីន</div>
        <div class="reason-outline-btn" onclick="selectReason('Cleaning', this)"><i class="fa-solid fa-broom text-info me-1"></i> សម្អាត</div>
        <div class="reason-outline-btn" onclick="selectReason('Damaged', this)"><i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> ខូចខាត</div>
        <div class="reason-outline-btn" style="grid-column: span 2;" onclick="selectReason('Other', this)"><i class="fa-solid fa-pen-to-square text-secondary me-1"></i> ផ្សេងៗ (Note)</div>
      </div>

      <div id="otherNoteBox" style="display: none;" class="mb-2">
        <input type="text" id="inputOtherNote" class="form-control form-control-sm bg-light text-dark border-secondary p-2" placeholder="បញ្ជាក់មូលហេតុបន្ថែម...">
      </div>

      <!-- Confirm Button -->
      <button class="btn-confirm-prof mt-2" type="button" onclick="openConfirmationModal()">
        <i class="fa-solid fa-circle-check fs-5 me-1"></i>
        <span>បញ្ជាក់ដកស្តុក (Confirm Stock Out)</span>
      </button>
    </div>

  </div>

  <!-- ── MODE 2: INVENTORY TABLE VIEW ── -->
  <div id="modeTable" style="display: none;">
    <div class="panel">
      <div class="panel-header py-2">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-box-seam"></i></div>
          <span class="fs-6 fw-bold">បញ្ជីវត្ថុធាតុដើម (Consumables List)</span>
          <span class="badge badge-binding">{{ $materials->count() }}</span>
        </div>
      </div>
      <div class="tbl-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Item Name</th>
              <th>Category</th>
              <th class="col-right">Current Stock</th>
              <th class="col-center">Status</th>
              <th class="col-center">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($materials as $m)
              @php $stk = $m->calculated_stock; @endphp
              <tr>
                <td class="fw-bold text-primary">{{ $m->code }}</td>
                <td>
                  <div class="fw-bold">{{ $m->name }}</div>
                  @if($m->name_km)<div class="small text-muted">{{ $m->name_km }}</div>@endif
                </td>
                <td><span class="badge badge-binding">{{ $m->categoryLabelShort() }}</span></td>
                <td class="text-end fw-bold fs-6">{{ $stk + 0 }} {{ $m->unit }}</td>
                <td class="text-center">{!! $m->stockStatusBadge($stk) !!}</td>
                <td class="text-center">
                  <button class="btn btn-primary btn-sm fw-bold px-2 py-1" style="font-size:0.78rem" onclick="quickStockOutModal({{ json_encode(['id'=>$m->id, 'name'=>$m->name_km ?: $m->name, 'stock'=>$stk, 'unit'=>$m->unit]) }})">
                    <i class="bi bi-box-arrow-up-right me-1"></i> ដកស្តុក
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── MODE 3: RECENT HISTORY LOG ── -->
  <div id="modeHistory" style="display: none;">
    <div class="panel p-3">
      <h6 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-clock-rotate-left text-warning me-1"></i> ប្រវត្តិដក/បញ្ចូល (Activity Log)</h6>
      <div id="historyListContainer">
        <div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span> Loading...</div>
      </div>
    </div>
  </div>

</div>

<!-- CONFIRMATION MODAL SHEET -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white border-0 py-2">
        <h6 class="modal-title fw-bold text-warning m-0"><i class="fa-solid fa-shield-halved me-2"></i> ផ្ទៀងផ្ទាត់ការដកស្តុក (Confirm Stock Out)</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3 bg-light">
        
        <table class="table table-bordered bg-white m-0 rounded-3 overflow-hidden" style="font-size:0.9rem">
          <tbody>
            <tr>
              <td class="text-muted fw-semibold">មុខទំនិញ (Item):</td>
              <td class="fw-bold text-dark" id="mItemName">-</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">ចំនួនដក (Qty Out):</td>
              <td class="fw-bold text-danger fs-5" id="mQtyOut">-</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">គោលបំណង (Reason):</td>
              <td class="fw-bold text-dark" id="mReason">-</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">អ្នកដក (Taken by):</td>
              <td class="fw-bold text-primary" id="mTakenBy">Davy</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">កាលបរិច្ឆេទ (Date):</td>
              <td class="text-muted" id="mDate">{{ date('j') }} {{ ['មករា','កុម្ភៈ','មីនា','មេសា','ឧសភា','មិថុនា','កក្កដា','សីហា','កញ្ញា','តុលា','វិច្ឆិកា','ធ្នូ'][(int)date('n')-1] }} {{ date('Y') }}</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">ម៉ោង (Time):</td>
              <td class="text-muted" id="mTime">{{ date('h:i A') }}</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">ស្តុកមុនដក (Before):</td>
              <td class="fw-bold text-secondary" id="mStockBefore">-</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">ស្តុកនៅសល់ (Remaining):</td>
              <td class="fw-bold text-success fs-5" id="mRemaining">-</td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">លេខប្រតិបត្តិការ (Ref):</td>
              <td class="fw-bold text-primary font-monospace" id="mRefCode">SO-{{ date('Ymd') }}-...</td>
            </tr>
          </tbody>
        </table>

      </div>
      <div class="modal-footer bg-white border-0 p-2 d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary flex-grow-1 py-2 fw-bold" data-bs-dismiss="modal">
          <i class="fa-solid fa-xmark me-1"></i> បោះបង់
        </button>
        <button type="button" class="btn btn-primary flex-grow-2 py-2 fw-bold fs-6" id="btnFinalConfirm" onclick="submitStockOut()">
          <i class="fa-solid fa-circle-check me-1"></i> បញ្ជាក់ដកស្តុក
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  let selectedItemData = null;
  let selectedReason = 'Production';

  document.addEventListener('DOMContentLoaded', () => {
    // Restore memory for Taken By name
    const savedName = localStorage.getItem('stock_out_taken_by') || "{{ session('user_name', 'User') }}";
    const nameInput = document.getElementById('inputTakenByName');
    const badge = document.getElementById('identityVerifiedBadge');
    const headerBadge = document.getElementById('headerUserBadge');
    if (nameInput) nameInput.value = savedName;

    // Pin Consumables category by default
    const chipC = document.getElementById('chipConsumable');
    if (chipC) {
      filterCat('consumable', chipC);
    }

    const tg = window.Telegram?.WebApp;
    if (tg && tg.initDataUnsafe?.user) {
      tg.ready();
      tg.expand();
      const user = tg.initDataUnsafe.user;
      const firstName = user.first_name || '';
      const lastName  = user.last_name || '';
      const fullName  = (firstName + ' ' + lastName).trim() || user.username || 'Telegram User';
      const tgIdentity = user.username ? `${fullName} (@${user.username})` : fullName;

      if (nameInput && !localStorage.getItem('stock_out_taken_by')) {
        nameInput.value = firstName || fullName;
      }
      if (badge) {
        badge.innerHTML = '<i class="fa-solid fa-user-pen me-1"></i> Can edit for others';
        badge.className = 'badge bg-success-subtle text-success border border-success-subtle';
        badge.classList.remove('d-none');
      }
      if (headerBadge) {
        headerBadge.innerHTML = `<i class="fa-solid fa-shield-check text-success me-1"></i> ${fullName}`;
        headerBadge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-bold text-xs';
      }
      const recBadge = document.getElementById('recorderInfoBadge');
      const recNameDisp = document.getElementById('recorderNameDisplay');
      if (recBadge && recNameDisp) {
        recNameDisp.innerText = tgIdentity;
        recBadge.classList.remove('d-none');
      }
    } else {
      @if(auth()->check())
        const webName = "{{ auth()->user()->name }}";
        if (nameInput && !localStorage.getItem('stock_out_taken_by')) {
          nameInput.value = webName;
        }
        if (badge) {
          badge.innerHTML = '<i class="fa-solid fa-user-check me-1"></i> Web Mode';
          badge.className = 'badge bg-info-subtle text-info border border-info-subtle';
          badge.classList.remove('d-none');
        }
        if (headerBadge) {
          headerBadge.innerHTML = `<i class="fa-solid fa-user-check text-primary me-1"></i> ${webName}`;
        }
      @endif
    }
  });

  function saveTakenByName(val) {
    if (val.trim()) {
      localStorage.setItem('stock_out_taken_by', val.trim());
    }
  }

  function setQuickName(name) {
    const nameInput = document.getElementById('inputTakenByName');
    if (nameInput) nameInput.value = name;
    saveTakenByName(name);
  }

  function switchViewMode(mode, el) {
    document.querySelectorAll('.btn-mode-outline').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    document.getElementById('modeVisual').style.display = 'none';
    document.getElementById('modeTable').style.display = 'none';
    document.getElementById('modeHistory').style.display = 'none';

    if (mode === 'visual') {
      document.getElementById('modeVisual').style.display = 'block';
    } else if (mode === 'table') {
      document.getElementById('modeTable').style.display = 'block';
    } else if (mode === 'history') {
      document.getElementById('modeHistory').style.display = 'block';
      loadHistoryLog();
    }
  }

  function filterCat(cat, el) {
    document.querySelectorAll('.outline-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');

    document.querySelectorAll('.item-row').forEach(row => {
      if (cat === 'all' || row.dataset.category === cat) {
        row.style.display = 'flex';
      } else {
        row.style.display = 'none';
      }
    });
  }

  function searchItems() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('.item-row').forEach(row => {
      const text = row.dataset.rawname || '';
      if (!q || text.includes(q)) {
        row.style.display = 'flex';
      } else {
        row.style.display = 'none';
      }
    });
  }

  function selectItem(el) {
    document.querySelectorAll('.item-row').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');

    selectedItemData = {
      id: el.dataset.id,
      name: el.dataset.name,
      unit: el.dataset.unit,
      stock: parseFloat(el.dataset.stock) || 0
    };

    document.getElementById('selectedName').innerText = selectedItemData.name;
    document.getElementById('selectedStock').innerText = selectedItemData.stock;
    document.getElementById('selectedUnit').innerText = selectedItemData.unit;
    document.getElementById('previewUnit').innerText = selectedItemData.unit;

    document.getElementById('inputQty').value = 1;
    updateRemainingPreview();

    document.getElementById('formSection').style.display = 'block';
    document.getElementById('formSection').scrollIntoView({ behavior: 'smooth' });
  }

  function deselectItem() {
    document.querySelectorAll('.item-row').forEach(r => r.classList.remove('selected'));
    selectedItemData = null;
    document.getElementById('formSection').style.display = 'none';
  }

  function addQtyPreset(delta) {
    const input = document.getElementById('inputQty');
    let val = (parseFloat(input.value) || 0) + delta;
    input.value = val;
    updateRemainingPreview();
  }

  function adjustQty(delta) {
    const input = document.getElementById('inputQty');
    let val = (parseFloat(input.value) || 0) + delta;
    if (val < 1) val = 1;
    input.value = val;
    updateRemainingPreview();
  }

  function updateRemainingPreview() {
    if (!selectedItemData) return;
    const qty = parseFloat(document.getElementById('inputQty').value) || 0;
    const rem = selectedItemData.stock - qty;
    const prev = document.getElementById('previewRemaining');
    prev.innerText = rem < 0 ? 0 : rem;
    prev.className = rem < 0 ? 'fs-5 text-danger fw-bold' : 'fs-5 text-warning fw-bold';
  }

  function selectReason(reason, el) {
    document.querySelectorAll('.reason-outline-btn').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedReason = reason;

    const noteBox = document.getElementById('otherNoteBox');
    noteBox.style.display = reason === 'Other' ? 'block' : 'none';
  }

  function quickStockOutModal(mat) {
    selectedItemData = mat;
    openConfirmationModal();
  }

  function openConfirmationModal() {
    if (!selectedItemData) return;

    const takenBy = document.getElementById('inputTakenByName').value.trim() || 'Davy';
    if (!takenBy) {
      Swal.fire({
        icon: 'warning',
        title: 'សូមបញ្ចូលឈ្មោះ',
        text: 'សូមបញ្ចូលឈ្មោះអ្នកដកស្តុក (Please enter Taken By name)',
        confirmButtonColor: '#2563eb'
      }).then(() => document.getElementById('inputTakenByName').focus());
      return;
    }

    const qty = parseFloat(document.getElementById('inputQty').value) || 0;
    if (qty <= 0) {
      Swal.fire({
        icon: 'warning',
        title: 'ចំនួនមិនត្រឹមត្រូវ',
        text: 'សូមបញ្ជាក់ចំនួនដកដែលត្រឹមត្រូវ',
        confirmButtonColor: '#2563eb'
      });
      return;
    }

    if (qty > selectedItemData.stock) {
      Swal.fire({
        icon: 'error',
        title: 'ស្តុកមិនគ្រប់គ្រាន់',
        text: 'ចំនួនដកចេញលើសពីស្តុកដែលមាន!',
        confirmButtonColor: '#ef4444'
      });
      return;
    }

    let note = '';
    if (selectedReason === 'Other') {
      note = document.getElementById('inputOtherNote').value.trim();
    }

    const reasonMap = {
      'Production': 'ប្រើប្រាស់ក្នុងការផលិត',
      'Machine maintenance': 'ថែទាំម៉ាស៊ីន',
      'Cleaning': 'សម្អាត',
      'Damaged': 'ខូចខាត',
      'Other': 'ផ្សេងៗ'
    };
    let reasonKh = reasonMap[selectedReason] || selectedReason;
    if (note && selectedReason === 'Other') reasonKh += ` (${note})`;
    else if (note && selectedReason !== 'Other') reasonKh += ` - ${note}`;

    const remaining = selectedItemData.stock - qty;
    const now = new Date();
    const monthsKh = ['មករា','កុម្ភៈ','មីនា','មេសា','ឧសភា','មិថុនា','កក្កដា','សីហា','កញ្ញា','តុលា','វិច្ឆិកា','ធ្នូ'];
    const dateKhmer = `${now.getDate()} ${monthsKh[now.getMonth()]} ${now.getFullYear()}`;
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const timeStr = `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
    const refCode = `SO-${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}-00${Math.floor(Math.random()*90)+10}`;

    document.getElementById('mItemName').innerText = selectedItemData.name;
    document.getElementById('mQtyOut').innerText = `${qty} ${selectedItemData.unit}`;
    document.getElementById('mTakenBy').innerText = takenBy;
    document.getElementById('mDate').innerText = dateKhmer;
    document.getElementById('mTime').innerText = timeStr;
    document.getElementById('mReason').innerText = reasonKh;
    document.getElementById('mStockBefore').innerText = `${selectedItemData.stock} ${selectedItemData.unit}`;
    document.getElementById('mRemaining').innerText = `${remaining} ${selectedItemData.unit}`;
    document.getElementById('mRefCode').innerText = refCode;

    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
  }

  async function submitStockOut() {
    const btn = document.getElementById('btnFinalConfirm');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

    const qty = parseFloat(document.getElementById('inputQty').value) || 1;
    const note = selectedReason === 'Other' ? document.getElementById('inputOtherNote').value.trim() : '';
    const takenBy = document.getElementById('inputTakenByName').value.trim() || 'Davy';

    saveTakenByName(takenBy);

    const tgInitData = window.Telegram?.WebApp?.initData || '';

    try {
      const res = await fetch('/api/telegram/app/stock-out', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          material_id: selectedItemData.id,
          quantity: qty,
          reason: selectedReason,
          notes: note,
          performed_by: takenBy,
          tg_init_data: tgInitData
        })
      });

      const contentType = res.headers.get('content-type');
      let data;
      if (contentType && contentType.includes('application/json')) {
        data = await res.json();
      } else {
        throw new Error(`Server error (${res.status})`);
      }

      if (data.ok) {
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
        
        Swal.fire({
          icon: 'success',
          title: 'បានដកស្តុកជោគជ័យ!',
          text: data.message || 'Stock Out Completed Successfully',
          confirmButtonColor: '#2563eb',
          timer: 2500,
          showConfirmButton: true
        });

        // Update local stock display
        const card = document.querySelector(`.outline-item-card[data-id="${selectedItemData.id}"]`);
        if (card) {
          card.dataset.stock = data.remaining;
          const badge = card.querySelector('.outline-status-pill');
          if (badge) {
            badge.innerHTML = `<span>🟢</span> <span>${data.remaining}</span>`;
          }
        }

        deselectItem();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'មានបញ្ហា!',
          text: data.message || 'Stock Out failed',
          confirmButtonColor: '#ef4444'
        });
      }
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'មានបញ្ហា!',
        text: err.message,
        confirmButtonColor: '#ef4444'
      });
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> បញ្ជាក់ដកស្តុក';
    }
  }

  async function loadHistoryLog() {
    const container = document.getElementById('historyListContainer');
    try {
      const res = await fetch('/api/telegram/app/data');
      const data = await res.json();
      if (data.ok && data.recent) {
        if (data.recent.length === 0) {
          container.innerHTML = '<div class="text-center text-muted py-3">មិនទាន់មានទិន្នន័យ</div>';
          return;
        }
        let html = '<div class="list-group list-group-flush" style="font-size:0.88rem">';
        data.recent.forEach(r => {
          html += `
            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
              <div>
                <div class="fw-bold text-dark">${r.material}</div>
                <div class="text-muted small">👤 <strong>${r.performed_by}</strong> • 🎯 ${r.reason}</div>
              </div>
              <div class="text-end">
                <div class="fw-bold text-danger">-${r.quantity} ${r.unit}</div>
                <div class="text-muted small">🕒 ${r.time}</div>
              </div>
            </div>
          `;
        });
        html += '</div>';
        container.innerHTML = html;
      }
    } catch (e) {
      container.innerHTML = '<div class="text-danger">Failed to load history</div>';
    }
  }
</script>
@endsection
