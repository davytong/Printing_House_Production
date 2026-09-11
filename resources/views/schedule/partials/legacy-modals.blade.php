{{-- ══════════════════════════════════════════════════════════════════
     LEGACY & SUPPORTING MODALS (100% Complete with Full Task Builder)
══════════════════════════════════════════════════════════════════ --}}

{{-- EDIT CELL MODAL --}}
<div class="modal fade modal-cell-edit" id="cellModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" id="cellForm" method="POST" action="{{ route('schedule.store') }}" style="border-radius: var(--radius-lg, 16px); border: none; max-height:95dvh;">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="process" id="cellProcess">
            <input type="hidden" name="day" id="cellDay">
            <input type="hidden" name="task" id="cellTaskHidden">
            
            <div class="modal-header" style="background: #f8fafc; border-radius: 16px 16px 0 0;">
                <h6 class="modal-title fw-bold" id="cellModalTitle">
                    <i class="bi bi-pencil-square text-primary me-2"></i>
                    កែប្រែកាលវិភាគ (Edit Cell)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding:1.25rem;">
                {{-- Process + Day (read-only) --}}
                <div class="d-flex gap-2 mb-3">
                    <div style="flex:1">
                        <label class="form-label mb-1 fs-xs fw-bold text-dark">ដំណើរការ</label>
                        <input type="text" class="form-control form-control-sm" id="cellProcessDisplay" readonly style="background:#f1f5f9;font-weight:700;">
                    </div>
                    <div style="flex:1">
                        <label class="form-label mb-1 fs-xs fw-bold text-dark">ថ្ងៃចាប់ផ្ដើម</label>
                        <input type="text" class="form-control form-control-sm" id="cellDayDisplay" readonly style="background:#f1f5f9;">
                    </div>
                </div>

                {{-- Task List --}}
                <label class="form-label mb-1 fs-xs fw-bold text-dark">
                    កិច្ចការ (Tasks)
                    <span class="text-muted fw-normal fs-xs">— add one or more, each with its own span</span>
                </label>
                <div id="taskBuilderList" style="display:flex;flex-direction:column;gap:0.5rem;"></div>

                <button type="button" id="btnAddTaskRow"
                        style="margin-top:.5rem;font-size:.78rem;background:none;border:1.5px dashed #94a3b8;
                               border-radius:6px;padding:.3rem .8rem;color:#64748b;cursor:pointer;width:100%;
                               transition:.15s;" onmouseover="this.style.borderColor='#4f46e5';this.style.color='#4f46e5';"
                        onmouseout="this.style.borderColor='#94a3b8';this.style.color='#64748b';">
                    <i class="bi bi-plus-circle"></i> បន្ថែមកិច្ចការ
                </button>

                {{-- Preview strip --}}
                <div id="taskPreviewStrip" style="margin-top:.6rem;display:none;">
                    <div style="font-size:.7rem;color:#64748b;margin-bottom:.25rem;">Preview — ថ្ងៃដែលត្រូវវាង:</div>
                    <div id="taskPreviewDays" style="display:flex;flex-wrap:wrap;gap:4px;"></div>
                </div>

                {{-- Note + Color --}}
                <div class="d-flex gap-2 mt-3">
                    <div style="flex:1">
                        <label class="form-label mb-1 fs-xs fw-bold text-dark">កំណត់ចំណាំ</label>
                        <input type="text" class="form-control form-control-sm" name="note" id="cellNote" placeholder="ចំណាំ...">
                    </div>
                    <div>
                        <label class="form-label mb-1 fs-xs fw-bold text-dark">ពណ៌</label>
                        <div class="d-flex flex-wrap gap-1" style="max-width:180px;">
                            @foreach($processColors as $proc => $clr)
                                <label class="color-option" style="cursor:pointer;">
                                    <input type="radio" name="color" value="{{ $clr }}" class="d-none color-radio">
                                    <span class="legend-dot" style="background:{{ $clr }};width:20px;height:20px;display:block;border-radius:3px;border:2px solid transparent;" title="{{ $proc }}"></span>
                                </label>
                            @endforeach
                            <label class="color-option" style="cursor:pointer;">
                                <input type="radio" name="color" value="" class="d-none color-radio" checked>
                                <span class="legend-dot" style="background:#e2e8f0;width:20px;height:20px;display:block;border-radius:3px;border:2px solid transparent;" title="Default"></span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Schedule Options --}}
                <div class="mt-3 pt-3" style="border-top: 1px dashed #e2e8f0;">
                    <div class="form-check form-switch mb-0" style="font-size:.78rem; font-weight: 600;">
                        <input class="form-check-input" type="checkbox" role="switch" name="include_sundays" id="includeSundays" value="1">
                        <label class="form-check-label" for="includeSundays" style="cursor:pointer;" title="If checked, tasks will be scheduled on Sundays instead of skipping them.">
                            រួមបញ្ចូលថ្ងៃអាទិត្យ (Include Sundays for Rush Jobs)
                        </label>
                    </div>
                </div>

                {{-- Copy to other processes --}}
                <div class="mt-3 pt-3" style="border-top: 1px dashed #e2e8f0;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0 fs-xs fw-bold text-dark">
                            ចម្លងទៅផ្នែកផ្សេងទៀត (Copy to other processes)
                        </label>
                        <div class="form-check form-switch mb-0" style="font-size:.75rem;">
                            <input class="form-check-input" type="checkbox" role="switch" name="copy_1_day" id="copy1Day" value="1" checked>
                            <label class="form-check-label text-muted" for="copy1Day" style="cursor:pointer;" title="If checked, ignores multi-day duration and copies as a 1-day task.">រយៈពេល ១ថ្ងៃ (1 day duration)</label>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2" id="copyProcessContainer">
                        @foreach($processes as $p)
                            <label class="btn btn-outline-secondary btn-sm process-checkbox-wrapper py-1 px-2" style="font-size:.75rem; border-radius: 20px;">
                                <input type="checkbox" name="copy_processes[]" value="{{ $p }}" class="d-none process-checkbox" onchange="this.parentElement.classList.toggle('btn-primary', this.checked); this.parentElement.classList.toggle('btn-outline-secondary', !this.checked); this.parentElement.classList.toggle('text-white', this.checked);">
                                {{ $p }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCell">
                    <i class="bi bi-trash3"></i> ជម្រះ
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="openMoveModal()">
                    <i class="bi bi-arrows-move"></i> ផ្លាស់ទី (Move)
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                <button type="submit" class="btn btn-sm btn-primary fw-bold" id="btnSaveCell">
                    <i class="bi bi-check-lg"></i> រក្សាទុក
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MOVE CELL MODAL --}}
<div class="modal fade" id="moveCellModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content" method="POST" action="{{ route('schedule.move') }}" style="border-radius: 16px; border: none;">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="from_process" id="moveFromProcess">
            <input type="hidden" name="from_day" id="moveFromDay">
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-arrows-move me-1"></i> ផ្លាស់ទីកិច្ចការ (Move Cell)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <p class="text-muted fs-xs mb-3">រំកិលកិច្ចការទាំងអស់ក្នុងប្រអប់នេះទៅទីតាំងថ្មីដោយស្វ័យប្រវត្តិ។</p>
                <div class="mb-2">
                    <label class="form-label fs-xs fw-bold">ផ្នែកថ្មី (New Process)</label>
                    <select name="to_process" id="moveToProcess" class="form-select form-select-sm" required>
                        @foreach($processes as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label fs-xs fw-bold">ថ្ងៃថ្មី (New Day)</label>
                    <input type="number" name="to_day" id="moveToDay" class="form-control form-control-sm" min="1" max="{{ $daysInMonth }}" required>
                </div>
            </div>
            <div class="modal-footer p-2 bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                <button type="submit" class="btn btn-sm btn-info text-white fw-bold">Confirm Move</button>
            </div>
        </form>
    </div>
</div>

{{-- COPY MONTH MODAL --}}
<div class="modal fade" id="copyMonthModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form class="modal-content" method="POST" action="{{ route('schedule.copy') }}" style="border-radius: 16px; border: none;">
            @csrf
            <input type="hidden" name="from_year" value="{{ $year }}">
            <input type="hidden" name="from_month" value="{{ $month }}">
            <input type="hidden" name="to_year" value="{{ $nextDate->year }}">
            <input type="hidden" name="to_month" value="{{ $nextDate->month }}">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold"><i class="bi bi-clipboard-plus me-1"></i> Copy ទៅខែបន្ទាប់</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <p class="fs-sm mb-2">Copy កាលវិភាគពី <strong>{{ $monthName }}</strong> ទៅ <strong>{{ $nextDate->translatedFormat('F Y') }}</strong>?</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="overwrite" id="copyOverwrite">
                    <label class="form-check-label fs-xs" for="copyOverwrite">សរសេរជាន់លើទិន្នន័យចាស់ (Overwrite existing)</label>
                </div>
            </div>
            <div class="modal-footer p-2 bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                <button type="submit" class="btn btn-sm btn-warning fw-bold">Copy</button>
            </div>
        </form>
    </div>
</div>

{{-- TELEGRAM ALERT MODAL --}}
<div class="modal fade" id="telegramAlertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('schedule.alert') }}" style="border-radius: 16px; border: none;">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="modal-header bg-info text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-telegram me-2"></i>ផ្ញើការជូនដំណឹងទៅ Telegram</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label class="form-label fs-xs fw-bold">ជ្រើសរើសថ្ងៃ (Select Day)</label>
                    <select name="day" class="form-select form-select-sm">
                        @foreach($days as $d)
                            <option value="{{ $d['day'] }}" {{ $d['day'] == now()->day ? 'selected' : '' }}>
                                {{ $d['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer p-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info btn-sm text-white fw-bold">Send to Telegram</button>
            </div>
        </form>
    </div>
</div>

{{-- URGENT TASK MODAL --}}
<div class="modal fade" id="urgentTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Urgent Task / ការងារបន្ទាន់</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fs-xs fw-bold">ផ្នែក (Process)</label>
                        <select class="form-select" id="urgentProcess">
                            @foreach($processes as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-xs fw-bold">ថ្ងៃបន្ទាន់ (Urgent Day)</label>
                        <input type="number" class="form-control" id="urgentDay" value="{{ now()->day }}" min="1" max="{{ $daysInMonth }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fs-xs fw-bold">ឈ្មោះកិច្ចការបន្ទាន់ (Task Name)</label>
                        <input type="text" class="form-control" id="urgentName" placeholder="e.g. VIP Printing Urgent Book">
                    </div>
                    <div class="col-12">
                        <label class="form-label fs-xs fw-bold">មូលហេតុ (Reason)</label>
                        <textarea class="form-control" id="urgentReason" rows="2" placeholder="e.g. ស្នើសុំដោយថ្នាក់ដឹកនាំ"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                <button type="button" class="btn btn-danger btn-sm fw-bold px-3" onclick="submitUrgentTask()">
                    <i class="bi bi-lightning-fill me-1"></i> បញ្ចូលការងារបន្ទាន់ (Insert Urgent)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- DOWNTIME MODAL --}}
<div class="modal fade" id="downtimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-tools me-2"></i>កត់ត្រាការខូចម៉ាស៊ីន / ផ្អាកការងារ (Machine Downtime Event)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fs-xs fw-bold">ផ្នែក (Process)</label>
                        <select class="form-select" id="downtimeProcess">
                            @foreach($processes as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-xs fw-bold">ថ្ងៃចាប់ផ្ដើមខូច</label>
                        <input type="number" class="form-control" id="downtimeDay" value="{{ now()->day }}" min="1" max="{{ $daysInMonth }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-xs fw-bold">ចំនួនថ្ងៃដែលខូច (Days)</label>
                        <input type="number" class="form-control" id="downtimeDays" value="1" min="1" max="14">
                    </div>
                    <div class="col-12">
                        <label class="form-label fs-xs fw-bold">មូលហេតុនៃការខូច (Downtime Reason)</label>
                        <textarea class="form-control" id="downtimeReason" rows="2" placeholder="e.g. ម៉ាស៊ីនកាត់គាំងភ្លើង / រង់ចាំគ្រឿងបន្លាស់"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                <button type="button" class="btn btn-warning btn-sm fw-bold px-3 text-dark" onclick="submitDowntime()">
                    <i class="bi bi-check-circle-fill me-1"></i> កត់ត្រា & រំកិលកាលវិភាគ (Log Downtime)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ══════════════════════════════════════════════════════════════════
// FULL TASK BUILDER JAVASCRIPT ENGINE (All Calculators & Steppers)
// ══════════════════════════════════════════════════════════════════

let _modalYear = {{ $year }}, _modalMonth = {{ $month }}, _modalStartDay = 1;
let _taskRows = [];
let _rowCounter = 0;

function isSunday(year, month, day) {
    return new Date(year, month - 1, day).getDay() === 0;
}

function dayName(year, month, day) {
    const d = new Date(year, month - 1, day);
    return ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][d.getDay()];
}

function stepInput(btn, delta) {
    const input = btn.parentElement.querySelector('input');
    if (!input) return;
    const current = parseFloat(input.value) || 0;
    const min = parseFloat(input.min) || 0;
    const max = parseFloat(input.max) || 999999;
    const next = Math.min(max, Math.max(min, current + delta));
    input.value = next > 0 ? next : '';
    input.dispatchEvent(new Event('input'));
}

function calcSpan(row) {
    const ch = parseFloat(row.totalChildren) || 0;
    const dy = parseFloat(row.dailyChildren) || 0;
    if (ch > 0 && dy > 0) return Math.max(1, Math.ceil(ch / dy));
    const m = parseInt(row.manualDays) || 0;
    return m > 0 ? m : 1;
}

function updateRowResult(el, row) {
    const span = calcSpan(row);
    const res  = el.querySelector('.tb-result');
    if (!res) return;
    const ch = parseFloat(row.totalChildren) || 0, dy = parseFloat(row.dailyChildren) || 0;
    if (ch > 0 && dy > 0) {
        res.style.cssText = 'min-width:52px;text-align:center;font-size:.82rem;font-weight:700;padding:4px 8px;border-radius:5px;border:1.5px solid #06b6d4;background:#ecfeff;color:#0e7490;';
        res.textContent = span + ' ថ្ងៃ';
    } else if (span > 1) {
        res.style.cssText = 'min-width:52px;text-align:center;font-size:.82rem;font-weight:700;padding:4px 8px;border-radius:5px;border:1.5px solid #86efac;background:#f0fdf4;color:#15803d;';
        res.textContent = span + ' ថ្ងៃ';
    } else {
        res.style.cssText = 'min-width:52px;text-align:center;font-size:.82rem;font-weight:700;padding:4px 8px;border-radius:5px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#94a3b8;';
        res.textContent = '1 ថ្ងៃ';
    }
}

function renderTaskRow(rowData) {
    const id = rowData.id;
    const el = document.createElement('div');
    el.className = 'task-builder-row';
    el.dataset.rowId = id;
    el.style.cssText = 'background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:.65rem .8rem;transition:border-color .15s;';

    const rowNum = _taskRows.findIndex(r => r.id === id) + 1 || _taskRows.length;

    el.innerHTML = `
        <div style="display:flex;gap:.4rem;align-items:center;margin-bottom:.5rem;">
            <span style="background:#e0e7ff;color:#4f46e5;border-radius:5px;padding:2px 7px;font-size:.68rem;font-weight:700;white-space:nowrap;flex-shrink:0;">#${rowNum}</span>
            <input type="text" class="form-control form-control-sm tb-name" value="${rowData.name || ''}" placeholder="ឈ្មោះកិច្ចការ..." autocomplete="off" style="flex:1;font-size:.9rem;min-height:36px;">
            <button type="button" onclick="duplicateTaskRow(${id})" style="background:none;border:none;color:#3b82f6;font-size:1.1rem;cursor:pointer;padding:4px 6px;line-height:1;border-radius:5px;flex-shrink:0;" title="ចម្លងកិច្ចការ (Duplicate)">
                <i class="bi bi-files"></i>
            </button>
            <button type="button" onclick="removeTaskRow(${id})" style="background:none;border:none;color:#ef4444;font-size:1.1rem;cursor:pointer;padding:4px 6px;line-height:1;border-radius:5px;flex-shrink:0;" title="លុបកិច្ចការ">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="tb-fields-grid">
            <div class="tb-field-group">
                <label class="tb-field-label">📅 ថ្ងៃ</label>
                <div class="input-group">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="stepInput(this, -1)" style="padding:0 8px;font-weight:bold;background:#fff;">-</button>
                    <input type="number" class="form-control form-control-sm tb-days" value="${rowData.manualDays || ''}" placeholder="—" min="1" max="60" style="text-align:center;font-weight:700;font-size:.85rem;padding:0 4px;">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="stepInput(this, 1)" style="padding:0 8px;font-weight:bold;background:#fff;">+</button>
                </div>
            </div>
            <div class="tb-or-divider">ឬ</div>
            <div class="tb-field-group">
                <label class="tb-field-label">📄 ចំនួនកូន</label>
                <div class="input-group">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="stepInput(this, -1)" style="padding:0 8px;font-weight:bold;background:#fff;">-</button>
                    <input type="number" class="form-control form-control-sm tb-children" value="${rowData.totalChildren || ''}" placeholder="100" min="0" style="text-align:center;font-size:.85rem;padding:0 4px;">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="stepInput(this, 1)" style="padding:0 8px;font-weight:bold;background:#fff;">+</button>
                </div>
            </div>
            <div class="tb-or-divider">÷</div>
            <div class="tb-field-group">
                <label class="tb-field-label">⚡ ក្នុង១ថ្ងៃ</label>
                <div class="input-group">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="stepInput(this, -1)" style="padding:0 8px;font-weight:bold;background:#fff;">-</button>
                    <input type="number" class="form-control form-control-sm tb-daily" value="${rowData.dailyChildren || ''}" placeholder="25" min="1" style="text-align:center;font-size:.85rem;padding:0 4px;">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="stepInput(this, 1)" style="padding:0 8px;font-weight:bold;background:#fff;">+</button>
                </div>
            </div>
            <div class="tb-or-divider">=</div>
            <div class="tb-field-group">
                <label class="tb-field-label">📊 គិត</label>
                <div class="tb-result">1 ថ្ងៃ</div>
            </div>
        </div>
    `;

    // Events
    el.querySelector('.tb-name').addEventListener('input', e => { rowData.name = e.target.value; rebuildPreview(); });
    el.querySelector('.tb-days').addEventListener('input', e => {
        rowData.manualDays = e.target.value;
        rowData.totalChildren = ''; rowData.dailyChildren = '';
        el.querySelector('.tb-children').value = '';
        el.querySelector('.tb-daily').value = '';
        updateRowResult(el, rowData);
        rebuildPreview();
    });
    el.querySelector('.tb-children').addEventListener('input', e => {
        rowData.totalChildren = e.target.value;
        rowData.manualDays = '';
        el.querySelector('.tb-days').value = '';
        updateRowResult(el, rowData);
        rebuildPreview();
    });
    el.querySelector('.tb-daily').addEventListener('input', e => {
        rowData.dailyChildren = e.target.value;
        rowData.manualDays = '';
        el.querySelector('.tb-days').value = '';
        updateRowResult(el, rowData);
        rebuildPreview();
    });

    updateRowResult(el, rowData);
    return el;
}

function addTaskRow(data = {}) {
    _rowCounter++;
    const rowData = {
        id: _rowCounter,
        name: data.name || '',
        manualDays: data.manualDays || '',
        totalChildren: data.totalChildren || '',
        dailyChildren: data.dailyChildren || ''
    };
    _taskRows.push(rowData);
    const el = renderTaskRow(rowData);
    document.getElementById('taskBuilderList').appendChild(el);
    rebuildPreview();
    setTimeout(() => el.querySelector('.tb-name')?.focus(), 50);
}

function duplicateTaskRow(id) {
    const src = _taskRows.find(r => r.id === id);
    if (!src) return;
    addTaskRow({
        name: src.name,
        manualDays: src.manualDays,
        totalChildren: src.totalChildren,
        dailyChildren: src.dailyChildren
    });
}

function removeTaskRow(id) {
    _taskRows = _taskRows.filter(r => r.id !== id);
    const el = document.querySelector(`.task-builder-row[data-row-id="${id}"]`);
    if (el) el.remove();
    document.querySelectorAll('.task-builder-row').forEach((row, i) => {
        const badge = row.querySelector('span[style*="e0e7ff"]');
        if (badge) badge.textContent = '#' + (i + 1);
    });
    rebuildPreview();
}

function parseStoredTasks(taskStr) {
    if (!taskStr) return [];
    const rows = [];
    const tokens = [];
    let depth = 0, cur = '';
    for (const ch of taskStr) {
        if (ch === '{') depth++;
        if (ch === '}') depth--;
        if (ch === ',' && depth === 0) { tokens.push(cur.trim()); cur = ''; }
        else cur += ch;
    }
    if (cur.trim()) tokens.push(cur.trim());

    tokens.forEach(t => {
        t = t.replace(/\s*\(\d+\/\d+\)/g, '').trim();
        const daysM  = t.match(/\[(\d+)d\]/);
        const childM = t.match(/\{([^}]+)\}/);
        let manualDays='', totalChildren='', dailyChildren='';
        if (childM) {
            const parts = childM[1].split('|');
            totalChildren = parts[0] || '';
            if (parts[1]) dailyChildren = parts[1].replace('/day','');
        } else if (daysM) {
            manualDays = daysM[1];
        }
        const name = t.replace(/\s*\[\d+d\]/g,'').replace(/\s*\{[^}]+\}/g,'').trim();
        if (name) rows.push({ name, manualDays, totalChildren, dailyChildren });
    });
    return rows;
}

function rebuildPreview() {
    const strip = document.getElementById('taskPreviewStrip');
    const cont  = document.getElementById('taskPreviewDays');
    if (!strip || !cont) return;

    const year = _modalYear, month = _modalMonth;
    const dim  = new Date(year, month, 0).getDate();
    let startDay = _modalStartDay;
    const incSundays = document.getElementById('includeSundays')?.checked || false;

    if (!incSundays) {
        while (startDay <= dim && isSunday(year, month, startDay)) startDay++;
    }

    const groups = [];
    _taskRows.forEach(row => {
        const span = calcSpan(row);
        const name = (row.name || '').trim();
        if (!name) return;

        const dayNumbers = [];
        let cur = startDay;
        while (dayNumbers.length < span && cur <= dim) {
            if (incSundays || !isSunday(year, month, cur)) dayNumbers.push(cur);
            cur++;
        }

        const colorInput = document.querySelector('input[name="color"]:checked');
        const clr = (colorInput && colorInput.value) ? colorInput.value : '#4f46e5';

        const dayChips = dayNumbers.map((d, i) =>
            `<span style="display:inline-flex;align-items:center;gap:2px;background:${clr};color:#fff;border-radius:4px;padding:2px 7px;font-size:.7rem;font-weight:600;">` +
            `${dayName(year,month,d)}&nbsp;${d}` +
            (span > 1 ? `<span style="opacity:.65;font-size:.62rem;">${i+1}/${span}</span>` : '') +
            `</span>`
        ).join('');

        groups.push(
            `<div style="display:flex;align-items:center;flex-wrap:wrap;gap:3px;margin-bottom:3px;">` +
            `<span style="font-size:.68rem;color:#64748b;white-space:nowrap;max-width:90px;overflow:hidden;text-overflow:ellipsis;" title="${name}">${name}:</span>` +
            dayChips +
            `</div>`
        );
    });

    if (groups.length) {
        cont.innerHTML = groups.join('');
        strip.style.display = 'block';
    } else {
        strip.style.display = 'none';
    }
    buildHiddenTask();
}

function buildHiddenTask() {
    const parts = [];
    _taskRows.forEach(row => {
        const span = calcSpan(row);
        const name = (row.name || '').trim();
        if (!name) return;
        let str = name;
        if (span > 1) str += ` [${span}d]`;
        const ch = parseFloat(row.totalChildren) || 0;
        const dy = parseFloat(row.dailyChildren) || 0;
        if (ch > 0 && dy > 0) str += ` {${ch}|${dy}/day}`;
        parts.push(str);
    });

    document.getElementById('cellTaskHidden').value = parts.join(', ');
}

function openCellModal(cell) {
    const process = cell.dataset.process;
    const day     = parseInt(cell.dataset.day);
    const task    = cell.dataset.task  || '';
    const note    = cell.dataset.note  || '';
    const color   = cell.dataset.color || '';

    _modalYear     = {{ $year }};
    _modalMonth    = {{ $month }};
    _modalStartDay = day;
    _taskRows      = [];
    _rowCounter    = 0;

    document.getElementById('cellProcess').value        = process;
    document.getElementById('cellDay').value            = day;
    document.getElementById('cellProcessDisplay').value = process;
    document.getElementById('cellDayDisplay').value     = String(day).padStart(2,'0') + '/{{ str_pad($month,2,"0",STR_PAD_LEFT) }}/{{ $year }}';
    document.getElementById('cellNote').value           = note;
    document.getElementById('taskBuilderList').innerHTML = '';

    // Color radios
    document.querySelectorAll('.modal-cell-edit .color-radio').forEach(r => {
        r.checked = (r.value === color);
        const dot = r.nextElementSibling;
        if (dot) dot.style.borderColor = r.checked ? '#0f172a' : 'transparent';
    });

    // Uncheck copy process checkboxes
    document.querySelectorAll('.process-checkbox').forEach(cb => {
        cb.checked = false;
        cb.parentElement.classList.remove('btn-primary', 'text-white');
        cb.parentElement.classList.add('btn-outline-secondary');
    });

    const rows = parseStoredTasks(task);
    if (rows.length) rows.forEach(r => addTaskRow(r));
    else addTaskRow();

    new bootstrap.Modal(document.getElementById('cellModal')).show();
}

document.getElementById('btnAddTaskRow')?.addEventListener('click', () => addTaskRow());
document.getElementById('includeSundays')?.addEventListener('change', () => rebuildPreview());
document.getElementById('btnClearCell')?.addEventListener('click', () => {
    document.getElementById('cellTaskHidden').value = '';
    document.getElementById('cellNote').value = '';
    document.getElementById('cellForm').submit();
});

// Color picker selection listener
document.querySelectorAll('.modal-cell-edit .color-radio').forEach(r => {
    r.addEventListener('change', () => {
        document.querySelectorAll('.modal-cell-edit .legend-dot').forEach(d => d.style.borderColor = 'transparent');
        if (r.checked && r.nextElementSibling) r.nextElementSibling.style.borderColor = '#0f172a';
        rebuildPreview();
    });
});

function openMoveModal() {
    bootstrap.Modal.getInstance(document.getElementById('cellModal'))?.hide();
    document.getElementById('moveFromProcess').value = document.getElementById('cellProcess').value;
    document.getElementById('moveFromDay').value     = document.getElementById('cellDay').value;
    document.getElementById('moveToProcess').value   = document.getElementById('cellProcess').value;
    document.getElementById('moveToDay').value       = document.getElementById('cellDay').value;
    new bootstrap.Modal(document.getElementById('moveCellModal')).show();
}

// ── Urgent / Downtime Submissions ──────────────────────────────────────
function submitUrgentTask() {
    const process = document.getElementById('urgentProcess').value;
    const day = document.getElementById('urgentDay').value;
    const name = document.getElementById('urgentName').value.trim();
    const reason = document.getElementById('urgentReason').value.trim();

    if (!name) { showToast('warning', 'Please enter task name'); return; }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("schedule.urgent") }}';
    const fields = {
        _token: document.querySelector('meta[name="csrf-token"]').content,
        year: '{{ $year }}', month: '{{ $month }}',
        process, urgent_day: day, duration_days: 1,
        task_name: name, note: 'URGENT', reason, mode: 'new'
    };
    Object.entries(fields).forEach(([k, v]) => {
        const i = document.createElement('input'); i.type='hidden'; i.name=k; i.value=v; form.appendChild(i);
    });
    document.body.appendChild(form);
    form.submit();
}

function submitDowntime() {
    const process = document.getElementById('downtimeProcess').value;
    const day = document.getElementById('downtimeDay').value;
    const days = document.getElementById('downtimeDays').value;
    const reason = document.getElementById('downtimeReason').value.trim();

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("schedule.downtime") }}';
    const fields = {
        _token: document.querySelector('meta[name="csrf-token"]').content,
        year: '{{ $year }}', month: '{{ $month }}',
        process, downtime_day: day, downtime_days: days,
        reason, affected_mode: 'shift'
    };
    Object.entries(fields).forEach(([k, v]) => {
        const i = document.createElement('input'); i.type='hidden'; i.name=k; i.value=v; form.appendChild(i);
    });
    document.body.appendChild(form);
    form.submit();
}
</script>
