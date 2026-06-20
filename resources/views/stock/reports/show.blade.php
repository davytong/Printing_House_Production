@extends('layouts.app')
@section('title','Stock Report — '.$stockReport->report_date->format('d/m/Y'))
@section('page-title','Stock Report Detail')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">{{ $stockReport->title ?? 'Stock Report' }}</h1>
    <p class="section-sub" style="font-family:var(--font-latin)">{{ $stockReport->report_date->format('d/m/Y') }}</p>
    <span class="badge {{ match($stockReport->category){'paper'=>'badge-binding','film'=>'badge-staple','offset'=>'badge-progress',default=>'badge-done'} }}" style="margin-top:.4rem">{{ $stockReport->categoryLabel() }}</span>
  </div>
  <div class="d-flex gap-2">
    <form action="{{ route('stock.reports.destroy', $stockReport) }}" method="POST"
          onsubmit="return confirm('តើអ្នកប្រាកដថាចង់លុបរបាយការណ៍នេះមែនទេ?')">
      @csrf @method('DELETE')
      <button class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none"><i class="bi bi-trash3"></i></button>
    </form>
    <a href="{{ route('stock.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
  </div>
</div>

<div class="row g-4">

  <div class="col-lg-8">
    {{-- Image --}}
    @if($stockReport->image_path)
      <div class="panel mb-4">
        <div class="panel-header"><div class="ph-title"><div class="ph-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-image"></i></div><span>រូបភាពរបាយការណ៍</span></div></div>
        <div class="panel-body" style="text-align:center">
          <a href="{{ Storage::disk('public')->url($stockReport->image_path) }}" target="_blank">
            <img src="{{ Storage::disk('public')->url($stockReport->image_path) }}"
                 style="max-width:100%;border-radius:var(--radius);border:1px solid var(--border)">
          </a>
        </div>
      </div>
    @endif

    {{-- Summary data --}}
    @if($stockReport->summary_data && isset($stockReport->summary_data['categories']))
      <div class="panel">
        <div class="panel-header"><div class="ph-title"><div class="ph-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-bar-chart"></i></div><span>សង្ខេបស្ថានភាព Stock</span></div></div>
        <div class="panel-body">
          @foreach($stockReport->summary_data['categories'] as $cat => $data)
            @php $emoji = match($cat){'paper'=>'📄','film'=>'🎞️',default=>'🖨️'}; @endphp
            <div style="margin-bottom:1rem">
              <div style="font-weight:700;font-size:.9rem;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem">
                <span>{{ $emoji }} {{ $data['label'] }}</span>
                <span style="font-family:var(--font-latin);font-size:.72rem;background:#dbeafe;color:#1e40af;padding:.1em .5em;border-radius:999px">{{ $data['count'] }}</span>
                @if($data['low_count'] > 0)
                  <span style="font-family:var(--font-latin);font-size:.72rem;background:#fee2e2;color:#dc2626;padding:.1em .5em;border-radius:999px">⚠️ {{ $data['low_count'] }} low</span>
                @endif
              </div>
              @foreach($data['items'] as $item)
                @php $color = $item['is_low'] ? 'var(--danger)' : 'var(--text-secondary)'; @endphp
                <div style="display:flex;justify-content:space-between;padding:.25rem 0 .25rem 1rem;font-size:.83rem;border-bottom:1px solid var(--surface-2)">
                  <span>{{ $item['name'] }}{{ $item['sub_type']?' · '.$item['sub_type']:'' }}</span>
                  <span style="font-family:var(--font-latin);font-weight:600;color:{{ $color }}">
                    {{ number_format($item['stock'],1) }} {{ $item['unit'] }}
                    @if($item['is_low']) ⚠️ @endif
                  </span>
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <div class="col-lg-4">
    {{-- Meta + Send --}}
    <div class="panel mb-4">
      <div class="panel-header"><div class="ph-title"><div class="ph-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-send-fill"></i></div><span>ផ្ញើទៅ Telegram</span></div></div>
      <div class="panel-body">
        @if($stockReport->telegram_sent)
          <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:var(--radius);padding:.75rem;font-size:.82rem;color:#065f46;margin-bottom:1rem">
            ✅ បានផ្ញើរួចរាល់ · {{ $stockReport->sent_at?->format('d/m/Y H:i') }}
          </div>
        @endif

        @if($telegramGroups->isEmpty())
          <div class="alert-info-soft">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span style="font-size:.82rem">មិនទាន់មាន Telegram Group</span>
          </div>
        @else
          <form action="{{ route('stock.reports.send', $stockReport) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">ជ្រើសរើសក្រុមដែលត្រូវផ្ញើ</label>
              <select id="stockReportGroup" class="form-select" required>
                <option value="">— ជ្រើសរើសក្រុម —</option>
                @foreach($telegramGroups as $g)
                  <option value="{{ $g->chat_id }}|{{ $g->message_thread_id ?? '' }}">
                    📌 {{ $g->displayLabel() }}
                  </option>
                @endforeach
              </select>
              <input type="hidden" name="chat_id" id="stockReportChatId" value="">
              <input type="hidden" name="message_thread_id" id="stockReportThread" value="">
            </div>
            <button type="submit" class="btn btn-success w-100">
              <i class="bi bi-send-fill"></i> ផ្ញើទៅ Telegram
            </button>
          </form>
        @endif
      </div>
    </div>

    {{-- Notes --}}
    @if($stockReport->notes)
      <div class="panel">
        <div class="panel-header"><div class="ph-title"><div class="ph-icon" style="background:#f5f3ff;color:#7c3aed"><i class="bi bi-chat-text"></i></div><span>Notes</span></div></div>
        <div class="panel-body" style="font-size:.88rem;color:var(--text-secondary);white-space:pre-wrap">{{ $stockReport->notes }}</div>
      </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
// Group selector → hidden fields (no submit mutation)
(function() {
  const sel     = document.getElementById('stockReportGroup');
  const chatIn  = document.getElementById('stockReportChatId');
  const thrdIn  = document.getElementById('stockReportThread');
  if (!sel) return;

  function sync() {
    const [chatId, threadId] = (sel.value || '').split('|');
    if (chatIn)  chatIn.value  = chatId   || '';
    if (thrdIn)  thrdIn.value  = threadId || '';
  }

  sel.addEventListener('change', sync);
  sync(); // sync on load if default option has a value
})();
</script>
@endpush
