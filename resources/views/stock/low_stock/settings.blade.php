@extends('layouts.app')
@section('title', 'ការកំណត់ការជូនដំណឹងស្តុកជិតអស់')
@section('page-title', 'Low Stock Alert Settings')

@section('breadcrumbs')
<div class="breadcrumbs">
  <a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <a href="{{ route('stock.movements.index') }}">Stock</a>
  <i class="bi bi-chevron-right bc-sep"></i>
  <span class="bc-active">Low Stock Settings</span>
</div>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <div>
    <h1 class="section-title">
      <i class="bi bi-sliders text-primary me-2"></i>
      <span>ការកំណត់ Low Stock Alert &amp; ក្រុមទទួលសារ</span>
    </h1>
    <p class="section-sub">Configure low-stock alert thresholds, recipient Telegram groups, and notification template</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('stock.low-stock.history') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-clock-history me-1"></i> ប្រវត្តិផ្ញើសារ History
    </a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success d-flex align-items-center mb-3">
    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

<form action="{{ route('stock.low-stock.update-settings') }}" method="POST">
  @csrf

  <div class="row g-4">
    {{-- Left column: General thresholds & Enable --}}
    <div class="col-lg-6">

      {{-- Enable Toggle Panel --}}
      <div class="panel mb-4">
        <div class="panel-header">
          <div class="ph-title">
            <div class="ph-icon bg-primary text-white"><i class="bi bi-bell-fill"></i></div>
            <span>បើក/បិទ ការជូនដំណឹង (Alert Switch)</span>
          </div>
        </div>
        <div class="panel-body">
          <div class="form-check form-switch fs-6">
            <input class="form-check-input" type="checkbox" name="low_stock_alert_enabled" id="alertSwitch" value="1" {{ $enabled ? 'checked' : '' }}>
            <label class="form-check-input-label fw-bold" for="alertSwitch">
              បើកដំណើរការ Low Stock Alert ពេលអ្នករាយការណ៍បំពេញ Daily Report
            </label>
          </div>
          <p class="text-muted fs-8 mt-2 mb-0">
            នៅពេលបើក ពេលអ្នករាយការណ៍ submit របាយការណ៍ស្តុក ប្រព័ន្ធនឹងពិនិត្យនិងសួរអំពីការផ្ញើសារទៅអ្នកដឹកនាំ។
          </p>
        </div>
      </div>

      {{-- Threshold Defaults --}}
      <div class="panel mb-4">
        <div class="panel-header">
          <div class="ph-title">
            <div class="ph-icon bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <span>កម្រិតស្តុកកំណត់ (Default Thresholds)</span>
          </div>
        </div>
        <div class="panel-body">
          <div class="mb-3">
            <label class="form-label fw-bold fs-8">កម្រិតស្តុកជិតអស់ទូទៅ (Default Low Stock Threshold)</label>
            <input type="number" step="0.1" name="low_stock_default_threshold" class="form-control" value="{{ $defaultThreshold }}" required>
            <span class="text-muted fs-8">កម្រិតកំណត់ជា default ប្រសិនបើ Material មិនបានកំណត់ threshold ដាច់ដោយឡែក។</span>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold fs-8">កម្រិត Critical (Default Critical Threshold)</label>
            <input type="number" step="0.1" name="low_stock_default_critical" class="form-control" value="{{ $defaultCritical }}">
            <span class="text-muted fs-8">ប្រសិនបើស្តុកនៅសល់ ≤ លេខនេះ ប្រព័ន្ធនឹងបង្ហាញ 🔴 Critical (ជិតអស់ខ្លាំង)។</span>
          </div>
        </div>
      </div>

      {{-- Destination Leader Groups --}}
      <div class="panel mb-4">
        <div class="panel-header">
          <div class="ph-title">
            <div class="ph-icon bg-info text-white"><i class="bi bi-people-fill"></i></div>
            <span>ជ្រើសរើស ក្រុមទទួលសារ (Leader / Group Destinations)</span>
          </div>
        </div>
        <div class="panel-body">
          <label class="form-label fw-bold fs-8 mb-2">ជ្រើសរើស Telegram Groups សម្រាប់ទទួលសារ Low Stock Alert (អាចជ្រើសច្រើន):</label>

          @if($telegramGroups->isEmpty())
            <div class="alert alert-warning fs-8">
              <i class="bi bi-exclamation-circle-fill me-1"></i>
              មិនទាន់មាន Telegram Group ក្នុងប្រព័ន្ធទេ។ សូមទៅកំណត់ក្នុង <a href="{{ route('telegram.setup') }}" class="fw-bold">Telegram Setup</a>
            </div>
          @else
            <div class="list-group shadow-sm border rounded" style="max-height: 280px; overflow-y: auto;">
              @foreach($telegramGroups as $group)
                <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2.5 border-bottom gap-2 cursor-pointer"
                       for="grp_{{ $group->id }}" style="cursor:pointer">
                  <div class="d-flex align-items-center gap-2.5 min-w-0" style="flex: 1;">
                    <input class="form-check-input flex-shrink-0 m-0"
                           type="checkbox"
                           name="group_ids[]"
                           value="{{ $group->id }}"
                           id="grp_{{ $group->id }}"
                           {{ in_array($group->id, $selectedGroupIds) ? 'checked' : '' }}
                           style="width: 1.1rem; height: 1.1rem; cursor: pointer;">
                    <div class="min-w-0">
                      <div class="fw-bold text-dark fs-8 text-truncate">
                        <i class="bi bi-telegram text-primary me-1"></i>
                        {{ $group->topic_name ?: $group->name }}
                      </div>
                      @if($group->parentGroup || $group->topic_name)
                        <div class="text-muted fs-9 text-truncate" style="font-size: .73rem">
                          {{ $group->parentGroup ? $group->parentGroup->name : $group->name }}
                        </div>
                      @endif
                    </div>
                  </div>
                  @if($group->purpose)
                    <span class="badge bg-light text-secondary border flex-shrink-0 fs-9 fw-normal ms-1" style="font-size: .7rem">
                      {{ $group->purpose }}
                    </span>
                  @endif
                </label>
              @endforeach
            </div>
          @endif
          <p class="text-muted fs-8 mt-2 mb-0">
            ប្រព័ន្ធនឹងផ្ញើសាររបាយការណ៍ស្តុកជិតអស់ទៅកាន់គ្រប់ Telegram Groups ដែលបាន tick ជ្រើសរើសខាងលើ។
          </p>
        </div>
      </div>

    </div>

    {{-- Right column: Message template editor --}}
    <div class="col-lg-6">
      <div class="panel h-100">
        <div class="panel-header">
          <div class="ph-title">
            <div class="ph-icon bg-success text-white"><i class="bi bi-chat-quote-fill"></i></div>
            <span>ទម្រង់សារ (Message Template Customization)</span>
          </div>
        </div>
        <div class="panel-body d-flex flex-direction-column">
          <label class="form-label fw-bold fs-8">កែសម្រួលទម្រង់សារប្រកាសទៅអ្នកដឹកនាំ (Telegram Template):</label>

          <textarea name="low_stock_message_template" id="templateInput" class="form-control fs-8 flex-grow-1" style="font-family:var(--font-khmer);font-size:.88rem;line-height:1.6;padding:.8rem" rows="12" placeholder="ទុកទំនេរដើម្បីប្រើទម្រង់ Default របស់ប្រព័ន្ធ">{{ $customTemplate }}</textarea>

          <div class="p-3 bg-light border rounded mt-3 fs-8">
            <strong class="d-block mb-1 text-primary"><i class="bi bi-code-slash me-1"></i> Placeholder variables ដែលអាចប្រើបាន:</strong>
            <ul class="mb-0 ps-3">
              <li><code>&#123;&#123;report_date&#125;&#125;</code> — កាលបរិច្ឆេទរបាយការណ៍</li>
              <li><code>&#123;&#123;reporter_name&#125;&#125;</code> — ឈ្មោះអ្នករាយការណ៍</li>
              <li><code>&#123;&#123;items_list&#125;&#125;</code> — បញ្ជីទំនិញជិតអស់ (បែងចែកតាម Category + Status Icon)</li>
            </ul>
          </div>
        </div>
        <div class="panel-footer p-3 border-top d-flex justify-content-end">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-circle-fill me-1"></i> រក្សាទុកការកំណត់ (Save Settings)
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection
