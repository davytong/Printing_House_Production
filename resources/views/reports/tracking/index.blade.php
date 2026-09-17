@extends('layouts.app')

@section('title', 'តាមដានរបាយការណ៍ប្រចាំថ្ងៃ — Daily Report Tracking')

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-primary"></i>
                <span>តាមដានរបាយការណ៍ប្រចាំថ្ងៃ</span>
                <span class="fs-6 text-muted fw-normal">(Daily Report Tracking)</span>
            </h1>
            <p class="text-muted mb-0 small">
                ត្រួតពិនិត្យការផ្ញើរបាយការណ៍ស្វ័យប្រវត្តិ ៣ ពេលប្រចាំថ្ងៃ (០៧:០០ AM, ០៣:១០ PM, ១១:៥០ PM) តាម Telegram
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Date Navigator -->
            <form method="GET" action="{{ route('reports.tracking.index') }}" class="d-flex align-items-center gap-1" id="dateNavForm">
                @if(request('staff')) <input type="hidden" name="staff" value="{{ request('staff') }}"> @endif
                @if(request('report_type')) <input type="hidden" name="report_type" value="{{ request('report_type') }}"> @endif
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

                <a href="{{ route('reports.tracking.index', array_merge(request()->query(), ['date' => $carbonDate->copy()->subDay()->toDateString()])) }}"
                   class="btn btn-outline-secondary btn-sm" title="ថ្ងៃមុន (Previous Day)">
                    <i class="bi bi-chevron-left"></i>
                </a>

                <input type="date" name="date" class="form-control form-control-sm text-center fw-semibold"
                       value="{{ $selectedDate }}" onchange="document.getElementById('dateNavForm').submit()"
                       style="min-width: 140px;">

                <a href="{{ route('reports.tracking.index', array_merge(request()->query(), ['date' => $carbonDate->copy()->addDay()->toDateString()])) }}"
                   class="btn btn-outline-secondary btn-sm" title="ថ្ងៃបន្ទាប់ (Next Day)">
                    <i class="bi bi-chevron-right"></i>
                </a>

                <a href="{{ route('reports.tracking.index', array_merge(request()->query(), ['date' => now('Asia/Phnom_Penh')->toDateString()])) }}"
                   class="btn btn-sm {{ $carbonDate->isToday() ? 'btn-primary' : 'btn-outline-primary' }}">
                    ថ្ងៃនេះ (Today)
                </a>
            </form>

            <div class="btn-group">
                <form action="{{ route('reports.tracking.check-now') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="ពិនិត្យ និងផ្ញើ Alert ឥឡូវនេះ">
                        <i class="bi bi-bell-fill me-1"></i>Check Alert Now
                    </button>
                </form>
                <form action="{{ route('reports.tracking.send-summary') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                    <button type="submit" class="btn btn-sm btn-outline-info" title="ផ្ញើសេចក្តីសង្ខេបទៅ Telegram">
                        <i class="bi bi-telegram me-1"></i>Send Summary
                    </button>
                </form>
                <a href="{{ route('reports.requirements.index') }}" class="btn btn-sm btn-dark">
                    <i class="bi bi-gear-fill me-1"></i>Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center py-2" role="alert">
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            <div>{{ session('info') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center py-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Required -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">របាយការណ៍សរុប (Required)</div>
                            <div class="fs-3 fw-bold mt-1 text-primary">{{ $totalRequired }}</div>
                        </div>
                        <div class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                            <i class="bi bi-card-checklist fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submitted On-time -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">ទាន់ពេល (On Time)</div>
                            <div class="fs-3 fw-bold mt-1 text-success">{{ $submittedCount }}</div>
                        </div>
                        <div class="badge bg-success-subtle text-success p-2 rounded-circle">
                            <i class="bi bi-check2-circle fs-5"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ $totalRequired > 0 ? ($submittedCount / $totalRequired) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Late -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">យឺត (Late)</div>
                            <div class="fs-3 fw-bold mt-1 text-warning-emphasis">{{ $lateCount }}</div>
                        </div>
                        <div class="badge bg-warning-subtle text-warning p-2 rounded-circle">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-warning" style="width: {{ $totalRequired > 0 ? ($lateCount / $totalRequired) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Missed -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">មិនបានផ្ញើ (Missed)</div>
                            <div class="fs-3 fw-bold mt-1 text-danger">{{ $missedCount }}</div>
                        </div>
                        <div class="badge bg-danger-subtle text-danger p-2 rounded-circle">
                            <i class="bi bi-x-circle fs-5"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-danger" style="width: {{ $totalRequired > 0 ? ($missedCount / $totalRequired) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-6 col-md-3 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small fw-medium">រង់ចាំ (Pending)</div>
                            <div class="fs-3 fw-bold mt-1 text-secondary">{{ $pendingCount }}</div>
                        </div>
                        <div class="badge bg-secondary-subtle text-secondary p-2 rounded-circle">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-secondary" style="width: {{ $totalRequired > 0 ? ($pendingCount / $totalRequired) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Matrix Schedule Tracking (Core 3 Daily Slots) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
            <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                <span>តារាងតាមដានតាមវេន ៣ ពេលប្រចាំថ្ងៃ (Daily Tracking Matrix)</span>
                <span class="badge bg-light text-dark border">{{ $carbonDate->format('d/m/Y') }}</span>
            </div>
            <div class="small text-muted">
                ចុចលើ Slot ដើម្បីពិនិត្យខ្លឹមសាររបាយការណ៍ (Click slot to view message)
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 200px;">បុគ្គលិក (Staff Member)</th>
                        <th class="text-center" style="min-width: 170px;">
                            <div>ពេលព្រឹក (Morning #1)</div>
                            <div class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-clock me-1"></i>07:00 AM
                            </div>
                        </th>
                        <th class="text-center" style="min-width: 170px;">
                            <div>ពេលរសៀល (Morning/Second #2)</div>
                            <div class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-clock me-1"></i>03:10 PM
                            </div>
                        </th>
                        <th class="text-center" style="min-width: 170px;">
                            <div>ពេលយប់ (Evening #3)</div>
                            <div class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-clock me-1"></i>11:50 PM
                            </div>
                        </th>
                        <th class="text-center" style="min-width: 130px;">លទ្ធផល (Summary)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffMatrix as $staff)
                        @php
                            $slot1 = $staff['slots']['07:00'] ?? null;
                            $slot2 = $staff['slots']['15:10'] ?? null;
                            $slot3 = $staff['slots']['23:50'] ?? null;

                            // Fallback for custom slots if deadline differs
                            if (!$slot1) {
                                foreach ($staff['slots'] as $time => $s) {
                                    if ($time < '12:00') { $slot1 = $s; break; }
                                }
                            }
                            if (!$slot2) {
                                foreach ($staff['slots'] as $time => $s) {
                                    if ($time >= '12:00' && $time < '18:00') { $slot2 = $s; break; }
                                }
                            }
                            if (!$slot3) {
                                foreach ($staff['slots'] as $time => $s) {
                                    if ($time >= '18:00') { $slot3 = $s; break; }
                                }
                            }

                            $completed = 0;
                            $totalSlots = count($staff['slots']);
                            foreach ($staff['slots'] as $s) {
                                if (in_array($s['status'], ['submitted', 'late'])) $completed++;
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $staff['staff_name'] }}</div>
                                <div class="small text-muted font-monospace">
                                    <i class="bi bi-telegram text-info me-1"></i>ID: {{ $staff['telegram_user_id'] }}
                                    @if($staff['telegram_username'])
                                        <span class="text-secondary">({{ '@' . $staff['telegram_username'] }})</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Slot 1: 07:00 AM -->
                            <td class="text-center">
                                @if($slot1)
                                    @include('reports.tracking._slot_cell', ['slot' => $slot1])
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <!-- Slot 2: 03:10 PM -->
                            <td class="text-center">
                                @if($slot2)
                                    @include('reports.tracking._slot_cell', ['slot' => $slot2])
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <!-- Slot 3: 11:50 PM -->
                            <td class="text-center">
                                @if($slot3)
                                    @include('reports.tracking._slot_cell', ['slot' => $slot3])
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <!-- Summary -->
                            <td class="text-center">
                                @if($totalSlots > 0 && $completed === $totalSlots)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-all me-1"></i>{{ $completed }}/{{ $totalSlots }} Full
                                    </span>
                                @elseif($completed > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        {{ $completed }}/{{ $totalSlots }} Partial
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        0/{{ $totalSlots }} Missed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                មិនទាន់មានកាលវិភាគរបាយការណ៍សម្រាប់ថ្ងៃនេះឡើយ (No report requirements configured for this date).
                                <div class="mt-2">
                                    <a href="{{ route('reports.requirements.index') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>បង្កើតកាលវិភាគថ្មី (Add Requirement)
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Submissions List -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-list-check text-primary"></i>
                <span>បញ្ជីលម្អិតនៃរបាយការណ៍ (Detailed Report Submissions)</span>
                <span class="badge bg-secondary-subtle text-secondary">{{ $dueRequirements->count() }} Items</span>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('reports.tracking.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <select name="staff" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="">-- បុគ្គលិកទាំងអស់ (All Staff) --</option>
                    @foreach($allStaff as $s)
                        <option value="{{ $s->telegram_user_id }}" {{ request('staff') == $s->telegram_user_id ? 'selected' : '' }}>
                            {{ $s->staff_name }}
                        </option>
                    @endforeach
                </select>

                <select name="report_type" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="">-- ប្រភេទ (All Types) --</option>
                    @foreach($reportTypes as $rt)
                        <option value="{{ $rt->report_type }}" {{ request('report_type') == $rt->report_type ? 'selected' : '' }}>
                            {{ $rt->report_title }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="">-- ស្ថានភាព (All Statuses) --</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted (On-time)</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                    <option value="missed" {{ request('status') == 'missed' ? 'selected' : '' }}>Missed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>

                @if(request()->hasAny(['staff', 'report_type', 'status']))
                    <a href="{{ route('reports.tracking.index', ['date' => $selectedDate]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>បុគ្គលិក (Staff)</th>
                        <th>របាយការណ៍ (Report)</th>
                        <th>Tag សម្គាល់ (Identifier Tag)</th>
                        <th class="text-center">កំណត់ម៉ោង (Deadline)</th>
                        <th class="text-center">ពេលផ្ញើ (Submitted)</th>
                        <th class="text-center">ស្ថានភាព (Status)</th>
                        <th class="text-end">សកម្មភាព (Actions)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dueRequirements as $req)
                        @php
                            $sub = $submissions->get($req->id);
                            $isFiltered = false;
                            if (request('status')) {
                                $currentStatus = $sub ? $sub->status : ($carbonDate->isToday() && now('Asia/Phnom_Penh')->lt($req->calculateDeadlineForDate($carbonDate)) ? 'pending' : 'missed');
                                if ($currentStatus !== request('status')) {
                                    $isFiltered = true;
                                }
                            }
                        @endphp

                        @if(!$isFiltered)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $req->staff_name }}</div>
                                <div class="small text-muted font-monospace">
                                    ID: {{ $req->telegram_user_id }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $req->report_title }}</div>
                                <span class="badge bg-light text-dark border">{{ $req->report_type }}</span>
                            </td>
                            <td>
                                <code class="text-primary fw-semibold">{{ $req->identifier_tag }}</code>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary font-monospace">
                                    <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::createFromFormat('H:i', $req->deadline_time)->format('h:i A') }}
                                </span>
                            </td>
                            <td class="text-center font-monospace">
                                @if($sub && $sub->submitted_at)
                                    <div>{{ $sub->formatted_submitted_at }}</div>
                                    @if($sub->revision_count > 0)
                                        <span class="badge bg-info-subtle text-info small">Rev #{{ $sub->revision_count }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($sub)
                                    {!! $sub->status_badge !!}
                                @elseif($carbonDate->isToday() && now('Asia/Phnom_Penh')->lt($req->calculateDeadlineForDate($carbonDate)))
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i>Pending
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-x-circle me-1"></i>Missed
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sub && $sub->message_text)
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="showReportModal({{ $sub->id }})">
                                        <i class="bi bi-eye me-1"></i>មើលរបាយការណ៍ (View)
                                    </button>
                                @else
                                    <span class="text-muted small">មិនទាន់មានទិន្នន័យ</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                មិនមានទិន្នន័យរបាយការណ៍ដែលត្រូវបង្ហាញ (No matching reports).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: View Report Submission Text -->
<div class="modal fade" id="reportViewModal" tabindex="-1" aria-labelledby="reportViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-body border-bottom">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="reportViewModalLabel">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span id="modalReportTitle">ខ្លឹមសាររបាយការណ៍ផលិតកម្ម</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-3 bg-light rounded-3 gap-2">
                    <div>
                        <div class="small text-muted">អ្នកផ្ញើ (Sender):</div>
                        <div class="fw-bold fs-6" id="modalStaffName">—</div>
                    </div>
                    <div>
                        <div class="small text-muted">ម៉ោងកំណត់ (Deadline):</div>
                        <div class="fw-semibold" id="modalDeadline">—</div>
                    </div>
                    <div>
                        <div class="small text-muted">ពេលផ្ញើជាក់ស្តែង (Submitted):</div>
                        <div class="fw-semibold text-primary" id="modalSubmittedAt">—</div>
                    </div>
                    <div>
                        <div class="small text-muted">ស្ថានភាព (Status):</div>
                        <div id="modalStatusBadge">—</div>
                    </div>
                </div>

                <div class="mb-2 fw-semibold small text-muted">ខ្លឹមសារសារ Telegram (Report Message Content):</div>
                <div class="p-3 bg-body-tertiary rounded-3 border font-monospace" style="white-space: pre-wrap; font-size: 0.95rem; max-height: 380px; overflow-y: auto;" id="modalMessageText">
                    កំពុងផ្ទុក...
                </div>
            </div>
            <div class="modal-footer bg-body border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បិទ (Close)</button>
            </div>
        </div>
    </div>
</div>

<script>
function showReportModal(submissionId) {
    const modalEl = document.getElementById('reportViewModal');
    const modal = new bootstrap.Modal(modalEl);

    document.getElementById('modalMessageText').innerText = 'កំពុងទាញយកទិន្នន័យ (Loading)...';
    modal.show();

    fetch(`/reports/tracking/submission/${submissionId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalStaffName').innerText = data.staff_name;
            document.getElementById('modalReportTitle').innerText = data.report_title;
            document.getElementById('modalDeadline').innerText = data.deadline_at;
            document.getElementById('modalSubmittedAt').innerText = data.submitted_at;
            document.getElementById('modalMessageText').innerText = data.message_text || '(Empty message)';

            let statusHtml = '';
            if (data.status === 'submitted') {
                statusHtml = '<span class="badge bg-success">ទាន់ពេល (On Time)</span>';
            } else if (data.status === 'late') {
                statusHtml = `<span class="badge bg-warning text-dark">យឺត ${data.late_minutes} នាទី (Late)</span>`;
            } else if (data.status === 'missed') {
                statusHtml = '<span class="badge bg-danger">មិនបានផ្ញើ (Missed)</span>';
            } else {
                statusHtml = '<span class="badge bg-secondary">រង់ចាំ (Pending)</span>';
            }
            if (data.revision_count > 0) {
                statusHtml += ` <span class="badge bg-info">Rev #${data.revision_count}</span>`;
            }
            document.getElementById('modalStatusBadge').innerHTML = statusHtml;
        })
        .catch(err => {
            document.getElementById('modalMessageText').innerText = 'មិនអាចទាញយកទិន្នន័យបានទេ: ' + err;
        });
}
</script>
@endsection