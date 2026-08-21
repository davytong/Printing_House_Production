{{-- DELAY LOG / WORK SUMMARY MODAL (inline, no new tab) --}}
<div class="modal fade modal-xl" id="delayLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;">
            <div class="modal-header" style="background:#fffbeb;border-radius:16px 16px 0 0;">
                <h6 class="modal-title">
                    <i class="bi bi-clipboard-data text-warning me-2"></i>
                    Monthly Work Summary
                    <span id="dlModalMonth" class="ms-2 text-muted" style="font-weight:400;font-size:.82rem;"></span>
                </h6>
                <div class="d-flex gap-2 ms-auto me-3">
                    <a id="dlFullPageBtn" href="{{ route('schedule.delay-report', ['year'=>$year,'month'=>$month]) }}"
                       class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Full Page
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="overflow-y:auto;padding:1.25rem;" id="delayLogBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <div class="mt-2 text-muted" style="font-size:.82rem;">Loading summary...</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- EDIT CELL MODAL --}}
<div class="modal fade modal-cell-edit" id="cellModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
            <div class="modal-header" style="background: #f8fafc; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h6 class="modal-title" id="cellModalTitle">
                    <i class="bi bi-pencil-square text-primary"></i>
                    កែប្រែកាលវិភាគ
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cellForm" method="POST" action="{{ route('schedule.store') }}">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="process" id="cellProcess">
                <input type="hidden" name="day" id="cellDay">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ដំណើរការ (Process)</label>
                        <input type="text" class="form-control" id="cellProcessDisplay" readonly style="background: #f1f5f9;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ថ្ងៃ (Day)</label>
                        <input type="text" class="form-control" id="cellDayDisplay" readonly style="background: #f1f5f9;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">កិច្ចការ (Task)</label>
                        
                        <!-- Task Builder -->
                        <div id="taskBuilder">
                            <!-- Task rows will be added here -->
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAddTask">
                            <i class="bi bi-plus-circle"></i> បន្ថែមកិច្ចការ (Add Task)
                        </button>
                        
                        <!-- Hidden input to store final task string -->
                        <input type="hidden" name="task" id="cellTask">
                        
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.5rem">
                            <i class="bi bi-info-circle"></i> បញ្ចូលថ្ងៃដោយផ្ទាល់ <strong>ឬ</strong> គណនាពីចំនួនកូន: ចំនួនកូន ÷ ក្នុង១ថ្ងៃ = ថ្ងៃ
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">កំណត់ចំណាំ (Note)</label>
                        <input type="text" class="form-control" name="note" id="cellNote"
                               placeholder="e.g. ថ្ងៃអាទិត្យ, ថ្ងៃឈប់សម្រាក...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ពណ៌ (Color)</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($stageColors ?? [] as $proc => $clr)
                                <label class="color-option" style="cursor:pointer;">
                                    <input type="radio" name="color" value="{{ $clr }}" class="d-none color-radio">
                                    <span class="legend-dot" style="background: {{ $clr }}; width:24px; height:24px; display:block; border-radius:4px; border: 2px solid transparent;" title="{{ $proc }}"></span>
                                </label>
                            @endforeach
                            <label class="color-option" style="cursor:pointer;">
                                <input type="radio" name="color" value="" class="d-none color-radio" checked>
                                <span class="legend-dot" style="background: #e2e8f0; width:24px; height:24px; display:block; border-radius:4px; border: 2px solid transparent;" title="Default"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCell">
                        <i class="bi bi-trash3"></i> ជម្រះ
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check-lg"></i> រក្សាទុក
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- COPY TO NEXT MONTH MODAL --}}
<div class="modal fade" id="copyMonthModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
            <div class="modal-header" style="background: #fef3c7; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h6 class="modal-title">
                    <i class="bi bi-clipboard-plus text-warning"></i>
                    Copy ទៅខែបន្ទាប់
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('schedule.copy') }}" method="POST">
                @csrf
                <input type="hidden" name="from_year" value="{{ $year }}">
                <input type="hidden" name="from_month" value="{{ $month }}">
                <input type="hidden" name="to_year" value="{{ $nextDate->year }}">
                <input type="hidden" name="to_month" value="{{ $nextDate->month }}">
                <div class="modal-body" style="padding: 1.5rem;">
                    <p style="font-size:.85rem; margin-bottom: 1rem;">
                        Copy កាលវិភាគពី <strong>{{ $monthName }}</strong> ទៅ <strong>{{ $nextDate->translatedFormat('F Y') }}</strong>?
                    </p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="overwrite" id="copyOverwrite">
                        <label class="form-check-label" for="copyOverwrite" style="font-size:.8rem;">
                            សរសេរជាន់លើទិន្នន័យចាស់ (Overwrite existing)
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="bi bi-clipboard-plus"></i> Copy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TELEGRAM ALERT MODAL (select group + day) --}}
<div class="modal fade" id="telegramAlertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-lg); border: none;">
            <div class="modal-header" style="background: #e0f7fa; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h6 class="modal-title">
                    <i class="bi bi-telegram text-info"></i>
                    ផ្ញើការជូនដំណឹងទៅ Telegram
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('schedule.alert') }}" method="POST">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600; font-size:.82rem;">ជ្រើសរើសថ្ងៃ (Select Day)</label>
                        <select name="day" class="form-select">
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                <option value="{{ $d }}" {{ $d == now()->day ? 'selected' : '' }}>
                                    {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}
                                    @if($d == now()->day) — ថ្ងៃនេះ @endif
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600; font-size:.82rem;">ជ្រើសរើស Group (Select Telegram Group)</label>
                        @php $telegramGroups = \App\Models\TelegramGroup::all(); @endphp
                        @if($telegramGroups->count() > 0)
                            <select name="group_id" class="form-select">
                                <option value="all">📢 ផ្ញើទៅគ្រប់ Group ទាំងអស់</option>
                                @foreach($telegramGroups as $group)
                                    <option value="{{ $group->id }}">
                                        {{ $group->displayLabel() }}
                                        @if($group->purpose) ({{ $group->purpose }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert alert-warning mb-0" style="font-size:.8rem;">
                                <i class="bi bi-exclamation-triangle"></i>
                                មិនទាន់មាន Telegram Group។ សូមទៅ <a href="{{ route('telegram.setup') }}">ការកំណត់ Telegram</a> ដើម្បីបន្ថែម។
                            </div>
                        @endif
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-weight:600; font-size:.82rem;">មើលជាមុន (Preview)</label>
                        <div class="p-2 rounded" style="background:#f1f5f9; font-size:.75rem; max-height:150px; overflow-y:auto;" id="alertPreview">
                            <em class="text-muted">ជ្រើសរើសថ្ងៃដើម្បីមើលកិច្ចការ...</em>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-info text-white" {{ $telegramGroups->count() == 0 ? 'disabled' : '' }}>
                        <i class="bi bi-send"></i> ផ្ញើ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



{{-- URGENT TASK MODAL --}}
<div class="modal fade" id="urgentTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 25px 50px -12px rgba(220, 38, 38, 0.25); background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
            <div class="modal-header" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-radius: 20px 20px 0 0; border-bottom: 1px solid #fecaca; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight: 800; color: #991b1b; letter-spacing: -0.01em;"><i class="bi bi-exclamation-triangle-fill text-danger me-2" style="font-size: 1.2rem;"></i> Urgent Task <span style="font-weight: 400; opacity: 0.7;">/ ការងារបន្ទាន់</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 1.75rem;">
                {{-- Mode selector --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Mode</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="urgentMode" id="modeNew" value="new" checked onchange="toggleUrgentMode()">
                            <label class="form-check-label" for="modeNew">
                                <strong>New Urgent Work</strong> — New job arrives, push planned work forward
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="urgentMode" id="modeExisting" value="existing" onchange="toggleUrgentMode()">
                            <label class="form-check-label" for="modeExisting">
                                <strong>Existing Work is Urgent</strong> — Move already-planned work to an earlier day
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Process *</label>
                        <select class="form-select" id="urgentProcess">
                            <option value="Design">Design</option>
                            <option value="Press" selected>Press</option>
                            <option value="Digital">Digital</option>
                            <option value="Folding">Folding</option>
                            <option value="Gathering">Gathering</option>
                            <option value="Staple">Staple</option>
                            <option value="Binding">Binding</option>
                            <option value="Cutting">Cutting</option>
                            <option value="Packaging">Packaging</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="urgentDayWrap">
                        <label class="form-label" id="urgentDayLabel">Start Day (in this month) *</label>
                        <input type="number" class="form-control" id="urgentDay" value="{{ now()->day }}"
                               min="1" max="{{ $daysInMonth }}" style="font-family:var(--font-latin)">
                    </div>
                </div>

                {{-- New mode fields --}}
                <div id="newModeFields">
                    <div class="mb-3">
                        <label class="form-label">Task Name *</label>
                        <input type="text" class="form-control" id="urgentName"
                               placeholder="e.g. Emergency Reprint Level 1" list="taskSuggestions2">
                        <datalist id="taskSuggestions2">
                            <option value="Listening Textbook"><option value="Listening Workbook">
                            <option value="Reading Textbook"><option value="Reading Workbook">
                            <option value="Writing Textbook"><option value="Test Book">
                            <option value="All Cover"><option value="Song">
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (working days) *</label>
                        <input type="number" class="form-control" id="urgentDuration" value="1" min="1" max="30"
                               style="font-family:var(--font-latin);width:100px">
                    </div>
                </div>

                {{-- Existing mode fields --}}
                <div id="existingModeFields" style="display:none">
                    <div class="mb-3">
                        <label class="form-label">Which task to make urgent? *</label>
                        <input type="text" class="form-control" id="urgentExistingName"
                               placeholder="Type exact task name as on the grid (e.g. Listening Textbook)">
                        <div class="form-text">This task will be moved to the day above. Work between that day and its current position will shift forward.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason / Why urgent?</label>
                    <input type="text" class="form-control" id="urgentReason"
                           placeholder="e.g. Customer order, Deadline moved up">
                </div>

                <div style="background: linear-gradient(135deg, #fef2f2 0%, #fff 100%); border: 1px solid #fca5a5; border-radius: 12px; padding: 1rem; font-size: .85rem; color: #991b1b; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.05);">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2 mt-1" style="font-size: 1.1rem;"></i>
                        <div>
                            <strong style="display:block; margin-bottom: 0.2rem;">Auto-shift Enabled</strong>
                            Tasks on the same process that are displaced will automatically move to the next available working day, and a delay log is recorded for the monthly report.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1.25rem 1.75rem; border-top: 1px solid #e2e8f0; background: rgba(248, 250, 252, 0.5); border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitUrgentTask()" style="border-radius: 8px; font-weight: 600; padding: 0.55rem 1.25rem; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Apply Urgent
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MACHINE DOWNTIME MODAL --}}
<div class="modal fade" id="downtimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 25px 50px -12px rgba(245, 158, 11, 0.25); background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);">
            <div class="modal-header" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 20px 20px 0 0; border-bottom: 1px solid #fde68a; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title" style="font-weight: 800; color: #92400e; letter-spacing: -0.01em;"><i class="bi bi-tools text-warning me-2" style="font-size: 1.2rem;"></i> Machine Downtime <span style="font-weight: 400; opacity: 0.7;">/ ម៉ាស៊ីនខូច</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 1.75rem;">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Process (Row) *</label>
                        <select class="form-select" id="downtimeProcess">
                            <option value="Design">Design</option>
                            <option value="Press" selected>Press</option>
                            <option value="Digital">Digital</option>
                            <option value="Folding">Folding</option>
                            <option value="Gathering">Gathering</option>
                            <option value="Staple">Staple</option>
                            <option value="Binding">Binding</option>
                            <option value="Cutting">Cutting</option>
                            <option value="Packaging">Packaging</option>
                            <option value="Delivery">Delivery</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="form-text">Which production row is affected?</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Day (in this month) *</label>
                        <input type="number" class="form-control" id="downtimeDay" value="{{ now()->day }}"
                               min="1" max="{{ $daysInMonth }}" style="font-family:var(--font-latin)">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Downtime Duration (working days) *</label>
                        <input type="number" class="form-control" id="downtimeDays" value="1" min="1" max="30"
                               style="font-family:var(--font-latin)">
                        <div class="form-text">1 day = 1 full working day blocked</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Machine (for record)</label>
                        <select class="form-select" id="downtimeMachine">
                            <option value="">— None / Unknown —</option>
                            @foreach(\App\Models\Machine::all() as $m)
                                <option value="{{ $m->id }}">{{ $m->code }} — {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason *</label>
                    <input type="text" class="form-control" id="downtimeReason"
                           placeholder="e.g. Press breakdown, Maintenance, Power outage">
                </div>
                <div class="mb-3">
                    <label class="form-label">Rescheduling Strategy</label>
                    <select class="form-select" id="downtimeStrategy">
                        <option value="shift_workflow" selected>Shift Entire Workflow (Recommended)</option>
                        <option value="shift_single">Shift Only This Process</option>
                    </select>
                </div>
                <div style="background: linear-gradient(135deg, #fffbeb 0%, #fff 100%); border: 1px solid #fde68a; border-radius: 12px; padding: 1rem; font-size: .85rem; color: #92400e; box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.05);">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2 mt-1" style="font-size: 1.1rem;"></i>
                        <div>
                            <strong style="display:block; margin-bottom: 0.2rem;">Cascade Shift Enabled</strong>
                            Depending on the strategy, tasks on the selected process row (and potentially downstream processes) from this day forward will shift by the downtime duration. A delay log entry is recorded for each shifted task.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1.25rem 1.75rem; border-top: 1px solid #e2e8f0; background: rgba(248, 250, 252, 0.5); border-radius: 0 0 20px 20px; flex-wrap: nowrap;">
                <a href="{{ route('schedule.delay-report', ['year'=>$year,'month'=>$month]) }}" class="btn btn-outline-secondary me-auto" style="border-radius: 8px; font-weight: 600; padding: 0.55rem 1rem;">
                    <i class="bi bi-journal-text me-1"></i> View Log
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitDowntime()" style="border-radius: 8px; font-weight: 600; padding: 0.55rem 1.25rem; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                    <i class="bi bi-tools me-1"></i> Apply Downtime
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    // Cell edit modal
    function openCellModal(cell) {
        const process = cell.dataset.process;
        const day = cell.dataset.day;
        const task = cell.dataset.task;
        const note = cell.dataset.note;
        const color = cell.dataset.color;

        document.getElementById('cellProcess').value = process;
        document.getElementById('cellDay').value = day;
        document.getElementById('cellProcessDisplay').value = process;
        document.getElementById('cellDayDisplay').value = day + '/{{ str_pad($month, 2, "0", STR_PAD_LEFT) }}/{{ $year }}';
        document.getElementById('cellTask').value = task;
        document.getElementById('cellNote').value = note;

        document.querySelectorAll('.color-radio').forEach(r => {
            r.checked = (r.value === color);
            r.closest('.color-option').querySelector('.legend-dot').style.borderColor =
                (r.value === color) ? '#4f46e5' : 'transparent';
        });

        const modal = new bootstrap.Modal(document.getElementById('cellModal'));
        modal.show();
    }

    // Color selection visual feedback
    document.querySelectorAll('.color-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.color-option .legend-dot').forEach(dot => {
                dot.style.borderColor = 'transparent';
            });
            this.closest('.color-option').querySelector('.legend-dot').style.borderColor = '#4f46e5';
        });
    });

    // Clear cell
    document.getElementById('btnClearCell').addEventListener('click', function() {
        document.getElementById('cellTask').value = '';
        document.getElementById('cellNote').value = '';
        document.querySelectorAll('.color-radio').forEach(r => r.checked = r.value === '');
        document.getElementById('cellForm').submit();
    });

    // Print / Export PDF — opens browser print dialog (save as PDF)
    function exportCalendarPDF() {
        const grid = document.querySelector('.schedule-container');
        const printWin = window.open('', '_blank');
        printWin.document.write(`
            <html>
            <head>
                <title>Production Schedule — {{ $monthName }}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; }
                    h1 { font-size: 18px; margin-bottom: 10px; text-align: center; }
                    .subtitle { text-align: center; font-size: 12px; color: #666; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; font-size: 9px; }
                    th, td { border: 1px solid #ccc; padding: 4px 3px; text-align: center; }
                    th { background: #f0f0f0; font-weight: 600; }
                    td.process { text-align: left; font-weight: 700; background: #fafafa; width: 80px; }
                    .task-badge { padding: 1px 4px; border-radius: 3px; color: #fff; font-size: 8px; display: inline-block; }
                    .weekend { background: #fffde7; }
                    @media print {
                        @page { size: landscape; margin: 10mm; }
                    }
                </style>
            </head>
            <body>
                <h1>📅 Production Schedule — {{ $monthName }}</h1>
                <p class="subtitle">Printing Tracker | Generated: ${new Date().toLocaleDateString()}</p>
                ${grid.querySelector('.schedule-grid-wrapper').innerHTML}
                <script>window.print(); window.close();<\/script>
            </body>
            </html>
        `);
        printWin.document.close();
    }

    // Telegram alert preview: show tasks for selected day
    @php
        $allMonthTasks = \App\Models\ProductionSchedule::where('year', $year)
            ->where('month', $month)
            ->whereNotNull('task')
            ->get()
            ->sortBy(fn($t) => array_search($t->process, $processes))
            ->groupBy('day')
            ->map(fn($tasks) => $tasks->values()->map(fn($t) => $t->process . ': ' . $t->task)->toArray());
    @endphp
    const monthTasks = @json($allMonthTasks);

    document.querySelector('select[name="day"]')?.addEventListener('change', function() {
        const day = this.value;
        const preview = document.getElementById('alertPreview');
        const tasks = monthTasks[day];
        if (tasks && tasks.length > 0) {
            preview.innerHTML = tasks.map(t => `<div>• ${t}</div>`).join('');
        } else {
            preview.innerHTML = '<em class="text-muted">គ្មានកិច្ចការសម្រាប់ថ្ងៃនេះ</em>';
        }
    });

    // Trigger preview on load
    document.addEventListener('DOMContentLoaded', function() {
        const daySelect = document.querySelector('select[name="day"]');
        if (daySelect) daySelect.dispatchEvent(new Event('change'));
    });

// ── Scroll to Today column on page load ──────────────────
document.addEventListener('DOMContentLoaded', function() {
    const todayCol = document.getElementById('today-col');
    if (todayCol) {
        const wrapper = document.querySelector('.schedule-grid-wrapper');
        if (wrapper) {
            // Calculate scroll position: center today in viewport
            const colLeft = todayCol.offsetLeft;
            const wrapperWidth = wrapper.clientWidth;
            const scrollTo = colLeft - (wrapperWidth / 3);
            wrapper.scrollLeft = Math.max(0, scrollTo);
        }
    }
});


// ── Urgent Task — operates on production_schedules grid ──────────────────
function toggleUrgentMode() {
    const mode = document.querySelector('input[name="urgentMode"]:checked').value;
    document.getElementById('newModeFields').style.display = (mode === 'new') ? '' : 'none';
    document.getElementById('existingModeFields').style.display = (mode === 'existing') ? '' : 'none';
    document.getElementById('urgentDayLabel').textContent =
        (mode === 'existing') ? 'Move TO this day *' : 'Start Day (in this month) *';
}

function submitUrgentTask() {
    const mode    = document.querySelector('input[name="urgentMode"]:checked').value;
    const process = document.getElementById('urgentProcess').value;
    const day     = parseInt(document.getElementById('urgentDay').value);
    const reason  = document.getElementById('urgentReason').value;

    if (!process || !day) {
        showToast('warning', 'Please select process and day');
        return;
    }

    let taskName = '';
    let duration = 1;

    if (mode === 'new') {
        taskName = document.getElementById('urgentName').value.trim();
        duration = parseInt(document.getElementById('urgentDuration').value) || 1;
        if (!taskName) { showToast('warning', 'Please enter a task name'); return; }
    } else {
        taskName = document.getElementById('urgentExistingName').value.trim();
        if (!taskName) { showToast('warning', 'Please enter the task name to make urgent'); return; }
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/schedule/urgent';

    const fields = {
        _token:        document.querySelector('meta[name="csrf-token"]').content,
        year:          '{{ $year }}',
        month:         '{{ $month }}',
        process:       process,
        urgent_day:    day,
        duration_days: duration,
        task_name:     taskName,
        note:          'URGENT',
        reason:        reason,
        mode:          mode,
    };

    Object.entries(fields).forEach(([k, v]) => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = v;
        form.appendChild(inp);
    });

    document.body.appendChild(form);
    form.submit();
}

// ── Machine Downtime — operates on production_schedules grid ──────────────
function submitDowntime() {
    const process  = document.getElementById('downtimeProcess').value;
    const day      = parseInt(document.getElementById('downtimeDay').value);
    const days     = parseInt(document.getElementById('downtimeDays').value) || 1;
    const reason   = document.getElementById('downtimeReason').value.trim();
    const machine  = document.getElementById('downtimeMachine').value;
    const strategy = document.getElementById('downtimeStrategy').value;

    if (!process || !day) {
        showToast('warning', 'Please select process and start day');
        return;
    }
    if (!reason) {
        showToast('warning', 'Please provide a reason for the downtime');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/schedule/downtime';

    const fields = {
        _token:         document.querySelector('meta[name="csrf-token"]').content,
        year:           '{{ $year }}',
        month:          '{{ $month }}',
        process:        process,
        downtime_day:   day,
        downtime_days:  days,
        reason:         reason,
        machine_id:     machine,
        strategy:       strategy
    };

    Object.entries(fields).forEach(([k, v]) => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = v;
        form.appendChild(inp);
    });

    document.body.appendChild(form);
    form.submit();
}


// ── Cell click: batch mode vs normal edit ────────────────────────────────
function handleCellClick(e, cell) {
    if (document.body.classList.contains("batch-mode")) {
        const cb = cell.querySelector(".batch-check");
        if (cb) { cb.checked = !cb.checked; updateBatchCount(); }
        return;
    }
    openCellModal(cell);
}

// ── Batch mode toggle ─────────────────────────────────────────────────────
let _batchActive = false;

function toggleBatchMode() {
    _batchActive = !_batchActive;
    document.body.classList.toggle("batch-mode", _batchActive);
    const btn = document.getElementById("batchModeBtn");
    if (!btn) return;
    if (_batchActive) {
        btn.classList.remove("btn-outline-success");
        btn.classList.add("btn-success");
        btn.innerHTML = '<i class="bi bi-x-square"></i> <span class="d-none d-md-inline ms-1">Exit Mark</span>';
    } else {
        btn.classList.remove("btn-success");
        btn.classList.add("btn-outline-success");
        btn.innerHTML = '<i class="bi bi-check2-square"></i> <span class="d-none d-md-inline ms-1">Mark</span>';
        document.querySelectorAll(".batch-check").forEach(cb => cb.checked = false);
        updateBatchCount();
    }
}

function updateBatchCount() {
    const n = document.querySelectorAll(".batch-check:checked").length;
    const el = document.getElementById("batchCount");
    if (el) el.textContent = n + " selected";
}

document.addEventListener("change", function(e) {
    if (e.target.classList.contains("batch-check")) updateBatchCount();
});

// ── Apply batch status to selected cells ─────────────────────────────────
function applyBatchStatus(status) {
    const checks = document.querySelectorAll(".batch-check:checked");
    if (!checks.length) { showToast("warning", "No cells selected"); return; }

    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const year  = "{{ $year }}";
    const month = "{{ $month }}";

    const promises = Array.from(checks).map(cb => {
        return fetch("/schedule/status", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json"
            },
            body: JSON.stringify({
                year: year, month: month,
                process: cb.dataset.process,
                day: cb.dataset.day,
                status: status
            })
        }).then(r => r.json());
    });

    Promise.all(promises).then(results => {
        const ok = results.filter(r => r.ok).length;
        const fail = results.length - ok;
        if (fail > 0) {
            showToast("warning", ok + " updated, " + fail + " failed (empty cells cannot be marked)");
        } else {
            showToast("success", ok + " cell(s) marked as " + status.replace("_", " "));
        }
        setTimeout(() => location.reload(), 900);
    }).catch(() => showToast("error", "Network error — please try again"));
}

// ── Delay log modal ───────────────────────────────────────────────────────
const _processColors = {
    Design:"#4285f4", Press:"#ea4335", Digital:"#8b5cf6", Folding:"#9c27b0",
    Gathering:"#ff9800", Staple:"#00bcd4", Binding:"#e91e63",
    Cutting:"#009688", Packaging:"#4caf50", Delivery:"#ff5722", Other:"#607d8b"
};

function openDelayModal() {
    const modalEl = document.getElementById("delayLogModal");
    if (!modalEl) {
        // Fallback: open full page
        window.location.href = "/schedule/delay-report?year={{ $year }}&month={{ $month }}";
        return;
    }
    new bootstrap.Modal(modalEl).show();
    loadDelayLog();
}

function loadDelayLog() {
    const body = document.getElementById("delayLogBody");
    if (!body) return;
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning"></div><div class="mt-2 text-muted" style="font-size:.82rem;">Loading...</div></div>';

    fetch("/schedule/delay-json?year={{ $year }}&month={{ $month }}")
    .then(r => r.json())
    .then(data => {
        const s = data.stats;
        let html = "";

        // Stats
        html += `<div class="d-flex flex-wrap gap-3 mb-3">
            <div class="p-3 rounded border text-center flex-fill">
                <div style="font-size:1.5rem;font-weight:800;color:#3b82f6;">${s.total}</div>
                <div style="font-size:.72rem;color:#64748b;">Scheduled</div>
            </div>
            <div class="p-3 rounded border text-center flex-fill">
                <div style="font-size:1.5rem;font-weight:800;color:#ef4444;">${s.urgent}</div>
                <div style="font-size:.72rem;color:#64748b;">Urgent Events</div>
            </div>
            <div class="p-3 rounded border text-center flex-fill">
                <div style="font-size:1.5rem;font-weight:800;color:#f59e0b;">${s.downtime}</div>
                <div style="font-size:.72rem;color:#64748b;">Downtime Events</div>
            </div>
            <div class="p-3 rounded border text-center flex-fill">
                <div style="font-size:1.5rem;font-weight:800;color:#7c3aed;">+${s.delayDays}</div>
                <div style="font-size:.72rem;color:#64748b;">Days Delayed</div>
            </div>
        </div>`;

        // Process summary
        if (Object.keys(data.processSummary).length) {
            html += `<div style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;
                                  border-bottom:2px solid #e2e8f0;padding-bottom:.3rem;margin-bottom:.75rem;">
                        Work by Process</div><div class="row g-2 mb-3">`;
            for (const [proc, info] of Object.entries(data.processSummary)) {
                const clr = _processColors[proc] || "#475569";
                const chips = Object.entries(info.tasks).map(([t,d]) =>
                    `<span style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:20px;
                                  padding:.1rem .5rem;font-size:.7rem;margin:2px;display:inline-block;">${
                        t.replace(/</g,"&lt;")
                    }${d>1?` <span style="background:${clr};color:#fff;border-radius:10px;
                                            padding:0 5px;font-size:.62rem;">×${d}d</span>`:""}</span>`
                ).join("");
                html += `<div class="col-md-6"><div style="border-left:4px solid ${clr};border:1px solid ${clr}30;
                            border-radius:8px;padding:.6rem .8rem;background:#fff;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="background:${clr};color:#fff;border-radius:4px;
                               padding:.1rem .4rem;font-size:.7rem;font-weight:700;">${proc}</span>
                        <span style="font-size:.7rem;color:#64748b;">${info.days}d</span>
                    </div><div>${chips}</div></div></div>`;
            }
            html += `</div>`;
        }

        // Delay log table
        if (data.logs.length) {
            html += `<div style="font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;
                                  border-bottom:2px solid #e2e8f0;padding-bottom:.3rem;margin-bottom:.75rem;">
                        Delay & Shift Log (${data.logs.length})</div>
                     <div class="table-responsive">
                     <table class="table table-sm table-hover" style="font-size:.78rem;">
                     <thead class="table-light"><tr>
                        <th>Process</th><th>Task</th>
                        <th class="text-center">Was Day</th><th class="text-center">Moved To</th>
                        <th class="text-center">+Days</th><th>Reason</th>
                     </tr></thead><tbody>`;
            data.logs.forEach(log => {
                const clr   = _processColors[log.process] || "#475569";
                const delay = log.shifted_to_day - log.original_day;
                const badge = log.reason_type === "urgent_task"
                    ? `<span class="badge bg-danger">Urgent</span>`
                    : `<span class="badge bg-warning text-dark">Downtime</span>`;
                html += `<tr>
                    <td><span style="background:${clr};color:#fff;border-radius:3px;
                               padding:.1rem .4rem;font-size:.7rem;">${log.process}</span></td>
                    <td><strong>${(log.original_task||"").replace(/</g,"&lt;")}</strong></td>
                    <td class="text-center"><span class="badge bg-secondary">Day ${log.original_day}</span></td>
                    <td class="text-center"><span class="badge bg-primary">Day ${log.shifted_to_day}</span></td>
                    <td class="text-center"><span class="badge ${delay>3?"bg-danger":"bg-warning text-dark"}">
                        ${delay>0?"+":""}${delay}d</span></td>
                    <td>${badge}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        } else {
            html += `<div class="alert alert-success mb-0">
                <i class="bi bi-check-circle me-2"></i>No delays recorded this month.
            </div>`;
        }

        body.innerHTML = html;
    })
    .catch(() => {
        body.innerHTML = `<div class="alert alert-danger">Failed to load.
            <a href="/schedule/delay-report?year={{ $year }}&month={{ $month }}">Open full page</a></div>`;
    });
}

// ── Clear month confirm ───────────────────────────────────────────────────
function confirmClearMonth() {
    if (confirm("⚠️ Clear ALL schedule data for this month? This cannot be undone.")) {
        document.getElementById("clearMonthForm").submit();
    }
}

// ── Task Builder System ───────────────────────────────────────────────────
const taskSuggestions = [
    'All Cover', 'Listening Textbook', 'Listening Workbook',
    'Reading Textbook', 'Reading Workbook', 'Writing Textbook',
    'Writing Workbook', 'Song', 'Folktale', 'Grammar',
    'Test Book', 'Teacher Book', 'Level 1', 'Level 2',
    'Level 3', 'Level 4', 'Level 5', 'Level 6',
    'Delivery', 'Maintenance'
];

let taskRowIndex = 0;

function addTaskRow(taskName = '', days = '', totalChildren = '', dailyChildren = '') {
    taskRowIndex++;
    const taskBuilder = document.getElementById('taskBuilder');
    
    const row = document.createElement('div');
    row.className = 'task-row mb-2';
    row.dataset.index = taskRowIndex;
    row.style.cssText = 'padding:0.75rem;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;';
    
    row.innerHTML = `
        <div style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem;">
            <div style="flex:1;">
                <input type="text" 
                       class="form-control form-control-sm task-name" 
                       placeholder="ឈ្មោះកិច្ចការ (e.g., Listening Textbook)"
                       value="${taskName}"
                       list="taskSuggestionsList"
                       style="font-size:0.88rem;">
            </div>
            <button type="button" 
                    class="btn btn-sm btn-outline-danger" 
                    onclick="removeTaskRow(this)"
                    title="លុបកិច្ចការនេះ">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <div style="display:flex;gap:0.5rem;align-items:end;">
            <!-- Direct Days Input -->
            <div style="flex:1;">
                <label style="font-size:0.72rem;color:#64748b;display:block;margin-bottom:2px;">
                    ថ្ងៃ (Days)
                </label>
                <input type="number" 
                       class="form-control form-control-sm task-days" 
                       placeholder="3"
                       value="${days}"
                       min="1" 
                       max="30"
                       style="font-size:0.88rem;font-weight:600;">
            </div>
            
            <div style="color:#94a3b8;font-size:0.75rem;padding:0 0.25rem;">ឬ</div>
            
            <!-- Children Calculator -->
            <div style="flex:1;">
                <label style="font-size:0.72rem;color:#64748b;display:block;margin-bottom:2px;">
                    ចំនួនកូន (Children)
                </label>
                <input type="number" 
                       class="form-control form-control-sm task-total-children" 
                       placeholder="100"
                       value="${totalChildren}"
                       min="0"
                       style="font-size:0.88rem;">
            </div>
            
            <div style="color:#94a3b8;font-size:0.85rem;">÷</div>
            
            <div style="flex:1;">
                <label style="font-size:0.72rem;color:#64748b;display:block;margin-bottom:2px;">
                    ក្នុង១ថ្ងៃ (/day)
                </label>
                <input type="number" 
                       class="form-control form-control-sm task-daily-children" 
                       placeholder="25"
                       value="${dailyChildren}"
                       min="0"
                       style="font-size:0.88rem;">
            </div>
            
            <div style="color:#94a3b8;font-size:0.85rem;">=</div>
            
            <div style="flex:1;">
                <label style="font-size:0.72rem;color:#64748b;display:block;margin-bottom:2px;">
                    គិតស្វ័យប្រវត្តិ
                </label>
                <div class="calculated-result" style="font-size:0.88rem;font-weight:700;color:#0ea5e9;
                     padding:0.375rem 0.5rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:4px;
                     text-align:center;min-height:31px;line-height:1.5;">
                    —
                </div>
            </div>
        </div>
    `;
    
    taskBuilder.appendChild(row);
    
    // Calculate and update display
    const daysInput = row.querySelector('.task-days');
    const totalInput = row.querySelector('.task-total-children');
    const dailyInput = row.querySelector('.task-daily-children');
    const resultDisplay = row.querySelector('.calculated-result');
    
    function updateCalculation() {
        const total = parseFloat(totalInput.value || 0);
        const daily = parseFloat(dailyInput.value || 0);
        const manualDays = parseFloat(daysInput.value || 0);
        
        if (total > 0 && daily > 0) {
            const calc = Math.ceil(total / daily);
            resultDisplay.textContent = calc + ' ថ្ងៃ';
            resultDisplay.style.background = '#ecfeff';
            resultDisplay.style.borderColor = '#06b6d4';
            resultDisplay.style.color = '#0891b2';
        } else if (manualDays > 0) {
            resultDisplay.textContent = '✓';
            resultDisplay.style.background = '#f0fdf4';
            resultDisplay.style.borderColor = '#86efac';
            resultDisplay.style.color = '#16a34a';
        } else {
            resultDisplay.textContent = '—';
            resultDisplay.style.background = '#f8fafc';
            resultDisplay.style.borderColor = '#e2e8f0';
            resultDisplay.style.color = '#94a3b8';
        }
        
        updateTaskHiddenInput();
    }
    
    daysInput.addEventListener('input', updateCalculation);
    totalInput.addEventListener('input', updateCalculation);
    dailyInput.addEventListener('input', updateCalculation);
    row.querySelector('.task-name').addEventListener('input', updateTaskHiddenInput);
    
    updateCalculation();
}

function removeTaskRow(btn) {
    btn.closest('.task-row').remove();
    updateTaskHiddenInput();
}

function updateTaskHiddenInput() {
    const rows = document.querySelectorAll('.task-row');
    const tasks = [];
    
    rows.forEach(row => {
        const name = row.querySelector('.task-name').value.trim();
        if (!name) return;
        
        const manualDays = parseFloat(row.querySelector('.task-days')?.value || 0);
        const totalChildren = parseFloat(row.querySelector('.task-total-children')?.value || 0);
        const dailyChildren = parseFloat(row.querySelector('.task-daily-children')?.value || 0);
        
        let days = 0;
        let metadata = '';
        
        // Priority: Use calculated days if both total and daily are provided
        if (totalChildren > 0 && dailyChildren > 0) {
            days = Math.ceil(totalChildren / dailyChildren);
            metadata = `|${dailyChildren}/day`;
        } else if (manualDays > 0) {
            days = Math.ceil(manualDays);
        } else {
            days = 1; // Default
        }
        
        let taskStr = name;
        if (days > 1) {
            taskStr += ` [${days}d]`;
        }
        if (totalChildren > 0) {
            taskStr += ` {${totalChildren}${metadata}}`;
        }
        
        tasks.push(taskStr);
    });
    
    document.getElementById('cellTask').value = tasks.join(', ');
}

// Initialize task builder when modal opens
function openCellModal(cell) {
    const process = cell.dataset.process;
    const day = cell.dataset.day;
    const task = cell.dataset.task;
    const note = cell.dataset.note;
    const color = cell.dataset.color;

    document.getElementById('cellProcess').value = process;
    document.getElementById('cellDay').value = day;
    document.getElementById('cellProcessDisplay').value = process;
    document.getElementById('cellDayDisplay').value = day + '/{{ str_pad($month, 2, "0", STR_PAD_LEFT) }}/{{ $year }}';
    document.getElementById('cellNote').value = note;

    // Clear and repopulate task builder
    const taskBuilder = document.getElementById('taskBuilder');
    taskBuilder.innerHTML = '';
    taskRowIndex = 0;
    
    if (task) {
        // Parse existing tasks: "Task1 [2d] {100|25/day}, Task2 [3d], Task3"
        const taskParts = task.split(',').map(t => t.trim()).filter(t => t);
        
        taskParts.forEach(taskStr => {
            // Extract days: [Xd]
            const daysMatch = taskStr.match(/\[(\d+)d\]/);
            let days = '';
            
            // Extract quantity and metadata: {qty|metadata}
            const qtyMatch = taskStr.match(/\{([^}]+)\}/);
            let totalChildren = '';
            let dailyChildren = '';
            
            if (qtyMatch) {
                const qtyParts = qtyMatch[1].split('|');
                totalChildren = qtyParts[0];
                if (qtyParts[1] && qtyParts[1].includes('/day')) {
                    dailyChildren = qtyParts[1].replace('/day', '');
                }
            } else if (daysMatch) {
                // Only days specified, no children
                days = daysMatch[1];
            }
            
            // Clean task name
            let taskName = taskStr
                .replace(/\[\d+d\]/g, '')
                .replace(/\{[^}]+\}/g, '')
                .replace(/\(\d+\/\d+\)/g, '')
                .trim();
            
            addTaskRow(taskName, days, totalChildren, dailyChildren);
        });
    }
    
    // Add at least one empty row if no tasks
    if (taskBuilder.children.length === 0) {
        addTaskRow();
    }

    // Handle color selection
    document.querySelectorAll('.color-radio').forEach(r => {
        r.checked = (r.value === color);
        r.closest('.color-option').querySelector('.legend-dot').style.borderColor =
            (r.value === color) ? '#4f46e5' : 'transparent';
    });

    const modal = new bootstrap.Modal(document.getElementById('cellModal'));
    modal.show();
}

// Add task button handler
document.addEventListener('DOMContentLoaded', function() {
    const btnAddTask = document.getElementById('btnAddTask');
    if (btnAddTask) {
        btnAddTask.addEventListener('click', () => addTaskRow());
    }
    
    // Create datalist for task suggestions
    const datalist = document.createElement('datalist');
    datalist.id = 'taskSuggestionsList';
    taskSuggestions.forEach(suggestion => {
        const option = document.createElement('option');
        option.value = suggestion;
        datalist.appendChild(option);
    });
    document.body.appendChild(datalist);
});

</script>

