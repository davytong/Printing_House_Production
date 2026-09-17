@extends('layouts.app')

@section('title', 'កំណត់កាលវិភាគរបាយការណ៍ — Report Requirements')

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-gear-wide-connected text-primary"></i>
                <span>ការកំណត់កាលវិភាគបុគ្គលិក</span>
                <span class="fs-6 text-muted fw-normal">(Report Requirements)</span>
            </h1>
            <p class="text-muted mb-0 small">
                គ្រប់គ្រងឈ្មោះបុគ្គលិក, Telegram User ID, Tag សម្គាល់, ម៉ោងកំណត់, និងគោលដៅ Alert
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('reports.tracking.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>ត្រឡប់ទៅផ្ទាំងតាមដាន (Tracking Dashboard)
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" id="btnScanUsersHeader" onclick="triggerFetchUsers(this)" title="ស្កេន និងទាញយកគណនីបុគ្គលិកពី Telegram Groups ស្វ័យប្រវត្តិ">
                <i class="bi bi-cloud-arrow-down-fill"></i>
                <span>ទាញយកគណនី Telegram ស្វ័យប្រវត្តិ</span>
                <span class="badge bg-primary text-white ms-1" id="scannedUsersCountBadge">{{ isset($telegramUsers) ? $telegramUsers->count() : 0 }}</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#createRequirementModal">
                <i class="bi bi-plus-circle me-1"></i>បន្ថែមបុគ្គលិកថ្មី (Add Requirement)
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Three Standard Schedules Reference Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-body">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border bg-light h-100">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary">Report 1</span>
                            <span class="fw-bold">ពេលព្រឹក (Morning)</span>
                        </div>
                        <div class="fs-4 fw-bold text-primary font-monospace">07:00 AM</div>
                        <div class="small text-muted mt-1">Default Tag: <code>[Morning Production Report]</code></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border bg-light h-100">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-info">Report 2</span>
                            <span class="fw-bold">ពេលរសៀល (Morning/Second)</span>
                        </div>
                        <div class="fs-4 fw-bold text-info font-monospace">03:10 PM (15:10)</div>
                        <div class="small text-muted mt-1">Default Tag: <code>[Second Production Report]</code></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-3 border bg-light h-100">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-dark">Report 3</span>
                            <span class="fw-bold">ពេលយប់ (Evening)</span>
                        </div>
                        <div class="fs-4 fw-bold text-dark font-monospace">11:50 PM (23:50)</div>
                        <div class="small text-muted mt-1">Default Tag: <code>[Evening Production Report]</code></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requirements Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
            <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-primary"></i>
                <span>បញ្ជីកាលវិភាគកំណត់របាយការណ៍បុគ្គលិក</span>
                <span class="badge bg-secondary-subtle text-secondary">{{ $requirements->count() }} Configured</span>
            </div>
            @if(isset($telegramUsers) && $telegramUsers->count() > 0)
                <div class="small text-muted d-none d-md-flex align-items-center gap-1">
                    <i class="bi bi-telegram text-primary"></i>
                    <span>ស្គាល់គណនី Telegram: <strong>{{ $telegramUsers->count() }} នាក់</strong></span>
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>បុគ្គលិក (Staff Name)</th>
                        <th>Telegram User ID</th>
                        <th>របាយការណ៍ (Report)</th>
                        <th>Tag សម្គាល់ (Identifier Tag)</th>
                        <th class="text-center">ម៉ោងកំណត់ (Deadline)</th>
                        <th>ថ្ងៃកំណត់ (Days)</th>
                        <th>គោលដៅ Alert</th>
                        <th class="text-center">ស្ថានភាព (Status)</th>
                        <th class="text-end">សកម្មភាព (Actions)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requirements as $req)
                        @php
                            $daysMap = ['mon' => 'Chn', 'tue' => 'Ang', 'wed' => 'Puth', 'thu' => 'Prh', 'fri' => 'Sok', 'sat' => 'Sau', 'sun' => 'Aty'];
                            $activeDays = $req->required_days ?: ['mon','tue','wed','thu','fri','sat'];
                            $isVerified = isset($telegramUsers) && $telegramUsers->firstWhere('telegram_user_id', $req->telegram_user_id);
                        @endphp
                        <tr class="{{ !$req->active ? 'opacity-50' : '' }}">
                            <td>
                                <div class="fw-bold">{{ $req->staff_name }}</div>
                                @if($req->telegram_username)
                                    <div class="small text-muted font-monospace">{{ '@' . $req->telegram_username }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <code class="fw-semibold">{{ $req->telegram_user_id }}</code>
                                    @if($isVerified)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle py-0 px-1" title="ស្គាល់គណនី Telegram នេះ (Auto-Matched)" style="font-size: 0.65rem;">
                                            <i class="bi bi-check-circle-fill"></i> Matched
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $req->report_title }}</div>
                                <span class="badge bg-light text-dark border">{{ $req->report_type }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ $req->identifier_tag }}
                                </span>
                            </td>
                            <td class="text-center font-monospace">
                                <span class="badge bg-secondary-subtle text-secondary fs-6">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::createFromFormat('H:i', $req->deadline_time)->format('h:i A') }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    @foreach(['mon','tue','wed','thu','fri','sat','sun'] as $d)
                                        <span class="badge {{ in_array($d, $activeDays) ? 'bg-success' : 'bg-light text-muted border' }}" style="font-size: 0.65rem;">
                                            {{ strtoupper($d) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($req->alert_chat_id)
                                    <span class="badge bg-light text-dark border font-monospace">
                                        Chat: {{ $req->alert_chat_id }}
                                        @if($req->alert_thread_id)
                                            (Topic: #{{ $req->alert_thread_id }})
                                        @endif
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        Default Group
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('reports.requirements.toggle', $req->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $req->active ? 'btn-success' : 'btn-outline-secondary' }} px-2 py-0" style="font-size: 0.75rem;">
                                        {{ $req->active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary"
                                            onclick='editRequirement(@json($req))'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('reports.requirements.destroy', $req->id) }}" method="POST"
                                          onsubmit="return confirm('តើអ្នកពិតជាចង់លុបកាលវិភាគនេះមែនទេ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                មិនទាន់មានកាលវិភាគបុគ្គលិកនៅឡើយទេ (No report requirements configured).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Requirement -->
<div class="modal fade" id="createRequirementModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('reports.requirements.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-body border-bottom">
                <h5 class="modal-title fw-bold" id="createModalLabel">
                    <i class="bi bi-plus-circle text-primary me-2"></i>បន្ថែមបុគ្គលិក និងកាលវិភាគថ្មី
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- Auto-Pick Telegram User Card -->
                    <div class="col-12">
                        <div class="p-3 bg-primary-subtle bg-opacity-25 rounded-3 border border-primary-subtle">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                                    <i class="bi bi-person-check-fill fs-5"></i>
                                    <span>ជ្រើសរើសគណនី Telegram ស្វ័យប្រវត្តិ (Auto-Pick Telegram User)</span>
                                </label>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill bg-white" style="font-size: 0.75rem;" onclick="triggerFetchUsers(this)">
                                    <i class="bi bi-arrow-repeat me-1"></i>ស្កេនពី Telegram ម្តងទៀត
                                </button>
                            </div>
                            <select id="create_auto_user_select" class="form-select user-picker-select font-monospace" onchange="onAutoUserSelect(this, 'create')">
                                <option value="">-- ចុចទីនេះដើម្បីជ្រើសរើសបុគ្គលិកពី Telegram (Select Scanned Staff) --</option>
                                @if(isset($telegramUsers))
                                    @foreach($telegramUsers as $u)
                                        <option value="{{ $u->telegram_user_id }}"
                                                data-name="{{ $u->display_name }}"
                                                data-username="{{ $u->username ?? '' }}">
                                            👤 {{ $u->display_name }} (ID: {{ $u->telegram_user_id }}) {{ $u->username ? '[@' . $u->username . ']' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text text-muted small mt-1">
                                <i class="bi bi-magic text-primary me-1"></i>ពេលជ្រើសរើស ប្រព័ន្ធនឹងបំពេញឈ្មោះបុគ្គលិក, Telegram User ID, និង Username ដោយស្វ័យប្រវត្តិ។
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ឈ្មោះបុគ្គលិក (Staff Name) <span class="text-danger">*</span></label>
                        <input type="text" name="staff_name" id="create_staff_name" class="form-control" required placeholder="e.g. Staff A (សុខា)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telegram User ID <span class="text-danger">*</span></label>
                        <input type="text" name="telegram_user_id" id="create_telegram_user_id" class="form-control font-monospace" required placeholder="e.g. 1234567890">
                        <div class="form-text">Telegram 64-bit Numeric User ID (អាចជ្រើសរើសពីប្រអប់ខាងលើ)</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telegram Username</label>
                        <input type="text" name="telegram_username" id="create_telegram_username" class="form-control" placeholder="e.g. sokha_press (Optional)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ឈ្មោះរបាយការណ៍ (Report Title) <span class="text-danger">*</span></label>
                        <input type="text" name="report_title" id="create_report_title" class="form-control" required value="Morning Production Report">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Identifier Tag <span class="text-danger">*</span></label>
                        <input type="text" name="identifier_tag" id="create_identifier_tag" class="form-control font-monospace" required value="[Morning Production Report]">
                        <div class="form-text">Tag ថេរដែលបុគ្គលិកត្រូវដាក់នៅដើមរបាយការណ៍ (Case-insensitive)</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ប្រភេទកូដ (Report Type Key) <span class="text-danger">*</span></label>
                        <select name="report_type" id="create_report_type" class="form-select">
                            <option value="morning_1">morning_1 (ពេលព្រឹក 07:00 AM)</option>
                            <option value="morning_2">morning_2 (ពេលរសៀល 03:10 PM)</option>
                            <option value="evening_3">evening_3 (ពេលយប់ 11:50 PM)</option>
                            <option value="custom">custom (កាលវិភាគផ្សេងទៀត)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                            <span>ម៉ោងកំណត់ Deadline (HH:MM) <span class="text-danger">*</span></span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="setPresetTime('create', '07:00', 'morning_1', 'Morning Production Report', '[Morning Production Report]')">07:00 AM</button>
                                <button type="button" class="btn btn-outline-info" onclick="setPresetTime('create', '15:10', 'morning_2', 'Second Production Report', '[Second Production Report]')">03:10 PM</button>
                                <button type="button" class="btn btn-outline-dark" onclick="setPresetTime('create', '23:50', 'evening_3', 'Evening Production Report', '[Evening Production Report]')">11:50 PM</button>
                            </div>
                        </label>
                        <input type="time" name="deadline_time" id="create_deadline_time" class="form-control font-monospace fs-5" required value="07:00">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">ថ្ងៃដែលត្រូវផ្ញើ (Required Working Days)</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(['mon' => 'ច័ន្ទ (Mon)', 'tue' => 'អង្គារ (Tue)', 'wed' => 'ពុធ (Wed)', 'thu' => 'ព្រហ (Thu)', 'fri' => 'សុក្រ (Fri)', 'sat' => 'សៅរ៍ (Sat)', 'sun' => 'អាទិត្យ (Sun)'] as $dayKey => $dayLabel)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_days[]" value="{{ $dayKey }}" id="create_day_{{ $dayKey }}"
                                           {{ $dayKey !== 'sun' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="create_day_{{ $dayKey }}">{{ $dayLabel }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-telegram text-primary me-1"></i>ជ្រើសរើសក្រុម/Topic ទទួល Alert (Choose Telegram Group/Topic)
                        </label>
                        <select id="create_alert_target_select" class="form-select" onchange="onReqTargetChange(this, 'create')">
                            <option value="">-- ប្រើប្រាស់ Default Alert Group ក្នុង .env / Settings --</option>
                            @if(isset($groupedChats))
                                @foreach($groupedChats as $cId => $chatGrps)
                                    <optgroup label="📢 {{ $chatGrps->first()->name ?? 'Group '.$cId }} (ID: {{ $cId }})">
                                        @foreach($chatGrps as $g)
                                            @php
                                                $val = $g->chat_id . '|' . ($g->message_thread_id ?? '');
                                                $disp = $g->message_thread_id ? ('↳ 📌 ' . ($g->topic_name ?: 'Topic #'.$g->message_thread_id) . ' (#' . $g->message_thread_id . ')') : ('👥 ' . $g->name . ' — General');
                                            @endphp
                                            <option value="{{ $val }}" data-chat="{{ $g->chat_id }}" data-thread="{{ $g->message_thread_id ?? '' }}">
                                                {{ $disp }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                        <input type="hidden" name="alert_chat_id" id="create_alert_chat_id">
                        <input type="hidden" name="alert_thread_id" id="create_alert_thread_id">
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" name="active" value="1" id="create_active" checked>
                            <label class="form-check-label fw-semibold" for="create_active">បើកដំណើរការតាមដាន (Active)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="send_ack" value="1" id="create_send_ack" checked>
                            <label class="form-check-label fw-semibold" for="create_send_ack">ផ្ញើវិក្កយបត្រទទួលពេលផ្ញើយឺត (Send Late Receipt on Telegram)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-body border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                <button type="submit" class="btn btn-primary btn-sm">រក្សាទុកកាលវិភាគ (Save Requirement)</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Requirement -->
<div class="modal fade" id="editRequirementModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="editRequirementForm" method="POST" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header bg-body border-bottom">
                <h5 class="modal-title fw-bold" id="editModalLabel">
                    <i class="bi bi-pencil-square text-primary me-2"></i>កែប្រែកាលវិភាគបុគ្គលិក
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- Auto-Pick Telegram User Card -->
                    <div class="col-12">
                        <div class="p-3 bg-primary-subtle bg-opacity-25 rounded-3 border border-primary-subtle">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                                    <i class="bi bi-person-check-fill fs-5"></i>
                                    <span>ជ្រើសរើសគណនី Telegram ឡើងវិញ (Switch Telegram User)</span>
                                </label>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill bg-white" style="font-size: 0.75rem;" onclick="triggerFetchUsers(this)">
                                    <i class="bi bi-arrow-repeat me-1"></i>ស្កេនពី Telegram
                                </button>
                            </div>
                            <select id="edit_auto_user_select" class="form-select user-picker-select font-monospace" onchange="onAutoUserSelect(this, 'edit')">
                                <option value="">-- ជ្រើសរើសគណនីបុគ្គលិកពី Telegram (Select User) --</option>
                                @if(isset($telegramUsers))
                                    @foreach($telegramUsers as $u)
                                        <option value="{{ $u->telegram_user_id }}"
                                                data-name="{{ $u->display_name }}"
                                                data-username="{{ $u->username ?? '' }}">
                                            👤 {{ $u->display_name }} (ID: {{ $u->telegram_user_id }}) {{ $u->username ? '[@' . $u->username . ']' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ឈ្មោះបុគ្គលិក (Staff Name) <span class="text-danger">*</span></label>
                        <input type="text" name="staff_name" id="edit_staff_name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telegram User ID <span class="text-danger">*</span></label>
                        <input type="text" name="telegram_user_id" id="edit_telegram_user_id" class="form-control font-monospace" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telegram Username</label>
                        <input type="text" name="telegram_username" id="edit_telegram_username" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ឈ្មោះរបាយការណ៍ (Report Title) <span class="text-danger">*</span></label>
                        <input type="text" name="report_title" id="edit_report_title" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Identifier Tag <span class="text-danger">*</span></label>
                        <input type="text" name="identifier_tag" id="edit_identifier_tag" class="form-control font-monospace" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ប្រភេទកូដ (Report Type Key) <span class="text-danger">*</span></label>
                        <input type="text" name="report_type" id="edit_report_type" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                            <span>ម៉ោងកំណត់ Deadline (HH:MM) <span class="text-danger">*</span></span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="setPresetTime('edit', '07:00')">07:00 AM</button>
                                <button type="button" class="btn btn-outline-info" onclick="setPresetTime('edit', '15:10')">03:10 PM</button>
                                <button type="button" class="btn btn-outline-dark" onclick="setPresetTime('edit', '23:50')">11:50 PM</button>
                            </div>
                        </label>
                        <input type="time" name="deadline_time" id="edit_deadline_time" class="form-control font-monospace fs-5" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">ថ្ងៃដែលត្រូវផ្ញើ (Required Working Days)</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(['mon' => 'ច័ន្ទ (Mon)', 'tue' => 'អង្គារ (Tue)', 'wed' => 'ពុធ (Wed)', 'thu' => 'ព្រហ (Thu)', 'fri' => 'សុក្រ (Fri)', 'sat' => 'សៅរ៍ (Sat)', 'sun' => 'អាទិត្យ (Sun)'] as $dayKey => $dayLabel)
                                <div class="form-check">
                                    <input class="form-check-input edit-day-checkbox" type="checkbox" name="required_days[]" value="{{ $dayKey }}" id="edit_day_{{ $dayKey }}">
                                    <label class="form-check-label small" for="edit_day_{{ $dayKey }}">{{ $dayLabel }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-telegram text-primary me-1"></i>ជ្រើសរើសក្រុម/Topic ទទួល Alert (Choose Telegram Group/Topic)
                        </label>
                        <select id="edit_alert_target_select" class="form-select" onchange="onReqTargetChange(this, 'edit')">
                            <option value="">-- ប្រើប្រាស់ Default Alert Group ក្នុង .env / Settings --</option>
                            @if(isset($groupedChats))
                                @foreach($groupedChats as $cId => $chatGrps)
                                    <optgroup label="📢 {{ $chatGrps->first()->name ?? 'Group '.$cId }} (ID: {{ $cId }})">
                                        @foreach($chatGrps as $g)
                                            @php
                                                $val = $g->chat_id . '|' . ($g->message_thread_id ?? '');
                                                $disp = $g->message_thread_id ? ('↳ 📌 ' . ($g->topic_name ?: 'Topic #'.$g->message_thread_id) . ' (#' . $g->message_thread_id . ')') : ('👥 ' . $g->name . ' — General');
                                            @endphp
                                            <option value="{{ $val }}" data-chat="{{ $g->chat_id }}" data-thread="{{ $g->message_thread_id ?? '' }}">
                                                {{ $disp }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                        <input type="hidden" name="alert_chat_id" id="edit_alert_chat_id">
                        <input type="hidden" name="alert_thread_id" id="edit_alert_thread_id">
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" name="active" value="1" id="edit_active">
                            <label class="form-check-label fw-semibold" for="edit_active">បើកដំណើរការតាមដាន (Active)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="send_ack" value="1" id="edit_send_ack">
                            <label class="form-check-label fw-semibold" for="edit_send_ack">ផ្ញើវិក្កយបត្រទទួលពេលផ្ញើយឺត</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-body border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                <button type="submit" class="btn btn-primary btn-sm">កែប្រែកាលវិភាគ (Update)</button>
            </div>
        </form>
    </div>
</div>

<script>
function setPresetTime(prefix, time, type = '', title = '', tag = '') {
    document.getElementById(`${prefix}_deadline_time`).value = time;
    if (type && document.getElementById(`${prefix}_report_type`)) {
        document.getElementById(`${prefix}_report_type`).value = type;
    }
    if (title && document.getElementById(`${prefix}_report_title`)) {
        document.getElementById(`${prefix}_report_title`).value = title;
    }
    if (tag && document.getElementById(`${prefix}_identifier_tag`)) {
        document.getElementById(`${prefix}_identifier_tag`).value = tag;
    }
}

function onReqTargetChange(select, prefix) {
    const opt = select.options[select.selectedIndex];
    const chatInput = document.getElementById(`${prefix}_alert_chat_id`);
    const threadInput = document.getElementById(`${prefix}_alert_thread_id`);
    if (chatInput) chatInput.value = opt.getAttribute('data-chat') || '';
    if (threadInput) threadInput.value = opt.getAttribute('data-thread') || '';
}

function onAutoUserSelect(select, prefix) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    const userId = opt.value;
    const name = opt.getAttribute('data-name') || '';
    const username = opt.getAttribute('data-username') || '';

    const staffNameInput = document.getElementById(`${prefix}_staff_name`);
    const userIdInput = document.getElementById(`${prefix}_telegram_user_id`);
    const usernameInput = document.getElementById(`${prefix}_telegram_username`);

    if (staffNameInput) staffNameInput.value = name;
    if (userIdInput) userIdInput.value = userId;
    if (usernameInput) usernameInput.value = username;
}

function triggerFetchUsers(btn) {
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Scanning...';

    fetch('{{ route("reports.requirements.fetch-users") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;

        if (data.success && data.users) {
            updateUserDropdowns(data.users);
            const countBadge = document.getElementById('scannedUsersCountBadge');
            if (countBadge) countBadge.textContent = data.users.length;
            showToast(data.message || `បានរកឃើញគណនី Telegram ចំនួន ${data.users.length} នាក់!`, 'success');
        } else {
            showToast(data.message || 'មិនអាចទាញយកបានទេ', 'danger');
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        showToast('មានបញ្ហាក្នុងការទាក់ទង Telegram API សូមពិនិត្យមើល Bot Token ឬ Network', 'danger');
    });
}

function updateUserDropdowns(users) {
    const selects = document.querySelectorAll('.user-picker-select');
    selects.forEach(select => {
        const currentVal = select.value;
        let html = '<option value="">-- ចុចទីនេះដើម្បីជ្រើសរើសបុគ្គលិកពី Telegram --</option>';
        users.forEach(u => {
            const uname = u.username ? ` [@${u.username}]` : '';
            html += `<option value="${u.telegram_user_id}" data-name="${u.display_name}" data-username="${u.username || ''}">👤 ${u.display_name} (ID: ${u.telegram_user_id})${uname}</option>`;
        });
        select.innerHTML = html;
        if (currentVal) {
            select.value = currentVal;
        }
    });
}

function showToast(msg, type = 'success') {
    let toastContainer = document.getElementById('toastNotificationContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastNotificationContainer';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '99999';
        document.body.appendChild(toastContainer);
    }

    const toastEl = document.createElement('div');
    toastEl.className = `alert alert-${type} alert-dismissible fade show shadow-lg d-flex align-items-center py-2 px-3 mb-2`;
    toastEl.role = 'alert';
    toastEl.innerHTML = `
        <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2 fs-5"></i>
        <div>${msg}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    toastContainer.appendChild(toastEl);

    setTimeout(() => {
        toastEl.classList.remove('show');
        setTimeout(() => toastEl.remove(), 300);
    }, 4500);
}

function editRequirement(req) {
    const form = document.getElementById('editRequirementForm');
    form.action = `/reports/requirements/${req.id}`;

    document.getElementById('edit_staff_name').value = req.staff_name;
    document.getElementById('edit_telegram_user_id').value = req.telegram_user_id;
    document.getElementById('edit_telegram_username').value = req.telegram_username || '';
    document.getElementById('edit_report_title').value = req.report_title;
    document.getElementById('edit_identifier_tag').value = req.identifier_tag;
    document.getElementById('edit_report_type').value = req.report_type;
    document.getElementById('edit_deadline_time').value = req.deadline_time;
    document.getElementById('edit_alert_chat_id').value = req.alert_chat_id || '';
    document.getElementById('edit_alert_thread_id').value = req.alert_thread_id || '';

    const editUserSelect = document.getElementById('edit_auto_user_select');
    if (editUserSelect) {
        editUserSelect.value = req.telegram_user_id || '';
    }

    const editTargetVal = (req.alert_chat_id ? req.alert_chat_id : '') + '|' + (req.alert_thread_id ? req.alert_thread_id : '');
    const editSelect = document.getElementById('edit_alert_target_select');
    if (editSelect) {
        editSelect.value = (req.alert_chat_id ? editTargetVal : '');
    }
    document.getElementById('edit_active').checked = Boolean(req.active);
    document.getElementById('edit_send_ack').checked = Boolean(req.send_ack);

    const activeDays = req.required_days || ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    document.querySelectorAll('.edit-day-checkbox').forEach(cb => {
        cb.checked = activeDays.includes(cb.value);
    });

    const modal = new bootstrap.Modal(document.getElementById('editRequirementModal'));
    modal.show();
}
</script>
@endsection