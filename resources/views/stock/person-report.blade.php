@extends('layouts.app')
@section('title','Person Stock Report')
@section('page-title','Person Stock Report')

@section('breadcrumbs')
<div class="breadcrumbs">
  <a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <a href="{{ route('stock.movements.index') }}">Stock</a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <span class="bc-active">Person Report</span>
</div>
@endsection

@section('content')
<style>
.pr-hero {
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #1e293b 100%);
  border-radius: 24px;
  padding: 2rem 2.5rem;
  margin-bottom: 1.75rem;
  position: relative;
  overflow: hidden;
}
.pr-hero::before {
  content:'';position:absolute;top:-60px;right:-60px;width:280px;height:280px;
  border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.25) 0%,transparent 70%);pointer-events:none;
}
.pr-hero::after {
  content:'';position:absolute;bottom:-40px;left:30%;width:200px;height:200px;
  border-radius:50%;background:radial-gradient(circle,rgba(139,92,246,.15) 0%,transparent 70%);pointer-events:none;
}
.pr-hero-title { font-size:1.6rem;font-weight:800;color:#fff;margin-bottom:.3rem;position:relative;z-index:1; }
.pr-hero-sub   { font-size:.85rem;color:rgba(255,255,255,.5);position:relative;z-index:1;font-family:var(--font-latin); }

.hero-kpi-row { display:flex;gap:.85rem;margin-top:1.5rem;flex-wrap:wrap;position:relative;z-index:1; }
.hero-kpi {
  background:rgba(255,255,255,.07);backdrop-filter:blur(12px);
  border:1px solid rgba(255,255,255,.11);border-radius:14px;padding:.85rem 1.25rem;min-width:110px;
  transition:all .2s;
}
.hero-kpi:hover { background:rgba(255,255,255,.13);transform:translateY(-2px); }
.hero-kpi .hk-val { font-family:var(--font-latin);font-size:1.75rem;font-weight:800;line-height:1;color:#fff; }
.hero-kpi .hk-lbl { font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:rgba(255,255,255,.4);font-family:var(--font-latin);margin-top:.25rem; }
.hk-out .hk-val { color:#f87171; }
.hk-in  .hk-val { color:#34d399; }
.hk-ppl .hk-val { color:#a78bfa; }
.hk-tot .hk-val { color:#60a5fa; }

.pr-filter {
  display:flex;align-items:center;flex-wrap:wrap;gap:.75rem;
  background:#fff;border:1px solid rgba(226,232,240,.8);border-radius:16px;
  padding:1rem 1.5rem;margin-bottom:1.75rem;box-shadow:0 2px 12px rgba(0,0,0,.03);
}
[data-theme="dark"] .pr-filter { background:var(--surface);border-color:rgba(255,255,255,.07); }

.period-tabs { display:flex;gap:3px;background:var(--surface-2);border-radius:10px;padding:3px; }
.period-tab {
  padding:.36rem .82rem;border-radius:7px;border:none;background:transparent;
  font-size:.78rem;font-weight:600;color:var(--text-muted);cursor:pointer;
  transition:all .18s;font-family:var(--font-khmer);white-space:nowrap;
}
.period-tab.active { background:#fff;color:var(--primary);box-shadow:0 2px 8px rgba(0,0,0,.08); }
[data-theme="dark"] .period-tab.active { background:#1e293b;color:#a5b4fc; }
.pr-select {
  font-family:var(--font-khmer);font-size:.82rem;border-radius:10px;
  border:1.5px solid var(--border);padding:.38rem .85rem;background:var(--surface-2);
  color:var(--text-primary);cursor:pointer;outline:none;transition:border-color .2s;
}
.pr-select:focus { border-color:var(--primary); }

.persons-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(460px,1fr));gap:1.25rem; }
@media (max-width:680px) { .persons-grid { grid-template-columns:1fr; } }

.pcard {
  background:#fff;border-radius:22px;border:1px solid rgba(226,232,240,.7);
  box-shadow:0 4px 20px rgba(0,0,0,.04);overflow:hidden;
  transition:transform .3s cubic-bezier(.4,0,.2,1),box-shadow .3s;
}
.pcard:hover { transform:translateY(-5px);box-shadow:0 18px 44px rgba(0,0,0,.09); }
[data-theme="dark"] .pcard { background:var(--surface);border-color:rgba(255,255,255,.08); }

.pcard-banner { height:5px; }

.pcard-head {
  display:flex;align-items:center;gap:1rem;padding:1.1rem 1.5rem .9rem;
  cursor:pointer;user-select:none;transition:background .15s;
}
.pcard-head:hover { background:rgba(0,0,0,.015); }
[data-theme="dark"] .pcard-head:hover { background:rgba(255,255,255,.02); }

.pcard-avatar {
  width:52px;height:52px;border-radius:16px;display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:1.2rem;font-family:var(--font-latin);color:#fff;
  flex-shrink:0;box-shadow:0 6px 18px rgba(0,0,0,.18);letter-spacing:-.02em;position:relative;
}
.rank-pip {
  position:absolute;top:-5px;right:-5px;width:20px;height:20px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:800;
  font-family:var(--font-latin);color:#fff;border:2px solid #fff;
}
.pcard-info { flex:1;min-width:0; }
.pcard-name { font-size:.98rem;font-weight:700;color:var(--text-primary);margin-bottom:.25rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.pcard-meta { display:flex;gap:.45rem;flex-wrap:wrap; }
.pmeta-chip {
  font-size:.65rem;font-weight:700;font-family:var(--font-latin);color:var(--text-muted);
  background:var(--surface-2);border-radius:6px;padding:.15rem .5rem;display:inline-flex;align-items:center;gap:.25rem;
}

.pcard-act { padding:.65rem 1.5rem .5rem;border-bottom:1px solid var(--border); }
.act-label-row { display:flex;justify-content:space-between;align-items:center;margin-bottom:.35rem; }
.act-track { height:7px;border-radius:999px;background:var(--surface-2);overflow:hidden; }
.act-fill {
  height:100%;border-radius:999px;width:0;
  transition:width 1.2s cubic-bezier(.34,1.56,.64,1);position:relative;overflow:hidden;
}
.act-fill::after {
  content:'';position:absolute;inset:0;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.45),transparent);
  animation:pr-shimmer 2.2s infinite linear;
}
@keyframes pr-shimmer { 0%{transform:translateX(-100%)} 100%{transform:translateX(100%)} }

.pcard-stats { display:flex;border-bottom:1px solid var(--border); }
.pstat {
  flex:1;display:flex;flex-direction:column;align-items:center;
  padding:.7rem .5rem;border-right:1px solid var(--border);transition:background .15s;
}
.pstat:last-child { border-right:none; }
.pstat:hover { background:var(--surface-2); }
[data-theme="dark"] .pstat:hover { background:rgba(255,255,255,.03); }
.pstat-val { font-family:var(--font-latin);font-size:1.1rem;font-weight:800;line-height:1.1; }
.pstat-lbl { font-size:.6rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--text-muted);font-family:var(--font-latin);margin-top:.15rem; }
.sv-out { color:#ef4444; }
.sv-in  { color:#10b981; }
.sv-tot { color:var(--text-primary); }

.cat-chips { display:flex;gap:.4rem;flex-wrap:wrap;padding:.65rem 1.5rem;border-bottom:1px solid var(--border); }
.cat-chip { display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;font-family:var(--font-latin);padding:.25rem .65rem;border-radius:8px; }

.pcard-items { overflow:hidden; }
.pit-thead { display:grid;grid-template-columns:1fr 82px 82px;gap:.5rem;padding:.45rem 1.5rem;background:var(--surface-2); }
[data-theme="dark"] .pit-thead { background:rgba(255,255,255,.04); }
.pit-th { font-size:.63rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);font-family:var(--font-latin); }
.pit-th.r { text-align:right; }
.pit-row {
  display:grid;grid-template-columns:1fr 82px 82px;gap:.5rem;align-items:center;
  padding:.58rem 1.5rem;border-bottom:1px solid rgba(226,232,240,.5);transition:background .15s;
}
.pit-row:last-child { border-bottom:none; }
.pit-row:hover { background:rgba(79,70,229,.03); }
[data-theme="dark"] .pit-row:hover { background:rgba(99,102,241,.05); }
.pit-mat { display:flex;align-items:center;gap:.6rem;min-width:0; }
.pit-dot { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
.pit-nm  { font-size:.83rem;font-weight:600;color:var(--text-primary); }
.pit-sub { font-size:.68rem;color:var(--text-muted); }
.pit-meta { font-size:.62rem;color:var(--text-muted);font-family:var(--font-latin); }
.pit-out { text-align:right;font-family:var(--font-latin);font-weight:800;font-size:.88rem;color:#ef4444; }
.pit-in  { text-align:right;font-family:var(--font-latin);font-weight:800;font-size:.88rem;color:#10b981; }
.pit-nil { text-align:right;font-family:var(--font-latin);font-size:.78rem;color:rgba(0,0,0,.18); }
.pit-unit { font-size:.62rem;font-weight:500;color:var(--text-muted); }

.pcard-toggle {
  display:flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem;
  font-size:.75rem;font-weight:600;color:var(--primary);cursor:pointer;
  background:rgba(79,70,229,.03);border-top:1px solid var(--border);transition:background .15s;user-select:none;
}
.pcard-toggle:hover { background:rgba(79,70,229,.08); }
[data-theme="dark"] .pcard-toggle { background:rgba(99,102,241,.04); }
[data-theme="dark"] .pcard-toggle:hover { background:rgba(99,102,241,.1); }

.pr-empty {
  grid-column:1/-1;display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:5rem 2rem;background:#fff;border-radius:22px;border:1.5px dashed var(--border);
  color:var(--text-muted);gap:.75rem;text-align:center;
}
[data-theme="dark"] .pr-empty { background:var(--surface); }
.pr-empty i { font-size:3.5rem;opacity:.15; }
</style>

{{-- HERO --}}
<div class="pr-hero">
  <div class="pr-hero-title"><i class="bi bi-people-fill" style="margin-right:.5rem"></i>Person Stock Report</div>
  <div class="pr-hero-sub">សង្ខេបការប្រើប្រាស់ Stock តាមមនុស្ស &nbsp;·&nbsp; Who took what items &amp; how much</div>
  <div class="hero-kpi-row">
    <div class="hero-kpi hk-ppl"><div class="hk-val">{{ count($personData) }}</div><div class="hk-lbl">People</div></div>
    <div class="hero-kpi hk-tot"><div class="hk-val">{{ $totalMovements }}</div><div class="hk-lbl">Movements</div></div>
    <div class="hero-kpi hk-out"><div class="hk-val">{{ $totalOut }}</div><div class="hk-lbl">Stock OUT</div></div>
    <div class="hero-kpi hk-in"><div class="hk-val">{{ $totalIn }}</div><div class="hk-lbl">Stock IN</div></div>
    <div class="hero-kpi" style="margin-left:auto">
      <div class="hk-val" style="font-size:.95rem;color:rgba(255,255,255,.65)">
        {{ \Carbon\Carbon::parse($startDate)->format('d M') }} → {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
      </div>
      <div class="hk-lbl">Period</div>
    </div>
  </div>
</div>

{{-- FILTER --}}
<form method="GET" action="{{ route('stock.person-report') }}" id="prForm">
<input type="hidden" name="period" id="periodInput" value="{{ $period }}">
<div class="pr-filter">
  <div class="period-tabs">
    @foreach(['this_week'=>'សប្តាហ៍នេះ','last_week'=>'សប្តាហ៍មុន','this_month'=>'ខែនេះ','last_month'=>'ខែមុន','custom'=>'⚙ Custom'] as $v=>$l)
      <button type="button" class="period-tab {{ $period===$v?'active':'' }}" onclick="setPeriod('{{ $v }}')">{{ $l }}</button>
    @endforeach
  </div>
  <div id="customDates" style="{{ $period==='custom'?'display:flex':'display:none' }};gap:.5rem;align-items:center">
    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="width:138px;border-radius:10px">
    <span style="color:var(--text-muted)">→</span>
    <input type="date" name="end_date"   class="form-control form-control-sm" value="{{ $endDate }}"   style="width:138px;border-radius:10px">
    <button type="submit" class="btn btn-primary btn-sm" style="border-radius:10px"><i class="bi bi-check2"></i></button>
  </div>
  <div style="flex:1"></div>
  <select name="category" class="pr-select" onchange="document.getElementById('prForm').submit()">
    <option value="">ប្រភេទទាំងអស់</option>
    <option value="paper"      {{ $category==='paper'?'selected':'' }}>📄 Paper</option>
    <option value="consumable" {{ $category==='consumable'?'selected':'' }}>🛒 Consumable</option>
    <option value="film"       {{ $category==='film'?'selected':'' }}>🎞 Film</option>
  </select>
  <select name="type" class="pr-select" onchange="document.getElementById('prForm').submit()">
    <option value="">ចលនាទាំងអស់</option>
    <option value="out"    {{ $type==='out'?'selected':'' }}>📤 OUT</option>
    <option value="in"     {{ $type==='in'?'selected':'' }}>📥 IN</option>
    <option value="adjust" {{ $type==='adjust'?'selected':'' }}>🔧 Adjust</option>
  </select>
</div>
</form>

{{-- CARDS GRID --}}
@php
$grads=[['#4f46e5','#818cf8'],['#0ea5e9','#38bdf8'],['#10b981','#34d399'],
        ['#f59e0b','#fcd34d'],['#ef4444','#f87171'],['#8b5cf6','#a78bfa'],
        ['#ec4899','#f472b6'],['#06b6d4','#22d3ee'],['#84cc16','#a3e635'],['#f97316','#fb923c']];
$cc=['paper'=>['bg'=>'#dbeafe','c'=>'#1d4ed8','d'=>'#3b82f6'],
     'consumable'=>['bg'=>'#fef3c7','c'=>'#92400e','d'=>'#f59e0b'],
     'film'=>['bg'=>'#f5f3ff','c'=>'#5b21b6','d'=>'#8b5cf6'],
     'other'=>['bg'=>'#f1f5f9','c'=>'#475569','d'=>'#94a3b8']];
$maxM = collect($personData)->max('movement_count') ?: 1;
@endphp

<div class="persons-grid">
@forelse($personData as $i => $p)
@php
[$g1,$g2]=$grads[$i%count($grads)];
$pct=max(5,round($p['movement_count']/$maxM*100));
$cmap=[];
foreach($p['items'] as $it){
  $cat=$it['category']??'other';
  if(!isset($cmap[$cat])) $cmap[$cat]=['out'=>0,'in'=>0,'cnt'=>0];
  $cmap[$cat]['out']+=$it['out_qty'];
  $cmap[$cat]['in']+=$it['in_qty'];
  $cmap[$cat]['cnt']++;
}
@endphp

<div class="pcard">
  <div class="pcard-banner" style="background:linear-gradient(90deg,{{ $g1 }},{{ $g2 }})"></div>

  {{-- HEAD --}}
  <div class="pcard-head" onclick="toggle({{ $i }})">
    <div style="position:relative;flex-shrink:0">
      <div class="pcard-avatar" style="background:linear-gradient(135deg,{{ $g1 }},{{ $g2 }})">
        {{ mb_strtoupper(mb_substr($p['name'],0,2)) }}
      </div>
      <div class="rank-pip" style="background:{{ $g1 }}">{{ $i+1 }}</div>
    </div>
    <div class="pcard-info">
      <div class="pcard-name">{{ $p['name'] }}</div>
      <div class="pcard-meta">
        <span class="pmeta-chip"><i class="bi bi-calendar3" style="font-size:.58rem"></i>{{ $p['days_active'] }}d</span>
        <span class="pmeta-chip"><i class="bi bi-box-seam" style="font-size:.58rem"></i>{{ $p['item_count'] }} items</span>
        <span class="pmeta-chip"><i class="bi bi-arrow-left-right" style="font-size:.58rem"></i>{{ $p['movement_count'] }} moves</span>
      </div>
    </div>
    <i class="bi bi-chevron-down" id="chv-{{ $i }}" style="color:var(--text-muted);flex-shrink:0;transition:transform .3s"></i>
  </div>

  {{-- ACTIVITY BAR --}}
  <div class="pcard-act">
    <div class="act-label-row">
      <span style="font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--text-muted);font-family:var(--font-latin)">Activity vs Top</span>
      <span style="font-size:.72rem;font-weight:800;font-family:var(--font-latin);color:{{ $g1 }}">{{ $pct }}%</span>
    </div>
    <div class="act-track">
      <div class="act-fill" data-w="{{ $pct }}" style="background:linear-gradient(90deg,{{ $g1 }},{{ $g2 }})"></div>
    </div>
  </div>

  {{-- STATS --}}
  <div class="pcard-stats">
    <div class="pstat"><div class="pstat-val sv-out">{{ number_format($p['total_out_qty'],0) }}</div><div class="pstat-lbl">OUT qty</div></div>
    <div class="pstat"><div class="pstat-val sv-in">{{ number_format($p['total_in_qty'],0) }}</div><div class="pstat-lbl">IN qty</div></div>
    <div class="pstat"><div class="pstat-val sv-tot" style="font-size:.95rem">{{ $p['total_out'] }}</div><div class="pstat-lbl">OUT trans</div></div>
    <div class="pstat"><div class="pstat-val sv-tot">{{ $p['movement_count'] }}</div><div class="pstat-lbl">Total</div></div>
  </div>

  {{-- CATEGORY CHIPS --}}
  @if(!empty($cmap))
  <div class="cat-chips">
    @foreach($cmap as $cat=>$cd)
    @php $cs=$cc[$cat]??$cc['other']; @endphp
    <div class="cat-chip" style="background:{{ $cs['bg'] }};color:{{ $cs['c'] }}">
      <div style="width:6px;height:6px;border-radius:50%;background:{{ $cs['d'] }}"></div>
      {{ ucfirst($cat) }}
      @if($cd['out']>0)<span style="opacity:.65">-{{ number_format($cd['out'],0) }}</span>@endif
      @if($cd['in']>0)<span style="opacity:.65">+{{ number_format($cd['in'],0) }}</span>@endif
    </div>
    @endforeach
  </div>
  @endif

  {{-- ITEMS (collapsible) --}}
  <div class="pcard-items" id="pit-{{ $i }}" style="{{ $i>0?'display:none':'' }}">
    <div class="pit-thead">
      <div class="pit-th">Material</div>
      <div class="pit-th r">📤 OUT</div>
      <div class="pit-th r">📥 IN</div>
    </div>
    @foreach($p['items'] as $it)
    @php $cs=$cc[$it['category']??'other']??$cc['other']; @endphp
    <div class="pit-row">
      <div class="pit-mat">
        <div class="pit-dot" style="background:{{ $cs['d'] }}"></div>
        <div style="min-width:0">
          <div class="pit-nm">{{ $it['name'] }}</div>
          @if($it['name_km'])<div class="pit-sub">{{ $it['name_km'] }}</div>@endif
          @if($it['last_date'])<div class="pit-meta">Last: {{ \Carbon\Carbon::parse($it['last_date'])->format('d M') }}@if($it['reference']) · {{ Str::limit($it['reference'],18) }}@endif</div>@endif
        </div>
      </div>
      @if($it['out_qty']>0)
        <div class="pit-out">{{ number_format($it['out_qty'],1)+0 }}<span class="pit-unit"> {{ $it['unit'] }}</span></div>
      @else
        <div class="pit-nil">—</div>
      @endif
      @if($it['in_qty']>0)
        <div class="pit-in">{{ number_format($it['in_qty'],1)+0 }}<span class="pit-unit"> {{ $it['unit'] }}</span></div>
      @else
        <div class="pit-nil">—</div>
      @endif
    </div>
    @endforeach
  </div>

  {{-- TOGGLE --}}
  <div class="pcard-toggle" onclick="toggle({{ $i }})">
    <span id="ptog-lbl-{{ $i }}">{{ $i===0?'បង្រួម':'មើលលម្អិត →' }}</span>
    <i class="bi bi-chevron-{{ $i===0?'up':'down' }}" id="ptog-ic-{{ $i }}"></i>
  </div>
</div>

@empty
<div class="pr-empty">
  <i class="bi bi-person-slash"></i>
  <div style="font-size:1rem;font-weight:700;color:var(--text-secondary)">គ្មានទិន្នន័យ — No data found</div>
  <div style="font-size:.85rem;max-width:380px">
    No stock movements with a recorded person found.<br>
    Fill in the <strong>«Performed By»</strong> field when recording stock movements.
  </div>
  <a href="{{ route('stock.movements.create') }}" class="btn btn-primary btn-sm mt-2">
    <i class="bi bi-plus-lg"></i> Record Movement
  </a>
</div>
@endforelse
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.act-fill').forEach((el, i) => {
    setTimeout(() => { el.style.width = el.dataset.w + '%'; }, 120 + i * 70);
  });
});

function toggle(i) {
  const items = document.getElementById('pit-' + i);
  const lbl   = document.getElementById('ptog-lbl-' + i);
  const ic    = document.getElementById('ptog-ic-' + i);
  const chv   = document.getElementById('chv-' + i);
  const open  = items.style.display !== 'none';
  if (open) {
    items.style.display = 'none';
    lbl.textContent = 'មើលលម្អិត →';
    ic.className  = 'bi bi-chevron-down';
    if (chv) chv.style.transform = '';
  } else {
    items.style.display = '';
    lbl.textContent = 'បង្រួម';
    ic.className  = 'bi bi-chevron-up';
    if (chv) chv.style.transform = 'rotate(180deg)';
  }
}

function setPeriod(val) {
  document.getElementById('periodInput').value = val;
  document.querySelectorAll('.period-tab').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
  const cd = document.getElementById('customDates');
  if (val === 'custom') { cd.style.display = 'flex'; }
  else { cd.style.display = 'none'; document.getElementById('prForm').submit(); }
}
</script>
@endpush
