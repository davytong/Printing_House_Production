@extends('layouts.app')
@section('title','ស្នើរសុំ #'.$request->request_code)
@section('page-title','Print Request Detail')

@section('content')

{{-- ── Header ── --}}
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">{{ $request->title }}</h1>
    <div style="display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;margin-top:.35rem">
      <span style="font-family:var(--font-latin);font-size:.8rem;color:var(--primary);font-weight:700">
        {{ $request->request_code }}
      </span>
      @php
        $priColors=['low'=>['#94a3b8','#f1f5f9'],'normal'=>['#6366f1','#eff6ff'],'high'=>['#f97316','#fff7ed'],'urgent'=>['#ef4444','#fee2e2']];
        $priLabels=['low'=>'ទាប','normal'=>'ធម្មតា','high'=>'ខ្ពស់','urgent'=>'បន្ទាន់'];
        [$pc,$pbg] = $priColors[$request->priority] ?? ['#94a3b8','#f1f5f9'];
      @endphp
      <span style="font-size:.75rem;font-weight:700;padding:.2em .65em;border-radius:999px;
                   background:{{ $pbg }};color:{{ $pc }};border:1px solid {{ $pc }}44">
        {{ $priLabels[$request->priority] ?? $request->priority }}
      </span>
      @if($request->isOverdue())
        <span style="font-size:.75rem;font-weight:700;padding:.2em .65em;border-radius:999px;
                     background:#fee2e2;color:#dc2626;border:1px solid #fca5a5">
          <i class="bi bi-clock-history me-1"></i>ហួសសំណត់
        </span>
      @endif
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    @if($request->status === 'pending')
      <button class="btn btn-success btn-sm"
              data-bs-toggle="modal" data-bs-target="#approveModal">
        <i class="bi bi-check-lg"></i> អនុម័ត
      </button>
      <button class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none"
              data-bs-toggle="modal" data-bs-target="#rejectModal">
        <i class="bi bi-x-lg"></i> បដិសេធ
      </button>
    @endif
    @if($request->status === 'approved')
      <form action="{{ route('requests.status',$request) }}" method="POST">
        @csrf <input type="hidden" name="status" value="in_production">
        <button class="btn btn-primary btn-sm">
          <i class="bi bi-play-circle"></i> ចាប់ផ្ដើមផលិត
        </button>
      </form>
    @endif
    @if($request->status === 'in_production')
      <form action="{{ route('requests.status',$request) }}" method="POST">
        @csrf <input type="hidden" name="status" value="completed">
        <button class="btn btn-success btn-sm">
          <i class="bi bi-check-all"></i> បញ្ចប់
        </button>
      </form>
    @endif
    @if(in_array($request->status,['pending','approved']))
      <a href="{{ route('requests.edit',$request) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-pencil"></i> កែប្រែ
      </a>
    @endif
    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> ត្រឡប់
    </a>
  </div>
</div>

<div class="row g-4">

  {{-- ── LEFT: book items + attachments ── --}}
  <div class="col-lg-8 d-flex flex-column gap-4">

    {{-- Book items table --}}
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d">
            <i class="bi bi-journals"></i>
          </div>
          <span>បញ្ជីសៀវភៅ</span>
          <span style="font-family:var(--font-latin);font-size:.72rem;background:#dbeafe;
                       color:#1e40af;padding:.15em .6em;border-radius:999px;font-weight:700">
            {{ $request->items->count() }} ចំណងជើង
          </span>
        </div>
        <div style="font-family:var(--font-latin);font-size:.82rem;font-weight:700;color:var(--primary)">
          Total: {{ number_format($request->totalQty()) }} ក្បាល
        </div>
      </div>

      @if($request->items->isEmpty())
        {{-- Legacy single-book request --}}
        <div class="panel-body">
          <div class="row g-3">
            @foreach([
              ['ចំណងជើង','title',false],
              ['ចំនួន', number_format($request->quantity_requested).' ក្បាល',true],
              ['ប្រភេទ','book_type',false],
              ['ថ្នាក់','grade',false],
            ] as [$lbl,$key,$latin])
              @php $val = is_string($key) && !$latin ? ($request->$key ?? '—') : $key; @endphp
              <div class="col-sm-6">
                <div style="font-size:.72rem;font-weight:600;color:var(--text-muted);
                            text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem">
                  {{ $lbl }}
                </div>
                <div style="font-size:.9rem;font-weight:600;
                            {{ $latin?'font-family:var(--font-latin)':'' }}">
                  {{ $val }}
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @else
        <div style="overflow-x:auto">
          <table style="width:100%;border-collapse:collapse">
            <thead>
              <tr style="background:var(--surface-2);border-bottom:2px solid var(--border)">
                @foreach([
                  ['#','32px','center'],
                  ['ឈ្មោះសៀវភៅ','',''],
                  ['ថ្នាក់','90px','center'],
                  ['ប្រភេទ','120px',''],
                  ['ចំនួន','100px','right'],
                  ['Notes','',''],
                ] as [$h,$w,$align])
                  <th style="padding:.65rem .9rem;
                             {{ $w?'width:'.$w.';':'' }}
                             {{ $align?'text-align:'.$align.';':'' }}
                             font-size:.78rem;font-weight:700;color:var(--text-primary)">
                    {{ $h }}
                  </th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($request->items as $n => $item)
                <tr style="border-bottom:1px solid var(--border);
                           {{ $loop->even?'background:var(--surface-2)':'' }}">
                  <td style="padding:.6rem .9rem;text-align:center;font-family:var(--font-latin);
                             font-size:.78rem;color:var(--text-muted);font-weight:600">
                    {{ $n + 1 }}
                  </td>
                  <td style="padding:.6rem .9rem">
                    <div style="font-weight:700;font-size:.88rem">{{ $item->book_title }}</div>
                    @if($item->book)
                      <div style="font-size:.72rem;color:var(--text-muted);margin-top:.1rem">
                        <i class="bi bi-link-45deg"></i> ភ្ជាប់ទៅ: {{ $item->book->title }}
                      </div>
                    @endif
                    @if($item->notes)
                      <div style="font-size:.72rem;color:var(--text-muted);font-style:italic">
                        {{ $item->notes }}
                      </div>
                    @endif
                  </td>
                  <td style="padding:.6rem .9rem;text-align:center">
                    @if($item->grade)
                      <span class="{{ is_numeric($item->grade)?'grade-badge grade-num':'grade-badge grade-primary' }}">
                        {{ is_numeric($item->grade)?'ថ្នាក់ '.$item->grade:$item->grade }}
                      </span>
                    @else
                      <span style="color:var(--text-muted)">—</span>
                    @endif
                  </td>
                  <td style="padding:.6rem .9rem">
                    @if($item->category)
                      <span class="badge {{ $item->category==='perfect_binding'?'badge-binding':'badge-staple' }}">
                        {{ $item->categoryLabel() }}
                      </span>
                    @else
                      <span style="color:var(--text-muted)">—</span>
                    @endif
                  </td>
                  <td style="padding:.6rem .9rem;text-align:right;font-family:var(--font-latin);
                             font-weight:700;font-size:.9rem;color:var(--primary)">
                    {{ number_format($item->quantity_requested) }}
                  </td>
                  <td style="padding:.6rem .9rem;font-size:.82rem;color:var(--text-secondary)">
                    {{ $item->notes ?? '' }}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr style="background:var(--surface-2);border-top:2px solid var(--border)">
                <td colspan="4"
                    style="padding:.7rem .9rem;font-weight:700;font-size:.85rem;color:var(--text-secondary)">
                  សរុប {{ $request->items->count() }} ចំណងជើង
                </td>
                <td style="padding:.7rem .9rem;text-align:right;font-family:var(--font-latin);
                           font-weight:800;font-size:1rem;color:var(--primary)">
                  {{ number_format($request->totalQty()) }}
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      @endif
    </div>

    {{-- Notes --}}
    @if($request->notes)
      <div class="panel">
        <div class="panel-header">
          <div class="ph-title">
            <div class="ph-icon" style="background:#f5f3ff;color:#7c3aed">
              <i class="bi bi-chat-square-text"></i>
            </div>
            <span>Notes</span>
          </div>
        </div>
        <div class="panel-body" style="font-size:.88rem;color:var(--text-secondary);
                                       line-height:1.7;white-space:pre-wrap">
          {{ $request->notes }}
        </div>
      </div>
    @endif

    {{-- Attachments --}}
    @if(!empty($request->attachments))
      <div class="panel">
        <div class="panel-header">
          <div class="ph-title">
            <div class="ph-icon" style="background:#fef3c7;color:#d97706">
              <i class="bi bi-paperclip"></i>
            </div>
            <span>ឯកសារភ្ជាប់</span>
            <span style="font-family:var(--font-latin);font-size:.72rem;background:#fef9c3;
                         color:#92400e;padding:.15em .55em;border-radius:999px;font-weight:700">
              {{ count($request->attachments) }}
            </span>
          </div>
        </div>
        <div class="panel-body">
          <div class="row g-3">
            @foreach($request->attachments as $i => $att)
              @php
                $isImg = str_starts_with($att['mime'] ?? '', 'image/');
                $isPdf = ($att['mime'] ?? '') === 'application/pdf';
                $isWord= str_contains($att['mime'] ?? '', 'word');
                $isXls = str_contains($att['mime'] ?? '', 'excel') || str_contains($att['mime'] ?? '', 'sheet');
                $icon  = $isImg?'bi-file-earmark-image':($isPdf?'bi-file-earmark-pdf':($isWord?'bi-file-earmark-word':($isXls?'bi-file-earmark-excel':'bi-file-earmark')));
                $iconColor = $isImg?'#6366f1':($isPdf?'#ef4444':($isWord?'#2563eb':($isXls?'#16a34a':'#94a3b8')));
                $url = Storage::disk('public')->url($att['path']);
              @endphp
              <div class="col-sm-6 col-md-4">
                <div style="border:1px solid var(--border);border-radius:var(--radius);
                            overflow:hidden;position:relative;background:var(--surface-2)">

                  {{-- Thumbnail or icon --}}
                  @if($isImg)
                    <a href="{{ $url }}" target="_blank">
                      <img src="{{ $url }}"
                           style="width:100%;height:110px;object-fit:cover;display:block">
                    </a>
                  @else
                    <a href="{{ $url }}" target="_blank"
                       style="display:flex;align-items:center;justify-content:center;
                              height:80px;text-decoration:none">
                      <i class="bi {{ $icon }}"
                         style="font-size:2rem;color:{{ $iconColor }}"></i>
                    </a>
                  @endif

                  {{-- Info bar --}}
                  <div style="padding:.5rem .75rem;border-top:1px solid var(--border);
                              display:flex;align-items:center;gap:.5rem">
                    <div style="flex:1;min-width:0">
                      <div style="font-size:.78rem;font-weight:600;white-space:nowrap;
                                  overflow:hidden;text-overflow:ellipsis">
                        {{ $att['original_name'] }}
                      </div>
                      <div style="font-size:.68rem;color:var(--text-muted);font-family:var(--font-latin)">
                        {{ round(($att['size'] ?? 0)/1024, 1) }} KB
                      </div>
                    </div>
                    <div class="d-flex gap-1">
                      <a href="{{ $url }}" target="_blank"
                         class="btn btn-ghost btn-sm" title="View" style="padding:.2rem .4rem">
                        <i class="bi bi-eye" style="font-size:.85rem"></i>
                      </a>
                      <a href="{{ $url }}" download="{{ $att['original_name'] }}"
                         class="btn btn-ghost btn-sm" title="Download" style="padding:.2rem .4rem">
                        <i class="bi bi-download" style="font-size:.85rem"></i>
                      </a>
                      @if(in_array($request->status,['pending','approved']))
                        <form action="{{ route('requests.remove-attachment',$request) }}"
                              method="POST" style="display:inline"
                              onsubmit="return confirm('លុបឯកសារនេះ?')">
                          @csrf @method('DELETE')
                          <input type="hidden" name="index" value="{{ $i }}">
                          <button class="btn btn-ghost btn-sm"
                                  style="color:var(--danger);padding:.2rem .4rem" title="Delete">
                            <i class="bi bi-trash3" style="font-size:.85rem"></i>
                          </button>
                        </form>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    {{-- Rejection reason --}}
    @if($request->status === 'rejected' && $request->rejection_reason)
      <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:var(--radius);
                  padding:1rem 1.25rem">
        <div style="font-weight:700;color:#991b1b;margin-bottom:.35rem">
          <i class="bi bi-x-circle me-1"></i> មូលហេតុបដិសេធ
        </div>
        <p style="font-size:.88rem;color:#b91c1c;margin:0">{{ $request->rejection_reason }}</p>
      </div>
    @endif

  </div>{{-- /left --}}

  {{-- ── RIGHT: meta + status ── --}}
  <div class="col-lg-4 d-flex flex-column gap-4">

    {{-- Status pipeline --}}
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dcfce7;color:#15803d">
            <i class="bi bi-activity"></i>
          </div>
          <span>ស្ថានភាព</span>
        </div>
      </div>
      <div class="panel-body">
        @php
          $steps   = ['pending'=>0,'approved'=>1,'in_production'=>2,'completed'=>3];
          $current = $steps[$request->status] ?? 0;
          $labels  = ['pending'=>'ស្នើ','approved'=>'អនុម័ត','in_production'=>'ផលិត','completed'=>'រួចរាល់'];
        @endphp
        @foreach($labels as $k => $l)
          @php $step = $steps[$k]; @endphp
          <div style="display:flex;align-items:center;gap:.75rem;padding:.55rem 0;
                      {{ !$loop->last?'border-bottom:1px solid var(--border)':'' }}">
            <div style="width:30px;height:30px;border-radius:50%;flex-shrink:0;
                        display:flex;align-items:center;justify-content:center;
                        font-size:.75rem;font-weight:700;
                        {{ $current>$step?'background:var(--success);color:#fff':
                           ($current==$step?'background:var(--primary);color:#fff':
                           'background:var(--surface-2);color:var(--text-muted)') }}">
              {{ $current > $step ? '✓' : ($step + 1) }}
            </div>
            <span style="font-size:.88rem;
                         font-weight:{{ $current==$step?700:400 }};
                         color:{{ $current==$step?'var(--text-primary)':'var(--text-muted)' }}">
              {{ $l }}
            </span>
            @if($k==='approved' && $request->approved_by && $current>=1)
              <span style="font-size:.72rem;color:var(--text-muted);margin-left:auto">
                {{ $request->approved_by }}
              </span>
            @endif
          </div>
        @endforeach
        @if(in_array($request->status,['rejected','cancelled']))
          <div style="margin-top:.75rem;padding:.6rem .85rem;background:#fee2e2;
                      border-radius:var(--radius-sm);font-size:.82rem;color:#b91c1c;font-weight:600">
            <i class="bi bi-x-circle me-1"></i> {{ ucfirst($request->status) }}
          </div>
        @endif
      </div>
    </div>

    {{-- Meta --}}
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#eff6ff;color:#1d4ed8">
            <i class="bi bi-info-circle"></i>
          </div>
          <span>ព័ត៌មាន</span>
        </div>
      </div>
      <div class="panel-body d-flex flex-column gap-3">
        @foreach([
          ['អ្នកស្នើ',   $request->requester_name, false],
          ['នាយកដ្ឋាន', $request->department ?? '—', false],
          ['ចំណងជើង',   $request->items->count().' ចំណងជើង · '.number_format($request->totalQty()).' ក្បាល', true],
          ['ត្រូវការមុន',$request->required_by?->format('d/m/Y') ?? '—', true],
          ['ថ្ងៃស្នើ',   $request->created_at->format('d/m/Y H:i'), true],
        ] as [$lbl,$val,$latin])
          <div>
            <div style="font-size:.7rem;font-weight:600;color:var(--text-muted);
                        text-transform:uppercase;letter-spacing:.05em">{{ $lbl }}</div>
            <div style="font-size:.9rem;font-weight:600;margin-top:.15rem;
                        {{ $latin?'font-family:var(--font-latin)':'' }}">
              {{ $val }}
            </div>
          </div>
        @endforeach
        @if($request->approved_at)
          <div>
            <div style="font-size:.7rem;font-weight:600;color:var(--text-muted);
                        text-transform:uppercase;letter-spacing:.05em">អនុម័ត</div>
            <div style="font-size:.85rem;margin-top:.15rem">{{ $request->approved_by }}</div>
            <div style="font-size:.75rem;font-family:var(--font-latin);color:var(--text-muted)">
              {{ $request->approved_at->format('d/m/Y H:i') }}
            </div>
          </div>
        @endif
      </div>
    </div>

  </div>

</div>

{{-- ── Approve Modal ── --}}
<div class="modal fade" id="approveModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form action="{{ route('requests.approve',$request) }}" method="POST">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">
            <i class="bi bi-check-circle me-2 text-success"></i>អនុម័តស្នើរសុំ
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:var(--radius);
                      padding:.75rem 1rem;margin-bottom:1rem;font-size:.83rem;color:#15803d">
            <strong>{{ $request->request_code }}</strong> —
            {{ $request->items->count() }} ចំណងជើង ·
            {{ number_format($request->totalQty()) }} ក្បាល
          </div>
          <label class="form-label">ឈ្មោះអ្នកអនុម័ត *</label>
          <input type="text" name="approved_by" class="form-control"
                 placeholder="Manager Name" required>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm"
                  data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-success btn-sm">
            <i class="bi bi-check-lg"></i> បញ្ជាក់អនុម័ត
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ── Reject Modal ── --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none">
      <form action="{{ route('requests.reject',$request) }}" method="POST">
        @csrf
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" style="font-size:.95rem;font-weight:700">
            <i class="bi bi-x-circle me-2 text-danger"></i>បដិសេធស្នើរសុំ
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">មូលហេតុ *</label>
          <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button type="button" class="btn btn-outline-secondary btn-sm"
                  data-bs-dismiss="modal">បោះបង់</button>
          <button type="submit" class="btn btn-sm"
                  style="background:#ef4444;color:#fff;border:none">
            <i class="bi bi-x-lg"></i> បដិសេធ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
