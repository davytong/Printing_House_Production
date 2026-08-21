@extends('layouts.app')
@section('title', 'ស្វែងរក')
@section('page-title', 'Search Results')

@section('content')
<div class="mb-4">
  <h1 class="section-title">លទ្ធផលស្វែងរក</h1>
  @if($query)
    <p class="section-sub">ស្វែងរក: "<strong>{{ $query }}</strong>" · រកឃើញ {{ $results->count() }} លទ្ធផល</p>
  @endif
</div>

<div class="row">
  <div class="col-lg-8">
    @if($query && strlen($query) < 2)
      <div class="panel">
        <div class="panel-body text-center" style="padding:3rem">
          <i class="bi bi-search" style="font-size:3rem;color:var(--text-muted);opacity:.5"></i>
          <p style="margin-top:1rem;color:var(--text-muted)">សូមវាយបញ្ចូលយ៉ាងហោចណាស់ 2 តួអក្សរដើម្បីស្វែងរក</p>
        </div>
      </div>
    @elseif($query && $results->isEmpty())
      <div class="panel">
        <div class="panel-body text-center" style="padding:3rem">
          <i class="bi bi-inbox" style="font-size:3rem;color:var(--text-muted);opacity:.5"></i>
          <p style="margin-top:1rem;color:var(--text-muted)">មិនរកឃើញលទ្ធផល សម្រាប់ "{{ $query }}"</p>
          <p style="font-size:.85rem;color:var(--text-muted)">សាកល្បងពាក្យគន្លឹះផ្សេង ឬពិនិត្យអក្ខរាវិរុទ្ធ</p>
        </div>
      </div>
    @elseif($results->isNotEmpty())
      <!-- Pill tabs for categories -->
      <div class="search-tabs-container mb-3" style="display:flex;gap:.5rem;overflow-x:auto;padding-bottom:.5rem;scrollbar-width:none;-webkit-overflow-scrolling:touch">
        <button class="btn btn-sm btn-primary search-tab-btn active" data-target="all" style="border-radius:999px">
          ទាំងអស់ (All) <span class="badge" style="background:rgba(255,255,255,.2);color:#fff;margin-left:.25rem;font-family:var(--font-latin)">{{ $results->count() }}</span>
        </button>
        @if($books->isNotEmpty())
        <button class="btn btn-sm btn-outline-secondary search-tab-btn" data-target="book" style="border-radius:999px">
          📚 សៀវភៅ <span class="badge bg-secondary text-white ms-1" style="font-family:var(--font-latin)">{{ $books->count() }}</span>
        </button>
        @endif
        @if($materials->isNotEmpty())
        <button class="btn btn-sm btn-outline-secondary search-tab-btn" data-target="material" style="border-radius:999px">
          📦 វត្ថុធាតុដើម <span class="badge bg-secondary text-white ms-1" style="font-family:var(--font-latin)">{{ $materials->count() }}</span>
        </button>
        @endif
        @if($suppliers->isNotEmpty())
        <button class="btn btn-sm btn-outline-secondary search-tab-btn" data-target="supplier" style="border-radius:999px">
          🏢 អ្នកផ្គត់ផ្គង់ <span class="badge bg-secondary text-white ms-1" style="font-family:var(--font-latin)">{{ $suppliers->count() }}</span>
        </button>
        @endif
        @if($pos->isNotEmpty())
        <button class="btn btn-sm btn-outline-secondary search-tab-btn" data-target="purchase_order" style="border-radius:999px">
          🛒 POs <span class="badge bg-secondary text-white ms-1" style="font-family:var(--font-latin)">{{ $pos->count() }}</span>
        </button>
        @endif
        @if($requests->isNotEmpty())
        <button class="btn btn-sm btn-outline-secondary search-tab-btn" data-target="print_request" style="border-radius:999px">
          🖨️ ស្នើរបោះពុម្ព <span class="badge bg-secondary text-white ms-1" style="font-family:var(--font-latin)">{{ $requests->count() }}</span>
        </button>
        @endif
        @if($machines->isNotEmpty())
        <button class="btn btn-sm btn-outline-secondary search-tab-btn" data-target="machine" style="border-radius:999px">
          ⚙️ ម៉ាស៊ីន <span class="badge bg-secondary text-white ms-1" style="font-family:var(--font-latin)">{{ $machines->count() }}</span>
        </button>
        @endif
        @if($inventory->isNotEmpty())
        <button class="btn btn-sm btn-outline-secondary search-tab-btn" data-target="inventory" style="border-radius:999px">
          📦 Legacy Stock <span class="badge bg-secondary text-white ms-1" style="font-family:var(--font-latin)">{{ $inventory->count() }}</span>
        </button>
        @endif
      </div>

      <div class="panel">
        <div class="panel-body" style="padding:0">
          @foreach($results as $idx => $result)
            <a href="{{ $result['url'] }}" 
               class="search-result-item"
               data-type="{{ $result['type'] }}"
               style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;
                      text-decoration:none;color:inherit;border-bottom:1px solid var(--border);
                      transition:background .15s">
              <div class="search-item-icon-box"
                   style="width:40px;height:40px;border-radius:8px;
                          background:var(--surface-2);display:flex;align-items:center;
                          justify-content:center;color:var(--primary);flex-shrink:0;
                          transition:transform .2s">
                <i class="{{ $result['icon'] }}" style="font-size:1.2rem"></i>
              </div>
              <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-primary)">
                  {{ $result['title'] }}
                </div>
                <div style="font-size:.78rem;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:.15rem">
                  {{ $result['subtitle'] }}
                </div>
              </div>
              @if($result['badge'])
                <span class="badge badge-binding" style="font-size:.7rem;flex-shrink:0;font-family:var(--font-latin)">
                  {{ $result['badge'] }}
                </span>
              @endif
              <i class="bi bi-chevron-right" style="color:var(--text-muted);font-size:.8rem"></i>
            </a>
          @endforeach

          <div id="no-tab-results" class="text-center d-none" style="padding:4rem 2rem">
            <i class="bi bi-inbox" style="font-size:3rem;color:var(--text-muted);opacity:.5"></i>
            <p style="margin-top:1rem;color:var(--text-muted);font-size:.88rem">មិនរកឃើញលទ្ធផលសម្រាប់ប្រភេទនេះទេ / No results found for this category</p>
          </div>
        </div>
      </div>
    @else
      <div class="panel">
        <div class="panel-body text-center" style="padding:3rem">
          <i class="bi bi-search" style="font-size:3rem;color:var(--text-muted);opacity:.5"></i>
          <p style="margin-top:1rem;color:var(--text-muted)">វាយបញ្ចូលពាក្យគន្លឹះខាងលើ ដើម្បីស្វែងរក</p>
        </div>
      </div>
    @endif
  </div>

  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-header">
        <div class="ph-title">
          <div class="ph-icon" style="background:#dbeafe;color:#1d4ed8">
            <i class="bi bi-info-circle"></i>
          </div>
          <span>អំពីការស្វែងរក</span>
        </div>
      </div>
      <div class="panel-body">
        <p style="font-size:.82rem;line-height:1.7;color:var(--text-secondary)">
          អ្នកអាចស្វែងរក៖
        </p>
        <ul style="font-size:.82rem;line-height:1.9;color:var(--text-secondary);padding-left:1.5rem">
          <li>សៀវភៅ (ចំណងជើង, ថ្នាក់)</li>
          <li>វត្ថុធាតុដើម (ឈ្មោះ, លេខកូដ)</li>
          <li>អ្នកផ្គត់ផ្គង់ (ឈ្មោះ, កូដ)</li>
          <li>Purchase Orders</li>
          <li>Print Requests</li>
          <li>ម៉ាស៊ីន</li>
          <li>Legacy Inventory</li>
        </ul>
        <div style="margin-top:1.25rem;padding:.85rem;background:var(--surface-2);border:1px solid var(--border);border-radius:10px;font-size:.78rem;color:var(--text-secondary);display:flex;align-items:flex-start;gap:.5rem">
          <i class="bi bi-lightbulb" style="color:var(--warning);font-size:1rem"></i>
          <div>
            <strong>ជំនួយ:</strong> វាយយ៉ាងហោចណាស់ 2 តួអក្សរ។ ប្រើ Ctrl+K សម្រាប់ផ្លូវកាត់ស្វែងរកពីគ្រប់ទំព័រ។
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.search-result-item:hover {
  background: var(--surface-2);
}
.search-result-item:hover .search-item-icon-box {
  transform: scale(1.08);
}
.search-result-item:last-child {
  border-bottom: none;
}
.search-tabs-container::-webkit-scrollbar {
  display: none;
}
</style>

@push('scripts')
<script>
document.querySelectorAll('.search-tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    // Toggle active classes
    document.querySelectorAll('.search-tab-btn').forEach(b => {
      b.classList.remove('btn-primary', 'active');
      b.classList.add('btn-outline-secondary');
    });
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-primary', 'active');

    const target = btn.dataset.target;
    let visibleCount = 0;

    document.querySelectorAll('.search-result-item').forEach(item => {
      if (target === 'all' || item.dataset.type === target) {
        item.style.setProperty('display', 'flex', 'important');
        visibleCount++;
      } else {
        item.style.setProperty('display', 'none', 'important');
      }
    });

    const emptyBox = document.getElementById('no-tab-results');
    if (visibleCount === 0) {
      emptyBox.classList.remove('d-none');
    } else {
      emptyBox.classList.add('d-none');
    }
  });
});
</script>
@endpush
@endsection
