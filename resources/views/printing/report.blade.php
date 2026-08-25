@extends('layouts.app')
@section('title',        'របាយការណ៍ការបោះពុម្ព')
@section('page-title',   'របាយការណ៍')

@section('content')

@php
  $totalBooks     = $books->count();
  $totalPrinted   = $books->sum('total_printed');
  $totalTarget    = $books->sum('target_qty');
  $totalRemaining = max($totalTarget - $totalPrinted, 0);
  $overallPct     = $totalTarget > 0 ? floor($totalPrinted / $totalTarget * 100) : 0;
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
  <div class="d-flex gap-2 flex-wrap">
    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#telegramModal">
      <i class="bi bi-telegram"></i> Send Telegram
    </button>
    <a href="{{ route('printing.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> ត្រឡប់ទៅគ្រប់គ្រង
    </a>
  </div>
</div>

{{-- ════════════════════════════════════════════
     PREVIEW MODAL (Simplified)
════════════════════════════════════════════ --}}
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen p-sm-4 p-2">
    <div class="modal-content" style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
      <div class="modal-header" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: 20px 20px 0 0; padding: 1.25rem 1.5rem; color: white;">
        <h5 class="modal-title" style="font-weight: 800; letter-spacing: -0.01em;">
          <i class="bi bi-file-text me-2"></i> របាយការណ៍ប្រចាំថ្ងៃ - {{ today()->format('d/m/Y') }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          {{-- Left: Controls --}}
          <div class="col-lg-3" style="border-right:1px solid #dee2e6;padding-right:1.5rem">
            
            {{-- Instructions --}}
            <div class="alert alert-info" role="alert" style="font-size:.85rem">
              <i class="bi bi-info-circle-fill"></i> <strong>របៀបប្រើប្រាស់:</strong>
              <ol class="mb-0 mt-2" style="padding-left:1.2rem">
                <li>ជ្រើស Level (ឬទុកទាំងអស់)</li>
                <li>រង់ចាំ Preview ផ្ទុក</li>
                <li>ចុច "Copy (Mobile)" ដើម្បី copy</li>
              </ol>
            </div>
            
            {{-- Grade Filter --}}
            <div class="mb-3">
              <label class="form-label" style="font-size:.9rem;font-weight:600">
                <i class="bi bi-funnel"></i> ជ្រើស Level / Grade
              </label>
              <select id="previewGradeFilter" class="form-select form-select-lg" onchange="loadPreviewWithFilter()">
                <option value="">📚 ទាំងអស់ (All Levels)</option>
                @isset($grades)
                  @foreach($grades as $g)
                    <option value="{{ $g }}">{{ $g }}</option>
                  @endforeach
                @endisset
              </select>
            </div>
            
          </div>
          
          {{-- Right: Preview Content --}}
          <div class="col-lg-9" style="max-height:80vh;overflow-y:auto">
            <div id="previewLoading" class="text-center py-5" style="display:none">
              <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-3 text-muted" style="font-size:1.1rem">កំពុងដំណើរការ...</p>
              <small class="text-muted">សូមរង់ចាំ បង្កើតរបាយការណ៍</small>
            </div>
            
            <div id="previewContent" style="background:#ffffff;border:2px solid #e2e8f0;border-radius:12px;padding:2rem;font-family:monospace;white-space:pre-wrap;min-height:400px;font-size:.85rem;line-height:1.8;box-shadow:0 4px 6px rgba(0,0,0,0.05)">
              <!-- Preview will load here -->
            </div>
            
            {{-- Mobile Copy Textarea (hidden by default) --}}
            <div id="mobileCopyArea" style="display:none;margin-top:1rem">
              <div class="alert alert-success" role="alert">
                <i class="bi bi-phone-fill"></i> <strong>សម្រាប់ Mobile:</strong><br>
                ចុចយូរលើ textarea ខាងក្រោម → ជ្រើស "Select All" → ចុច "Copy"
              </div>
              <textarea id="mobileCopyTextarea" class="form-control" readonly style="font-family:monospace;font-size:.8rem;height:350px;white-space:pre-wrap;border:2px solid #10b981"></textarea>
              <button class="btn btn-outline-secondary btn-sm mt-2" onclick="hideCopyTextarea()">
                <i class="bi bi-x-lg"></i> បិទ
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> បិទ
        </button>
        <button type="button" class="btn btn-info" onclick="selectAllPreview()">
          <i class="bi bi-check2-square"></i> Select All
        </button>
        <button type="button" class="btn btn-success" onclick="downloadReport()">
          <i class="bi bi-download"></i> Download
        </button>
        <button type="button" class="btn btn-primary" onclick="showCopyTextarea()">
          <i class="bi bi-phone"></i> Copy (Mobile)
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════
     TELEGRAM SEND MODAL
════════════════════════════════════════════ --}}
<div class="modal fade" id="telegramModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.25); background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
      <div class="modal-header" style="background: linear-gradient(135deg, #d1fae5 0%, #ecfdf5 100%); border-radius: 20px 20px 0 0; border-bottom: 1px solid #a7f3d0; padding: 1.25rem 1.5rem;">
        <h5 class="modal-title" style="font-weight: 800; color: #065f46; letter-spacing: -0.01em;"><i class="bi bi-telegram text-success me-2" style="font-size: 1.2rem;"></i> ផ្ញើរបាយការណ៍ទៅ Telegram</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="telegramForm" action="{{ route('printing.send-telegram') }}" method="POST">
        @csrf
        <div class="modal-body" style="padding: 1.75rem;">
          <div class="mb-4">
            <label class="form-label" style="font-weight: 700; color: #334155;">ថ្ងៃរបាយការណ៍</label>
            <input type="date" name="date" class="form-control" value="{{ today()->toDateString() }}" required style="background: #f8fafc; border-radius: 10px; padding: 0.75rem;">
          </div>
          <div class="mb-4">
            <label class="form-label" style="font-weight: 700; color: #334155;">ទម្រង់</label>
            <select name="format" class="form-select" style="background: #f8fafc; border-radius: 10px; padding: 0.75rem;">
              <option value="compact">Compact (សម្រាប់ Telegram)</option>
              <option value="full">Full (លម្អិត)</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label" style="font-weight: 700; color: #334155;">ជ្រើសរើស Level (កម្រិត)</label>
            <select name="grade" class="form-select" style="background: #f8fafc; border-radius: 10px; padding: 0.75rem;">
              <option value="">— គ្រប់ Level (All Levels) —</option>
              @foreach($grades as $g)
                <option value="{{ $g }}">{{ $g }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label" style="font-weight: 700; color: #334155;">ផ្ញើទៅក្រុម</label>
            <select name="group_id" class="form-select" style="background: #f8fafc; border-radius: 10px; padding: 0.75rem;">
              <option value="">ផ្ញើទៅគ្រប់ក្រុម (Active)</option>
              @foreach($telegramGroups as $group)
                <option value="{{ $group->id }}">{{ $group->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="alert alert-info" style="font-size:.85rem">
            <i class="bi bi-info-circle"></i> របាយការណ៍នឹងត្រូវបានផ្ញើដោយស្វ័យប្រវត្តិទៅ Telegram group ដែលបានជ្រើសរើស
          </div>
        </div>
        <div class="modal-footer d-flex flex-column flex-sm-row justify-content-end gap-2 p-3">
          <button type="submit" class="btn btn-success px-4 py-2.5 fw-bold order-1 order-sm-2 w-100 w-sm-auto" id="telegramSendBtn">
            <span class="tg-btn-label"><i class="bi bi-send-fill me-1"></i> ផ្ញើឥឡូវ (Send Now)</span>
            <span class="tg-btn-loading" style="display:none">
              <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              កំពុងផ្ញើ...
            </span>
          </button>
          <button type="button" class="btn btn-outline-secondary px-4 py-2.5 order-2 order-sm-1 w-100 w-sm-auto" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> បោះបង់ (Cancel)
          </button>
        </div>
      </form>
    </div>
  </div>
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
            <div class="prog-fill" style="width:{{ $item['pct'] }}%;background:{{ $item['color'] }} !important;border-radius:999px;"></div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

</div>

{{-- ════════════════════════════════════════════
     TELEGRAM SEND — upgraded with grade filter + caption
════════════════════════════════════════════ --}}
@php
  $grades = $books->pluck('grade')->filter()->unique()->sort()->values();
@endphp

<div class="panel mb-4">
  <div class="panel-header">
    <div class="ph-title">
      <div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-send-fill"></i></div>
      <span>ផ្ញើរបាយការណ៍ទៅ Telegram</span>
    </div>
    {{-- Live preview badge --}}
    <span id="previewBadge"
          style="font-family:var(--font-latin);font-size:.72rem;background:#eff6ff;
                 color:#1d4ed8;padding:.2em .65em;border-radius:999px;font-weight:700;
                 border:1px solid #bfdbfe">
      <i class="bi bi-eye me-1"></i><span id="previewCount">0</span> ចំណង
    </span>
  </div>

  <div class="panel-body">
    @if($telegramGroups->isEmpty())
      <div class="alert-info-soft">
        <i class="bi bi-exclamation-circle-fill" style="font-size:1.1rem;flex-shrink:0;margin-top:.1rem"></i>
        <div>
          <strong>មិនទាន់មានក្រុម Telegram ។</strong>
          <span> សូម​បន្ថែម Bot ហើយ Poll ឬ Set Webhook ។
          <a href="{{ route('telegram.setup') }}" style="color:var(--primary)">រៀបចំ Bot →</a>
          </span>
        </div>
      </div>
    @else

    <div class="row g-4">

      {{-- ── Column 1: Destination + Caption ── --}}
      <div class="col-lg-5 d-flex flex-column gap-3">

        {{-- Group selector --}}
        <div>
          <label class="form-label">ក្រុម Telegram *</label>
          <select id="telegramGroup" class="form-select">
            <option value="">— ជ្រើសរើសក្រុម —</option>
            @foreach($telegramGroups as $group)
              <option value="{{ $group->chat_id }}|{{ $group->message_thread_id ?? '' }}">
                📌 {{ $group->displayLabel() }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Custom caption --}}
        <div>
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0">Caption / ចំណងជើង</label>
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-outline-success active" id="btnCaptionSummary" style="font-size:.7rem;padding:.15rem .5rem">📊 Summary</button>
              <button type="button" class="btn btn-outline-success" id="btnCaptionFull" style="font-size:.7rem;padding:.15rem .5rem">📜 Full Details</button>
            </div>
          </div>
          @php
$months = [
    1 => 'មករា', 'កុម្ភៈ', 'មីនា', 'មេសា',
    'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា',
    'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'
];

$day = now()->format('d');
$month = $months[(int) now()->format('m')];
$year = now()->format('Y');
$time = now()->format('H:i');
@endphp

<textarea id="telegramCaption" class="form-control" rows="12"
          maxlength="4096"
          style="font-size:.84rem;resize:vertical;line-height:1.7">📄 សូមគោរពរាយការណ៍
សូមគោរពជម្រាបជូន ឯកឧត្តមបណ្ឌិត ឯកឧត្តម លោកជំទាវ និងសមាជិកក្រុមការងារ
📅 ថ្ងៃទី {{ $day }} ខែ{{ $month }} ឆ្នាំ {{ $year }}

ក្រុមការងារខ្ញុំ សូមគោរពរាយការណ៍អំពីស្ថានភាពការងារបោះពុម្ពសៀវភៅ ដូចខាងក្រោម៖

━━━━━━━━━━━━━━━━━━
 【បូកសរុបការងារបោះពុម្ព】
━━━━━━━━━━━━━━━━━━
សម្រេចបានសរុបទាំងអស់ថ្ងៃនេះ៖ {{ number_format($todayTotal) }} ក្បាល
សរុបការងារបោះពុម្ពរួច៖ {{ number_format($totalPrinted) }} ក្បាល
នៅខ្វះសរុប៖ {{ number_format($totalRemaining) }} ក្បាល

សូមគោរពអរគុណ 🙏</textarea>
          <div class="d-flex justify-content-between align-items-center mt-1">
            <span style="font-size:.72rem;color:var(--text-muted)">
              <i class="bi bi-info-circle me-1"></i> ជ្រើសរើសប្រភេទ Caption ខាងលើ
            </span>
            <span style="font-size:.72rem;font-weight:600;color:var(--text-muted);font-family:var(--font-latin)" id="captionCount">0 / 4096</span>
          </div>

          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="sendFullTextAlso" checked>
            <label class="form-check-label" for="sendFullTextAlso" style="font-size:.75rem;font-weight:600;color:#334155">
              ⚡ ផ្ញើសារអត្ថបទលម្អិតបន្ថែមជាសារទី ២ (Send Details as 2nd Message)
            </label>
          </div>
        </div>

        {{-- Send buttons --}}
        <div class="d-flex gap-2 mt-auto pt-2">
          <button id="sendTelegramBtn" class="btn btn-success btn-lg flex-grow-1" style="font-size:.88rem">
            <i class="bi bi-image me-1"></i> ផ្ញើរូបភាព + Caption
          </button>
          <button id="sendTextBtn" class="btn btn-outline-success btn-lg" style="font-size:.88rem" title="ផ្ញើតែអត្ថបទលម្អិត">
            <i class="bi bi-chat-text-fill me-1"></i> ផ្ញើអត្ថបទ
          </button>
        </div>

        <p style="font-size:.72rem;color:var(--text-muted);margin:0;text-align:center">
          <i class="bi bi-shield-check me-1"></i> ផ្ញើទៅ Telegram Topic/Group ដោយសុវត្ថិភាព
        </p>

      </div>

      {{-- ── Column 2: Grade / Level selector ── --}}
      <div class="col-lg-7">
        <label class="form-label d-flex justify-content-between align-items-center">
          <span>ជ្រើសសៀវភៅ / Level ដែលចង់ផ្ញើ</span>
          <div style="display:flex;gap:.4rem">
            <button type="button" id="selectAllGrades" class="btn btn-ghost btn-sm"
                    style="font-size:.72rem;padding:.2rem .6rem;color:var(--primary)">
              ជ្រើសទាំងអស់
            </button>
            <button type="button" id="clearAllGrades" class="btn btn-ghost btn-sm"
                    style="font-size:.72rem;padding:.2rem .6rem;color:var(--danger)">
              Clear
            </button>
          </div>
        </label>

        {{-- Grade checkboxes grid --}}
        <div id="gradeCheckboxes"
             style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));
                    gap:.5rem;padding:.85rem;background:var(--surface-2);
                    border-radius:var(--radius);border:1px solid var(--border)">
          @if($grades->isEmpty())
            <p style="font-size:.82rem;color:var(--text-muted);margin:0;grid-column:1/-1">
              មិនទាន់មាន Grade/Level ក្នុង Database
            </p>
          @else
            {{-- "All books" option --}}
            <label class="grade-chip" style="cursor:pointer">
              <input type="checkbox" class="grade-cb" value="__all__" checked
                     style="display:none">
              <div class="chip-inner" style="padding:.45rem .75rem;border-radius:var(--radius-sm);
                           border:2px solid var(--primary);background:#eff6ff;color:var(--primary);
                           font-size:.8rem;font-weight:700;text-align:center;
                           transition:all var(--ease);user-select:none">
                <i class="bi bi-check2-all me-1"></i>ទាំងអស់
              </div>
            </label>
            @foreach($grades as $g)
              <label class="grade-chip" style="cursor:pointer">
                <input type="checkbox" class="grade-cb" value="{{ $g }}" checked
                       style="display:none">
                <div class="chip-inner" style="padding:.45rem .75rem;border-radius:var(--radius-sm);
                             border:2px solid var(--border-dark);background:var(--surface);
                             font-size:.8rem;font-weight:600;text-align:center;
                             transition:all var(--ease);user-select:none">
                  {{ $g }}
                </div>
              </label>
            @endforeach
          @endif
        </div>

        {{-- Live preview of books to be sent --}}
        <div style="margin-top:.75rem;max-height:350px;overflow-y:auto;
                    background:var(--surface-2);border-radius:var(--radius);
                    border:1px solid var(--border)" id="previewList">
          {{-- Filled by JS --}}
        </div>

      </div>
    </div>
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
            $pct       = $book->target_qty > 0 ? floor($book->total_printed / $book->target_qty * 100) : 0;
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

{{-- ── Telegram snapshot — Detail first, Level bars at bottom ── --}}
<div id="telegramReport" aria-hidden="true" style="
    position:fixed; left:-9999px; top:0; width:700px;
    padding:0; background:#ffffff;
    font-family:'Hanuman','Poppins',sans-serif;
    border-radius:12px; color:#0f172a; overflow:hidden;
    border:1px solid #e2e8f0;">

  {{-- HEADER --}}
  <div style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:14px 18px;color:#fff">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div>
        <div style="font-size:13px;font-weight:700;font-family:'Hanuman',sans-serif">🖨️ របាយការណ៍ការបោះពុម្ព</div>
        <div style="font-size:9px;opacity:.85;font-family:'Poppins',sans-serif;margin-top:2px">{{ today()->format('d/m/Y H:i') }}</div>
      </div>
      <div style="text-align:right">
        <div style="font-size:7px;text-transform:uppercase;letter-spacing:.08em;opacity:.8">TODAY</div>
        <div id="snapTodayTotal" style="font-size:20px;font-weight:800;font-family:'Poppins',sans-serif">0</div>
      </div>
    </div>
  </div>

  {{-- KPI STATS --}}
  <div style="display:grid;grid-template-columns:repeat(4,1fr);border-bottom:1px solid #e2e8f0">
    <div style="padding:8px;text-align:center;border-right:1px solid #e2e8f0">
      <div style="font-size:7px;color:#64748b;font-family:'Hanuman',sans-serif">គោលដៅ</div>
      <div id="snapTarget" style="font-size:15px;font-weight:800;color:#1e293b;font-family:'Poppins',sans-serif">0</div>
    </div>
    <div style="padding:8px;text-align:center;border-right:1px solid #e2e8f0">
      <div style="font-size:7px;color:#64748b;font-family:'Hanuman',sans-serif">បោះពុម្ព</div>
      <div id="snapPrinted" style="font-size:15px;font-weight:800;color:#059669;font-family:'Poppins',sans-serif">0</div>
    </div>
    <div style="padding:8px;text-align:center;border-right:1px solid #e2e8f0">
      <div style="font-size:7px;color:#64748b;font-family:'Hanuman',sans-serif">នៅសល់</div>
      <div id="snapRemaining" style="font-size:15px;font-weight:800;color:#d97706;font-family:'Poppins',sans-serif">0</div>
    </div>
    <div style="padding:8px;text-align:center">
      <div style="font-size:7px;color:#64748b;font-family:'Hanuman',sans-serif">ដំណើរការ</div>
      <div id="snapPct" style="font-size:15px;font-weight:800;color:#4f46e5;font-family:'Poppins',sans-serif">0%</div>
    </div>
  </div>

  {{-- DETAIL TABLE (FIRST — shows specific book quantities) --}}
  <div style="padding:0">
    <div id="snapDetailHeader" style="padding:6px 14px;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:9px;font-weight:700;color:#475569;font-family:'Hanuman',sans-serif">📋 ព័ត៌មានលម្អិត</div>
    <table style="width:100%;border-collapse:collapse;font-size:9px">
      <thead>
        <tr style="background:#f1f5f9">
          <th style="padding:4px 8px;text-align:left;font-family:'Hanuman',sans-serif;font-size:9px;color:#475569">ឈ្មោះ</th>
          <th style="padding:4px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:7px;color:#475569">TARGET</th>
          <th style="padding:4px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:7px;color:#475569">PRINTED</th>
          <th style="padding:4px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:7px;color:#7c3aed">TODAY</th>
          <th style="padding:4px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:7px;color:#475569">LEFT</th>
          <th style="padding:4px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:7px;color:#475569">%</th>
        </tr>
      </thead>
      <tbody id="snapTableBody"></tbody>
    </table>
  </div>

  {{-- LEVEL PROGRESS BARS (BOTTOM — visual only, no qty confusion) --}}
  <div style="padding:10px 14px;border-top:1px solid #e2e8f0;background:#f8fafc">
    <div style="font-size:9px;font-weight:700;color:#1d4ed8;margin-bottom:6px;font-family:'Hanuman',sans-serif">📊 ស្ថានភាពតាម Level</div>
    <div id="snapLevelBars"></div>
  </div>

  {{-- FOOTER --}}
  <div id="snapFooter" style="padding:8px 14px;background:#f0fdf4;border-top:1px solid #e2e8f0;font-size:8px;color:#065f46"></div>
</div>
</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
@endpush

@push('scripts')
<script>
// ── All books data from PHP ────────────────────────────────────────
@php
  $allBooksJson = $books->map(fn($b) => [
      'id'             => $b->id,
      'title'          => $b->title,
      'grade'          => $b->grade ?? '',
      'category'       => $b->category,
      'target_qty'     => $b->target_qty,
      'total_printed'  => $b->total_printed,
      'today_qty'      => (int) ($todayPrints[$b->id] ?? 0),
  ])->values();
@endphp
const ALL_BOOKS = {!! json_encode($allBooksJson) !!};
const TODAY_TOTAL = {{ $todayTotal }};

(function () {

  // ── Grade chip toggle ─────────────────────────────
  const chips       = document.querySelectorAll('.grade-cb');
  const allChip     = document.querySelector('.grade-cb[value="__all__"]');
  const captionEl   = document.getElementById('telegramCaption');
  const captionCnt  = document.getElementById('captionCount');
  const previewList = document.getElementById('previewList');
  const previewCnt  = document.getElementById('previewCount');

  function selectedGrades() {
    const vals = [...chips]
      .filter(c => c.checked && c.value !== '__all__')
      .map(c => c.value);
    // if none unchecked non-all → means "all" chip selected or nothing
    return vals;
  }

  function filteredBooks() {
    const grades = selectedGrades();
    const allSelected = allChip?.checked;
    if (allSelected || grades.length === 0) return ALL_BOOKS;
    return ALL_BOOKS.filter(b => grades.includes(b.grade));
  }

  function updateChipStyle(chip) {
    const inner = chip.closest('.grade-chip')?.querySelector('.chip-inner');
    if (!inner) return;
    if (chip.checked) {
      inner.style.borderColor  = 'var(--primary)';
      inner.style.background   = '#eff6ff';
      inner.style.color        = 'var(--primary)';
    } else {
      inner.style.borderColor  = 'var(--border-dark)';
      inner.style.background   = 'var(--surface)';
      inner.style.color        = 'var(--text-muted)';
    }
  }

  function updatePreview() {
    if (!previewList || !previewCnt) return;

    const books = filteredBooks();

    // Preview badge
    previewCnt.textContent = books.length;

    // Preview list — show ALL books with individual progress
    if (!books.length) {
      previewList.innerHTML = '<p style="padding:.75rem 1rem;font-size:.8rem;color:var(--text-muted);margin:0">មិនមានសៀវភៅ</p>';
      return;
    }

    // Group by grade for display
    const byGrade = {};
    books.forEach(b => {
      const g = b.grade || '(no grade)';
      if (!byGrade[g]) byGrade[g] = [];
      byGrade[g].push(b);
    });

    let html = '';
    for (const [grade, gbooks] of Object.entries(byGrade)) {
      const gPrinted = gbooks.reduce((s, b) => s + b.total_printed, 0);
      const gTarget  = gbooks.reduce((s, b) => s + b.target_qty, 0);
      const gPct     = gTarget > 0 ? Math.min(Math.floor(gPrinted / gTarget * 100), 100) : 0;
      const gToday   = gbooks.reduce((s, b) => s + (b.today_qty || 0), 0);

      // Grade header
      html += `
        <div style="padding:.5rem .85rem;background:#f1f5f9;border-bottom:1px solid var(--border);
                    display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:1">
          <span style="font-size:.78rem;font-weight:700;color:var(--primary)">${grade}</span>
          <div style="display:flex;align-items:center;gap:.6rem">
            <span style="font-family:'Poppins',sans-serif;font-size:.7rem;font-weight:700;color:${gPct>=100?'#059669':gPct>=50?'var(--primary)':'#d97706'}">${gPct}%</span>
            <span style="font-family:'Poppins',sans-serif;font-size:.68rem;color:var(--text-muted)">${gPrinted.toLocaleString()}/${gTarget.toLocaleString()}</span>
            ${gToday > 0 ? `<span style="font-family:'Poppins',sans-serif;font-size:.68rem;color:#7c3aed;font-weight:600">+${gToday}</span>` : ''}
          </div>
        </div>`;

      // Each book in this grade
      gbooks.forEach(b => {
        const bPct   = b.target_qty > 0 ? Math.min(Math.floor(b.total_printed / b.target_qty * 100), 100) : 0;
        const bRem   = Math.max(b.target_qty - b.total_printed, 0);
        const bToday = b.today_qty || 0;

        // Skip books completed before today to save space
        if (bRem <= 0 && bToday <= 0) return;

        const barColor = bPct >= 100 ? '#059669' : bPct >= 50 ? '#4f46e5' : '#d97706';
        const cat    = b.category === 'perfect_binding' ? 'បិត' : 'កិប';

        html += `
          <div style="padding:.4rem .85rem .4rem 1.5rem;border-bottom:1px solid #f8fafc;
                      display:flex;align-items:center;gap:.5rem">
            <div style="flex:1;min-width:0">
              <div style="display:flex;align-items:center;gap:.4rem">
                <span style="font-size:.76rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${b.title}</span>
                <span style="font-size:.62rem;color:var(--text-muted);flex-shrink:0">${cat}</span>
              </div>
              <div style="display:flex;align-items:center;gap:.5rem;margin-top:.2rem">
                <div style="flex:1;height:4px;background:#e2e8f0;border-radius:999px;overflow:hidden">
                  <div style="height:100%;width:${bPct}%;background:${barColor};border-radius:999px"></div>
                </div>
                <span style="font-family:'Poppins',sans-serif;font-size:.65rem;font-weight:700;color:${barColor};min-width:28px;text-align:right">${bPct}%</span>
              </div>
            </div>
            <div style="text-align:right;flex-shrink:0;min-width:70px">
              <div style="font-family:'Poppins',sans-serif;font-size:.7rem;font-weight:600">
                ${b.total_printed.toLocaleString()}/${b.target_qty.toLocaleString()}
              </div>
              ${bToday > 0 ? `<div style="font-family:'Poppins',sans-serif;font-size:.65rem;color:#7c3aed;font-weight:700">+${bToday} ថ្ងៃនេះ</div>` : ''}
              ${bRem > 0 ? `<div style="font-family:'Poppins',sans-serif;font-size:.62rem;color:var(--text-muted)">សល់ ${bRem.toLocaleString()}</div>` : ''}
            </div>
          </div>`;
      });
    }
    previewList.innerHTML = html;

    // Update snapshot DOM
    const total_printed  = books.reduce((s, b) => s + b.total_printed, 0);
    const total_target   = books.reduce((s, b) => s + b.target_qty, 0);
    const total_remain   = Math.max(total_target - total_printed, 0);
    const overall_pct    = total_target > 0 ? Math.min(Math.floor(total_printed / total_target * 100), 100) : 0;

    document.getElementById('snapPrinted').textContent  = total_printed.toLocaleString();
    document.getElementById('snapRemaining').textContent= total_remain.toLocaleString();
    document.getElementById('snapPct').textContent      = overall_pct + '%';

    // New elements
    const snapTarget = document.getElementById('snapTarget');
    if (snapTarget) snapTarget.textContent = total_target.toLocaleString();
    const snapTodayTotal = document.getElementById('snapTodayTotal');
    const snapTodayQty = document.getElementById('snapTodayQty');
    const todayFiltered = books.reduce((s, b) => s + (b.today_qty || 0), 0);
    if (snapTodayTotal) snapTodayTotal.textContent = todayFiltered.toLocaleString();
    if (snapTodayQty) snapTodayQty.textContent = todayFiltered.toLocaleString();

    // Level progress bars (ALL levels always)
    const byGradeAll = {};
    ALL_BOOKS.forEach(b => { const g = b.grade||'—'; if(!byGradeAll[g]) byGradeAll[g]=[]; byGradeAll[g].push(b); });

    let levelHtml = '';
    let bestLevel = '', bestPct = 0, worstLevels = [];
    for (const [grade, gbooks] of Object.entries(byGradeAll)) {
      const gP = gbooks.reduce((s,b)=>s+b.total_printed,0);
      const gT = gbooks.reduce((s,b)=>s+b.target_qty,0);
      const gPct = gT > 0 ? Math.min(Math.floor(gP/gT*100),100) : 0;
      const barColor = gPct>=100?'#059669':gPct>=70?'#4f46e5':gPct>=40?'#d97706':'#ef4444';
      if (gPct > bestPct) { bestPct = gPct; bestLevel = grade; }
      if (gPct === 0) worstLevels.push(grade);

      levelHtml += `<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <span style="font-size:9px;font-weight:700;min-width:50px;color:#1e293b">${grade}</span>
        <div style="flex:1;height:7px;background:#e2e8f0;border-radius:999px;overflow:hidden">
          <div style="height:100%;width:${gPct}%;background:${barColor};border-radius:999px"></div>
        </div>
        <span style="font-family:'Poppins',sans-serif;font-size:9px;font-weight:800;color:${barColor};min-width:30px;text-align:right">${gPct}%</span>
      </div>`;
    }
    const snapLevelBars = document.getElementById('snapLevelBars');
    if (snapLevelBars) snapLevelBars.innerHTML = levelHtml;

    // Detail header
    const selectedG = selectedGrades().length > 0 ? selectedGrades().join(', ') : 'ទាំងអស់';
    const snapDetailHeader = document.getElementById('snapDetailHeader');
    if (snapDetailHeader) snapDetailHeader.textContent = `📋 ព័ត៌មានលម្អិត — ${selectedG} (${books.length} ចំណងជើង)`;

    // Table rows — GROUPED BY LEVEL with sub-headers
    const tbody = document.getElementById('snapTableBody');
    const byGradeDetail = {};
    books.forEach(b => {
      const g = b.grade || '—';
      if (!byGradeDetail[g]) byGradeDetail[g] = [];
      byGradeDetail[g].push(b);
    });

    let rowsHtml = '';
    for (const [grade, gbooks] of Object.entries(byGradeDetail)) {
      // Level sub-header row
      const gP = gbooks.reduce((s,b)=>s+b.total_printed,0);
      const gT = gbooks.reduce((s,b)=>s+b.target_qty,0);
      const gPct = gT > 0 ? Math.min(Math.floor(gP/gT*100),100) : 0;
      const gColor = gPct>=100?'#059669':gPct>=70?'#4f46e5':gPct>=40?'#d97706':'#ef4444';

      rowsHtml += `<tr style="background:#eff6ff;border-bottom:1px solid #bfdbfe">
        <td colspan="5" style="padding:5px 8px;font-family:'Hanuman',sans-serif;font-size:9px;font-weight:700;color:#1d4ed8">
          📘 ${grade}
        </td>
        <td style="padding:5px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:9px;font-weight:800;color:${gColor}">${gPct}%</td>
      </tr>`;

      // Book rows under this level
      gbooks.forEach(b => {
        const rem = Math.max(b.target_qty - b.total_printed, 0);
        const pct = b.target_qty > 0 ? Math.min(Math.floor(b.total_printed/b.target_qty*100),100) : 0;
        const todayQ = b.today_qty || 0;

        // Skip books completed before today to save space
        if (rem <= 0 && todayQ <= 0) return;

        const pctColor = pct>=100?'#059669':pct>=50?'#4f46e5':'#d97706';
        rowsHtml += `<tr style="border-bottom:1px solid #f1f5f9">
          <td style="padding:3px 8px 3px 16px;font-family:'Hanuman',sans-serif;font-size:9px">${b.title}</td>
          <td style="padding:3px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:9px">${b.target_qty.toLocaleString()}</td>
          <td style="padding:3px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:9px;color:#059669;font-weight:600">${b.total_printed.toLocaleString()}</td>
          <td style="padding:3px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:9px;color:${todayQ>0?'#7c3aed':'#94a3b8'};font-weight:${todayQ>0?'700':'400'}">${todayQ>0?'+'+todayQ:'—'}</td>
          <td style="padding:3px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:9px">${rem.toLocaleString()}</td>
          <td style="padding:3px 6px;text-align:center;font-family:'Poppins',sans-serif;font-size:9px;font-weight:700;color:${pctColor}">${pct}%</td>
        </tr>`;
      });
    }
    tbody.innerHTML = rowsHtml;

    // Footer summary
    const snapFooter = document.getElementById('snapFooter');
    if (snapFooter) {
      snapFooter.innerHTML = `<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:4px">
        <span>🏆 នាំមុខ: <strong>${bestLevel} (${bestPct}%)</strong></span>
        ${worstLevels.length ? `<span>⚠️ ត្រូវបន្ត: <strong>${worstLevels.join(', ')}</strong></span>` : ''}
      </div>`;
    }

    // Auto-update Telegram Caption text to match selected levels and caption mode
    if (captionEl) {
      captionEl.value = currentCaptionMode === 'full' 
        ? generateCaptionText(books) 
        : generateSummaryCaptionText(books);
      if (captionCnt) {
        const len = captionEl.value.length;
        captionCnt.textContent = len + ' / 4096';
        captionCnt.style.color = len > 3950 ? 'var(--danger)' : 'var(--text-muted)';
      }
    }
  }

  function generateSummaryCaptionText(books) {
    if (!books || !books.length) return '';

    const byGrade = {};
    books.forEach(b => {
      const g = b.grade || '—';
      if (!byGrade[g]) byGrade[g] = [];
      byGrade[g].push(b);
    });

    const now = new Date();
    const day = String(now.getDate()).padStart(2, '0');
    const monthsKhmer = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
    const month = monthsKhmer[now.getMonth()];
    const year = now.getFullYear();

    let text = `សូមគោរពរាយការណ៍ជូនឯកឧត្តមបណ្ឌិត ឯកឧត្តម លោកជំទាវ និងសមាជិកក្រុមការងារ\n`;
    text += `ថ្ងៃទី ${day} ខែ ${month} ឆ្នាំ ${year}\n\n`;
    text += `ក្រុមការងារខ្ញុំ សូមគោរពរាយការណ៍អំពីស្ថានភាពការងារបោះពុម្ពសៀវភៅ ដូចខាងក្រោម៖\n\n`;

    let summariesText = "━━━━━【បូកសរុប】━━━━━\n";
    let grandToday = 0;
    let grandTotal = 0;
    let grandRemaining = 0;

    for (const [grade, gbooks] of Object.entries(byGrade)) {
      let gradeToday = 0;
      let gradeTotal = 0;
      let gradeRemaining = 0;

      gbooks.forEach(b => {
        const rem = Math.max(b.target_qty - b.total_printed, 0);
        const todayQ = b.today_qty || 0;
        gradeToday += todayQ;
        gradeTotal += b.total_printed;
        gradeRemaining += rem;
      });

      const gTarget = gbooks.reduce((s, b) => s + b.target_qty, 0);
      summariesText += `បូកសរុប ${grade}\n`;
      summariesText += `ចំនួន Order សរុប៖ ${gTarget.toLocaleString()} ក្បាល\n`;
      summariesText += `សរុបមុន និងក្រោយ៖ ${gradeTotal.toLocaleString()} ក្បាល\n`;
      summariesText += `នៅខ្វះសរុប៖ ${gradeRemaining.toLocaleString()} ក្បាល\n\n`;

      grandToday += gradeToday;
      grandTotal += gradeTotal;
      grandRemaining += gradeRemaining;
    }

    text += summariesText;

    text += `━━【បូកសរុបការងារបោះពុម្ព】━━\n\n`;
    text += `សម្រេចបានសរុបទាំងអស់ថ្ងៃនេះ៖ ${grandToday.toLocaleString()} ក្បាល\n`;
    text += `សរុបការងារបោះពុម្ពរួច៖ ${grandTotal.toLocaleString()} ក្បាល\n`;
    if (grandRemaining > 0) {
      text += `នៅខ្វះសរុប៖ ${grandRemaining.toLocaleString()} ក្បាល\n\n`;
    } else {
      text += `ការងារបានសម្រចរួចរាល់\n\n`;
    }
    text += `សូមគោរពអរគុណ 🙏`;

    return text;
  }

  function getBookCategoryLabel(title, category) {
    const t = (title || '').toLowerCase();
    if (t.includes('textbook')) return 'Textbook';
    if (t.includes('workbook')) return 'Workbook';
    if (t.includes('song')) return 'Song';
    if (t.includes('forktale') || t.includes('folktale')) return 'Folktale';
    if (t.includes('eloquence')) return 'Eloquence';
    if (t.includes('flashcard')) return 'Flashcard';
    if (t.includes('guidebook')) return 'Guidebook';

    if (category && category.trim()) {
      const c = category.trim();
      return c.charAt(0).toUpperCase() + c.slice(1);
    }

    const parts = (title || '').trim().split(' ');
    const last = parts[parts.length - 1];
    return last ? (last.charAt(0).toUpperCase() + last.slice(1)) : 'Other';
  }

  function generateCaptionText(books) {
    if (!books || !books.length) return '';

    const byGrade = {};
    books.forEach(b => {
      const g = b.grade || '—';
      if (!byGrade[g]) byGrade[g] = [];
      byGrade[g].push(b);
    });

    const now = new Date();
    const day = String(now.getDate()).padStart(2, '0');
    const monthsKhmer = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
    const month = monthsKhmer[now.getMonth()];
    const year = now.getFullYear();

    let text = `សូមគោរពរាយការណ៍ជូនឯកឧត្តមបណ្ឌិត ឯកឧត្តម លោកជំទាវ និងសមាជិកក្រុមការងារ\n`;
    text += `ថ្ងៃទី ${day} ខែ ${month} ឆ្នាំ ${year}\n\n`;
    text += `ក្រុមការងារខ្ញុំ សូមគោរពរាយការណ៍អំពីស្ថានភាពការងារបោះពុម្ពសៀវភៅ ដូចខាងក្រោម៖\n\n`;

    let detailsText = "";
    let summariesText = "━━━━━【បូកសរុប】━━━━━\n";

    let grandToday = 0;
    let grandTotal = 0;
    let grandRemaining = 0;

    for (const [grade, gbooks] of Object.entries(byGrade)) {
      const typeTargets = {};
      gbooks.forEach(b => {
        const catLabel = getBookCategoryLabel(b.title, b.category);
        typeTargets[catLabel] = (typeTargets[catLabel] || 0) + b.target_qty;
      });

      const targets = [];
      for (const [label, qty] of Object.entries(typeTargets)) {
        if (qty > 0) {
          targets.push(`${label} = ${qty.toLocaleString()} ក្បាល`);
        }
      }

      let gradeToday = 0;
      let gradeTotal = 0;
      let gradeRemaining = 0;
      let counter = 1;
      let gradeItemsText = "";

      gbooks.forEach(b => {
        const rem = Math.max(b.target_qty - b.total_printed, 0);
        const todayQ = b.today_qty || 0;

        gradeToday += todayQ;
        gradeTotal += b.total_printed;
        gradeRemaining += rem;

        // Skip if completed before today
        if (rem <= 0 && todayQ <= 0) return;

        gradeItemsText += `${counter}/ ${b.title}\n`;
        if (todayQ > 0) {
          gradeItemsText += `សម្រេចបានថ្ងៃនេះ៖ ${todayQ.toLocaleString()} ក្បាល\n`;
        }
        gradeItemsText += `សរុបមុន និងក្រោយ៖ ${b.total_printed.toLocaleString()} ក្បាល\n`;
        if (rem > 0) {
          gradeItemsText += `នៅខ្វះសរុប៖ ${rem.toLocaleString()} ក្បាល\n`;
        }
        gradeItemsText += '\n';
        counter++;
      });

      if (gradeItemsText.trim().length > 0) {
        detailsText += `***សៀវភៅ ${grade}\n`;
        if (targets.length > 0) {
          detailsText += targets.join(' / ') + '\n\n';
        } else {
          detailsText += '\n';
        }
        detailsText += gradeItemsText;
      }

      const gTarget = gbooks.reduce((s, b) => s + b.target_qty, 0);
      summariesText += `បូកសរុប ${grade}\n`;
      summariesText += `ចំនួន Order សរុប៖ ${gTarget.toLocaleString()} ក្បាល\n`;
      summariesText += `សរុបមុន និងក្រោយ៖ ${gradeTotal.toLocaleString()} ក្បាល\n`;
      summariesText += `នៅខ្វះសរុប៖ ${gradeRemaining.toLocaleString()} ក្បាល\n\n`;

      grandToday += gradeToday;
      grandTotal += gradeTotal;
      grandRemaining += gradeRemaining;
    }

    text += detailsText;
    text += summariesText;

    text += `━━【បូកសរុបការងារបោះពុម្ព】━━\n\n`;
    text += `សម្រេចបានសរុបទាំងអស់ថ្ងៃនេះ៖ ${grandToday.toLocaleString()} ក្បាល\n`;
    text += `សរុបការងារបោះពុម្ពរួច៖ ${grandTotal.toLocaleString()} ក្បាល\n`;
    if (grandRemaining > 0) {
      text += `នៅខ្វះសរុប៖ ${grandRemaining.toLocaleString()} ក្បាល\n\n`;
    } else {
      text += `ការងារបានសម្រចរួចរាល់\n\n`;
    }
    text += `សូមគោរពអរគុណ 🙏`;

    return text;
  }

  // Chip click handlers
  chips.forEach(chip => {
    chip.addEventListener('change', () => {
      if (chip.value === '__all__') {
        // Toggle all non-all chips to match
        chips.forEach(c => {
          if (c.value !== '__all__') {
            c.checked = chip.checked;
            updateChipStyle(c);
          }
        });
      } else {
        // If unchecking a specific grade, uncheck "all" chip
        if (!chip.checked && allChip) {
          allChip.checked = false;
          updateChipStyle(allChip);
        }
        // If all specific grades are checked, re-check "all"
        const allSpecific = [...chips].filter(c => c.value !== '__all__');
        if (allSpecific.every(c => c.checked) && allChip) {
          allChip.checked = true;
          updateChipStyle(allChip);
        }
      }
      updateChipStyle(chip);
      updatePreview();
    });

    // Init styles
    updateChipStyle(chip);
  });

  // Select all / Clear all buttons
  document.getElementById('selectAllGrades')?.addEventListener('click', () => {
    chips.forEach(c => { c.checked = true; updateChipStyle(c); });
    updatePreview();
  });
  document.getElementById('clearAllGrades')?.addEventListener('click', () => {
    chips.forEach(c => { c.checked = false; updateChipStyle(c); });
    updatePreview();
  });

  // Caption counter + snapshot update
  captionEl?.addEventListener('input', () => {
    const len = captionEl.value.length;
    if (captionCnt) {
      captionCnt.textContent = len + ' / 4096';
      captionCnt.style.color = len > 3950 ? 'var(--danger)' : 'var(--text-muted)';
    }
    updatePreview();
  });

  // Table filters (existing)
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

  // ── Caption Mode Toggles ─────────────────────────────
  let currentCaptionMode = 'summary';
  const btnCaptionSummary = document.getElementById('btnCaptionSummary');
  const btnCaptionFull    = document.getElementById('btnCaptionFull');

  btnCaptionSummary?.addEventListener('click', () => {
    currentCaptionMode = 'summary';
    btnCaptionSummary.classList.add('active');
    btnCaptionFull?.classList.remove('active');
    updatePreview();
  });

  btnCaptionFull?.addEventListener('click', () => {
    currentCaptionMode = 'full';
    btnCaptionFull.classList.add('active');
    btnCaptionSummary?.classList.remove('active');
    updatePreview();
  });

  // ── Telegram Send (Image + Caption) ───────────────────
  const sendBtn     = document.getElementById('sendTelegramBtn');
  const sendTextBtn = document.getElementById('sendTextBtn');
  const groupSelect = document.getElementById('telegramGroup');
  const reportEl    = document.getElementById('telegramReport');

  sendBtn?.addEventListener('click', async () => {
    const raw = groupSelect?.value;
    if (!raw) { showToast('warning', 'សូមជ្រើសរើសក្រុម Telegram មុន'); return; }

    const parts    = raw.split('|');
    const chatId   = parts[0];
    const threadId = parts[1] || null;
    if (!chatId) { showToast('warning', 'សូមជ្រើសរើសក្រុម Telegram មុន'); return; }

    const books = filteredBooks();
    if (!books.length) { showToast('warning', 'មិនមានសៀវភៅដែលជ្រើស — សូមជ្រើស Level ណាមួយ'); return; }

    updatePreview();
    showLoading(true, 'កំពុងផ្ញើរូបភាពរបាយការណ៍...');

    reportEl.style.position = 'fixed';
    reportEl.style.left = '0';
    reportEl.style.top = '0';
    reportEl.style.zIndex = '1';
    reportEl.style.pointerEvents = 'none';

    try {
      await new Promise(r => setTimeout(r, 300));
      const canvas = await html2canvas(reportEl, {
        scale: 2,
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff',
        windowWidth: 680,
      });
      
      reportEl.style.left = '-9999px';
      reportEl.style.zIndex = '';
      reportEl.style.pointerEvents = '';

      const sendFullTextAlso = document.getElementById('sendFullTextAlso')?.checked;
      const fullTextReport   = generateCaptionText(books);

      await new Promise((resolve, reject) => {
        canvas.toBlob(async blob => {
          if (!blob) { reject(new Error('Capture failed')); return; }

          const finalCaption = captionEl.value.trim() || '📄 របាយការណ៍ការបោះពុម្ព';

          const fd = new FormData();
          fd.append('chat_id', chatId);
          if (threadId) fd.append('message_thread_id', threadId);
          fd.append('photo', blob, 'report.png');
          fd.append('caption', finalCaption);
          if (sendFullTextAlso) {
            fd.append('send_full_text', '1');
            fd.append('full_text', fullTextReport);
          }
          fd.append('_token', document.querySelector('meta[name=csrf-token]').content);

          const res  = await fetch('{{ route("telegram.send.image") }}', { method:'POST', body:fd });
          const data = await res.json();
          (res.ok && data.ok) ? resolve() : reject(new Error(data.message || 'Server error'));
        }, 'image/png');
      });

      showLoading(false);
      showToast('success', sendFullTextAlso ? 'ផ្ញើរូបភាព + អត្ថបទលម្អិតទី ២ ទៅ Telegram ជោគជ័យ! 🎉' : 'ផ្ញើរូបភាពទៅ Telegram ជោគជ័យ! 🎉');
    } catch (err) {
      reportEl.style.left = '-9999px';
      reportEl.style.zIndex = '';
      reportEl.style.pointerEvents = '';
      showLoading(false);
      showToast('error', 'ផ្ញើមិនបាន: ' + err.message);
    }
  });

  // ── Telegram Send (Text Report Only) ─────────────────
  sendTextBtn?.addEventListener('click', async () => {
    const raw = groupSelect?.value;
    if (!raw) { showToast('warning', 'សូមជ្រើសរើសក្រុម Telegram មុន'); return; }

    const parts    = raw.split('|');
    const chatId   = parts[0];
    const threadId = parts[1] || null;
    if (!chatId) { showToast('warning', 'សូមជ្រើសរើសក្រុម Telegram មុន'); return; }

    const books = filteredBooks();
    if (!books.length) { showToast('warning', 'មិនមានសៀវភៅដែលជ្រើស — សូមជ្រើស Level ណាមួយ'); return; }

    const fullTextReport = generateCaptionText(books);

    showLoading(true, 'កំពុងផ្ញើសារអត្ថបទទៅ Telegram...');

    try {
      const res = await fetch('{{ route("telegram.send") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          chat_id: chatId,
          message_thread_id: threadId,
          message: fullTextReport
        })
      });

      const data = await res.json();
      showLoading(false);

      if (res.ok && data.ok) {
        showToast('success', 'ផ្ញើសារអត្ថបទទៅ Telegram ជោគជ័យ! 🎉');
      } else {
        showToast('error', 'មិនអាចផ្ញើបានទេ: ' + (data.message || 'Server error'));
      }
    } catch (err) {
      showLoading(false);
      showToast('error', 'មានបញ្ហា: ' + err.message);
    }
  });

  // Initial render
  updatePreview();

})();

// ─── PREVIEW REPORT (Simplified) ─────────────────────────────────────────────
let currentPreviewReport = '';

async function loadPreview() {
  const previewContent = document.getElementById('previewContent');
  const previewLoading = document.getElementById('previewLoading');
  const gradeFilter = document.getElementById('previewGradeFilter').value;
  
  // Show loading
  previewLoading.style.display = 'block';
  previewContent.style.display = 'none';
  
  try {
    let url = `/report/daily?date={{ today()->toDateString() }}&format=full`;
    if (gradeFilter) {
      url += `&grade=${encodeURIComponent(gradeFilter)}`;
    }
    
    const response = await fetch(url);
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const data = await response.json();
    
    if (data.success) {
      currentPreviewReport = data.report;
      previewContent.textContent = data.report;
      previewContent.style.display = 'block';
      previewLoading.style.display = 'none';
    } else {
      throw new Error(data.message || 'Failed to load preview');
    }
  } catch (error) {
    console.error('Load preview error:', error);
    previewContent.innerHTML = `<div class="alert alert-danger">
      <i class="bi bi-exclamation-triangle-fill"></i> 
      <strong>មិនអាចផ្ទុក Preview:</strong><br>${error.message}
    </div>`;
    previewContent.style.display = 'block';
    previewLoading.style.display = 'none';
    showToast('error', 'មិនអាចផ្ទុក Preview: ' + error.message);
  }
}

async function loadPreviewWithFilter() {
  loadPreview();
}

// Auto-load preview when modal opens
document.getElementById('previewModal').addEventListener('shown.bs.modal', function () {
  loadPreview();
});

// ─── TELEGRAM SEND (AJAX + spinner + double-send guard) ───────────
(function () {
  const form = document.getElementById('telegramForm');
  if (!form) return;

  const btn     = document.getElementById('telegramSendBtn');
  const label   = btn.querySelector('.tg-btn-label');
  const loading = btn.querySelector('.tg-btn-loading');
  let sending   = false;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    e.stopPropagation();

    // Client-side guard: block while a send is in flight
    if (sending) return;
    sending = true;

    // Show spinner, disable button
    btn.disabled = true;
    label.style.display = 'none';
    loading.style.display = 'inline-flex';

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: new FormData(form)
      });

      const data = await res.json().catch(() => ({}));

      if (res.ok && data.success) {
        showToast('success', data.message || '✅ ផ្ញើជោគជ័យ!');
        const modalEl = document.getElementById('telegramModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
      } else if (res.status === 429) {
        showToast('error', data.message || 'កំពុងផ្ញើរួចហើយ សូមរង់ចាំ...');
      } else {
        showToast('error', data.message || 'មិនអាចផ្ញើបានទេ។');
      }
    } catch (err) {
      showToast('error', 'មានបញ្ហា៖ ' + err.message);
    } finally {
      // Restore button
      sending = false;
      btn.disabled = false;
      label.style.display = 'inline';
      loading.style.display = 'none';
    }
  });
})();

// Select all text in preview
function selectAllPreview() {
  const previewContent = document.getElementById('previewContent');
  const range = document.createRange();
  range.selectNodeContents(previewContent);
  const selection = window.getSelection();
  selection.removeAllRanges();
  selection.addRange(range);
  
  showToast('info', 'ជ្រើសរើសរួច! ចុច Ctrl+C ដើម្បី Copy');
}

// Show textarea for mobile copying
function showCopyTextarea() {
  const previewContent = document.getElementById('previewContent');
  const textarea = document.getElementById('mobileCopyTextarea');
  const mobileArea = document.getElementById('mobileCopyArea');
  
  const textToCopy = previewContent.textContent || previewContent.innerText;
  
  if (!textToCopy || textToCopy.trim().length === 0) {
    showToast('error', 'សូមរង់ចាំ preview ផ្ទុករួចជាមុន');
    return;
  }
  
  // Show textarea with report text
  textarea.value = textToCopy;
  mobileArea.style.display = 'block';
  previewContent.style.display = 'none';
  
  // Auto-select the text
  textarea.select();
  textarea.setSelectionRange(0, textarea.value.length);
  
  showToast('info', '👆 ចុចយូរលើ textarea ហើយជ្រើស Copy');
}

// Hide mobile copy textarea
function hideCopyTextarea() {
  const previewContent = document.getElementById('previewContent');
  const mobileArea = document.getElementById('mobileCopyArea');
  
  mobileArea.style.display = 'none';
  previewContent.style.display = 'block';
}

// Download report as text file
function downloadReport() {
  const previewContent = document.getElementById('previewContent');
  const textToDownload = previewContent.textContent || previewContent.innerText;
  
  if (!textToDownload || textToDownload.trim().length === 0) {
    showToast('error', 'សូមរង់ចាំ preview ផ្ទុករួចជាមុន');
    return;
  }
  
  // Create blob and download
  const blob = new Blob([textToDownload], { type: 'text/plain;charset=utf-8' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'daily-report-' + new Date().toISOString().split('T')[0] + '.txt';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
  
  showToast('success', '✅ របាយការណ៍ត្រូវបាន Download! បើកឯកសារ .txt ហើយ copy ចេញ។');
}

// ─── DAILY REPORT COPY TO CLIPBOARD ────────────────────────────────
</script>

<style>
.grade-chip .chip-inner {
  transition: background .15s, border-color .15s, color .15s;
}
.grade-chip .chip-inner:hover {
  filter: brightness(.96);
}
</style>
@endpush







