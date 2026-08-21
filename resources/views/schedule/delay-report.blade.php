@extends('layouts.app')

@section('title', 'Monthly Work Summary')

@section('content')
@php
    use Carbon\Carbon;
    $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

    $processColors = [
        'Design'    => '#4285f4',
        'Press'     => '#ea4335',
        'Digital'   => '#8b5cf6',
        'Folding'   => '#9c27b0',
        'Gathering' => '#ff9800',
        'Staple'    => '#00bcd4',
        'Binding'   => '#e91e63',
        'Cutting'   => '#009688',
        'Packaging' => '#4caf50',
        'Delivery'  => '#ff5722',
        'Other'     => '#607d8b',
    ];
@endphp

<style>
@media print {
    .sidebar, .topbar, nav, .page-header, .btn, .d-flex.align-items-center.justify-content-between { display: none !important; }
    .page-content { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
    .glass-card, .summary-card { border: 1px solid #ddd !important; box-shadow: none !important; background: transparent !important; }
    body { font-size: 11px; }
}

/* Premium Typography */
.page-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #1e293b;
}

/* Premium KPI Cards */
.kpi-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    flex: 1;
    min-width: 180px;
}
.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
}
.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--color-1), var(--color-2));
}
.kpi-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
    background: linear-gradient(135deg, var(--color-1), var(--color-2));
    box-shadow: 0 8px 16px -4px rgba(var(--color-rgb), 0.3);
    flex-shrink: 0;
}
.kpi-val {
    font-size: 1.75rem;
    font-weight: 800;
    font-family: var(--font-latin);
    line-height: 1.1;
    color: #0f172a;
    margin-bottom: 0.2rem;
}
.kpi-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-secondary);
}

/* Premium Badges & Chips */
.process-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.task-chip {
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    margin: 3px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    transition: all 0.2s;
}
.task-chip:hover {
    background: #fff;
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.04);
}
.task-chip .duration-badge {
    color: #fff;
    border-radius: 12px;
    padding: 0.1rem 0.4rem;
    font-size: 0.65rem;
    font-weight: 700;
    margin-left: 6px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Premium Section Titles */
.section-title {
    font-size: 0.9rem;
    font-weight: 800;
    color: #1e293b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
}
.section-title i {
    color: #6366f1;
    font-size: 1.1rem;
    margin-right: 0.5rem;
}

/* Premium Process Cards */
.process-card {
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.6) 100%);
    backdrop-filter: blur(10px);
    height: 100%;
    position: relative;
    border: 1px solid rgba(255,255,255,0.8);
    box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.process-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px -5px rgba(0,0,0,0.08);
}

/* Beautiful Tables */
.premium-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.premium-table th {
    background: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.75rem 1rem;
    border-bottom: 2px solid #e2e8f0;
}
.premium-table th:first-child { border-top-left-radius: 8px; }
.premium-table th:last-child { border-top-right-radius: 8px; }
.premium-table td {
    padding: 0.85rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 0.85rem;
}
.premium-table tbody tr {
    transition: background-color 0.2s ease;
}
.premium-table tbody tr:hover {
    background-color: #f8fafc !important;
}
.premium-table tbody tr:last-child td {
    border-bottom: none;
}

/* Glassmorphic Container Cards */
.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}
</style>

{{-- HEADER --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="page-title mb-1">
            <i class="bi bi-clipboard-data text-primary me-2"></i>
            Monthly Work Summary <span style="color:#94a3b8;font-weight:400;">/</span> <span class="text-primary">{{ $monthName }}</span>
        </h1>
        <div style="font-size:.85rem;color:#64748b;font-weight:500;">
            Production schedule report with delay log
        </div>
    </div>
    <div class="d-flex gap-2 cal-actions align-items-center">
        <a href="{{ route('schedule.index', ['year'=>$year,'month'=>$month]) }}" class="btn btn-light shadow-sm" style="font-weight:600;border-radius:8px;">
            <i class="bi bi-arrow-left me-1"></i> Back to Schedule
        </a>
        <select id="telegramGroupId" class="form-select shadow-sm" style="width: auto; border-radius:8px; font-size:.85rem; padding:0.4rem 2rem 0.4rem 0.75rem;">
            <option value="all">📢 All Groups</option>
            @foreach(\App\Models\TelegramGroup::all() as $g)
                <option value="{{ $g->id }}">{{ $g->displayLabel() }}</option>
            @endforeach
        </select>
        <button id="btnTelegramDelay" class="btn shadow-sm text-white" onclick="sendDelayToTelegram()" style="font-weight:600;border-radius:8px;background:linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);border:none;">
            <i class="bi bi-telegram me-1"></i> Telegram
        </button>
        <select id="paperSize" class="form-select shadow-sm" style="width: auto; border-radius:8px; font-size:.85rem; padding:0.4rem 2rem 0.4rem 0.75rem;" onchange="updatePaperSize()">
            <option value="A4">📄 A4</option>
            <option value="A3">📄 A3</option>
            <option value="Letter">📄 Letter</option>
        </select>
        <button class="btn btn-primary shadow-sm" onclick="window.print()" style="font-weight:600;border-radius:8px;background:linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);border:none;">
            <i class="bi bi-printer me-1"></i> Print / PDF
        </button>
    </div>
</div>

{{-- ── TOP STATS ── --}}
<div class="d-flex flex-wrap gap-3 mb-4">
    <div class="kpi-card" style="--color-1:#3b82f6; --color-2:#2563eb; --color-rgb:37,99,235;">
        <div class="d-flex gap-3 align-items-start">
            <div class="kpi-icon-wrap"><i class="bi bi-calendar3"></i></div>
            <div>
                <div class="kpi-val">{{ $allCells->count() }}</div>
                <div class="kpi-title">Total Scheduled Cells</div>
            </div>
        </div>
    </div>
    <div class="kpi-card" style="--color-1:#10b981; --color-2:#059669; --color-rgb:5,150,105;">
        <div class="d-flex gap-3 align-items-start">
            <div class="kpi-icon-wrap"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="kpi-val">{{ $completedCells }}</div>
                <div class="kpi-title">Completed (past days)</div>
            </div>
        </div>
    </div>
    <div class="kpi-card" style="--color-1:#ef4444; --color-2:#dc2626; --color-rgb:239,68,68;">
        <div class="d-flex gap-3 align-items-start">
            <div class="kpi-icon-wrap"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="kpi-val">{{ $delayedTasks }}</div>
                <div class="kpi-title">Urgent Interruptions</div>
            </div>
        </div>
    </div>
    <div class="kpi-card" style="--color-1:#f59e0b; --color-2:#d97706; --color-rgb:245,158,11;">
        <div class="d-flex gap-3 align-items-start">
            <div class="kpi-icon-wrap"><i class="bi bi-tools"></i></div>
            <div>
                <div class="kpi-val">{{ $downtimeEvents }}</div>
                <div class="kpi-title">Downtime Events</div>
            </div>
        </div>
    </div>
    <div class="kpi-card" style="--color-1:#8b5cf6; --color-2:#7c3aed; --color-rgb:139,92,246;">
        <div class="d-flex gap-3 align-items-start">
            <div class="kpi-icon-wrap"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="kpi-val">+{{ $totalDelayDays }}</div>
                <div class="kpi-title">Total Days Delayed</div>
            </div>
        </div>
    </div>
</div>

{{-- ── PROCESS WORK SUMMARY ── --}}
<div class="glass-card">
    <div class="card-body p-4">
        <div class="section-title"><i class="bi bi-bar-chart-steps"></i>Work Done by Process</div>
        @if(empty($processSummary))
            <p class="text-muted" style="font-size:.85rem;">No scheduled work for this month yet.</p>
        @else
        <div class="row g-3">
            @foreach($processSummary as $proc => $info)
            @php $clr = $processColors[$proc] ?? '#475569'; @endphp
            <div class="col-md-6 col-lg-4">
                <div class="process-card" style="border-left: 4px solid {{ $clr }}; background: linear-gradient(90deg, {{ $clr }}05 0%, #fff 100%);">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="process-badge" style="background: linear-gradient(135deg, {{ $clr }}ee, {{ $clr }});">{{ $proc }}</span>
                        <span style="font-size:.8rem;font-weight:700;color:#64748b;background:#f1f5f9;padding:0.15rem 0.5rem;border-radius:12px;">{{ $info['days'] }} day(s)</span>
                    </div>
                    <div class="d-flex flex-wrap">
                        @foreach($info['tasks'] as $taskName => $dayCount)
                            <span class="task-chip">
                                {{ $taskName }}
                                @if($dayCount > 1)
                                    <span class="duration-badge" style="background:{{ $clr }}">{{ $dayCount }}d</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── SCHEDULED TASK TIMELINE ── --}}
<div class="glass-card">
    <div class="card-body p-4">
        <div class="section-title"><i class="bi bi-calendar-week"></i>Full Schedule Timeline</div>
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Process</th>
                        <th>Tasks</th>
                        <th>Note</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allCells as $cell)
                    @php
                        $today = now()->day;
                        $isCurrentMonth = ($year == now()->year && $month == now()->month);
                        $isPast   = $isCurrentMonth ? $cell->day < $today : $month < now()->month;
                        $isToday  = $isCurrentMonth && $cell->day == $today;
                        $isFuture = !$isPast && !$isToday;
                        $clr = $processColors[$cell->process] ?? '#475569';
                    @endphp
                    <tr style="{{ $isToday ? 'background: linear-gradient(90deg, #fef9c333 0%, transparent 100%);' : '' }}">
                        <td style="font-family:var(--font-latin);font-weight:700;color:#475569;white-space:nowrap;">
                            {{ str_pad($cell->day,2,'0',STR_PAD_LEFT) }}<span style="color:#cbd5e1;">/</span>{{ str_pad($month,2,'0',STR_PAD_LEFT) }}
                            @if($isToday)<span class="badge ms-2" style="background:#fef08a;color:#854d0e;font-size:0.65rem;">Today</span>@endif
                        </td>
                        <td><span class="process-badge" style="background: linear-gradient(135deg, {{ $clr }}ee, {{ $clr }});">{{ $cell->process }}</span></td>
                        <td style="max-width:300px;white-space:normal;">
                            <div class="d-flex flex-wrap gap-1">
                            @foreach(array_map('trim', explode(',', $cell->task)) as $t)
                                @if($t)
                                @php $isUrgent = str_contains(strtoupper($t), 'URGENT'); $isDowntime = str_starts_with($t,'🔧'); @endphp
                                <span style="display:inline-flex;align-items:center;background:{{ $isUrgent ? '#fef2f2' : ($isDowntime ? '#fffbeb' : '#f8fafc') }};
                                    color:{{ $isUrgent ? '#b91c1c' : ($isDowntime ? '#b45309' : '#334155') }};
                                    border:1px solid {{ $isUrgent ? '#fecaca' : ($isDowntime ? '#fde68a' : '#e2e8f0') }};
                                    border-radius:6px;padding:0.15rem 0.5rem;font-size:.75rem;font-weight:600;">
                                    @if($isUrgent)<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>@endif
                                    @if($isDowntime)<i class="bi bi-tools me-1"></i>@endif
                                    {{ $t }}
                                </span>
                                @endif
                            @endforeach
                            </div>
                        </td>
                        <td style="font-size:.78rem;color:#64748b;max-width:200px;white-space:normal;font-style:italic;">{{ $cell->note }}</td>
                        <td>
                            @if($isDowntime ?? false)
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;">Downtime</span>
                            @elseif($isPast)
                                <span class="badge" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">Done</span>
                            @elseif($isToday)
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;">In Progress</span>
                            @else
                                <span class="badge" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;">Planned</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── DELAY & SHIFT LOG ── --}}
<div class="glass-card">
    <div class="card-body p-4">
        <div class="section-title d-flex align-items-center justify-content-between w-100 border-bottom-0 mb-0">
            <div class="d-flex align-items-center">
                <i class="bi bi-journal-text text-danger"></i>
                <span class="ms-1">Delay & Shift Log</span>
            </div>
            @if($logs->isNotEmpty())
                <div>
                    <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:0.3rem 0.6rem;">{{ $logs->where('reason_type','urgent_task')->count() }} urgent</span>
                    <span class="badge ms-1" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;padding:0.3rem 0.6rem;">{{ $logs->where('reason_type','machine_downtime')->count() }} downtime</span>
                </div>
            @endif
        </div>
        
        <div class="border-top mb-4 mt-2" style="border-color:#f1f5f9 !important;"></div>

        @if($logs->isEmpty())
            <div class="alert alert-success d-flex align-items-center border-0 shadow-sm" style="background:linear-gradient(90deg, #f0fdf4 0%, #fff 100%);border-left:4px solid #22c55e !important;font-size:.85rem;font-weight:600;color:#15803d;">
                <i class="bi bi-check-circle-fill fs-5 me-3 text-success"></i>
                <div>
                    Excellent!<br>
                    <span style="font-weight:400;color:#166534;">No delays this month. All tasks ran on their original schedule.</span>
                </div>
            </div>
        @else
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Process</th>
                        <th>Task</th>
                        <th>Original Day</th>
                        <th>→ Moved To</th>
                        <th>Delay</th>
                        <th>Reason</th>
                        <th>Detail</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    @php
                        $delay = $log->shifted_to_day - $log->original_day;
                        $clr = $processColors[$log->process] ?? '#475569';
                    @endphp
                    <tr>
                        <td><span class="process-badge" style="background: linear-gradient(135deg, {{ $clr }}ee, {{ $clr }});">{{ $log->process }}</span></td>
                        <td style="font-weight:700;color:#334155;">{{ $log->original_task }}</td>
                        <td class="text-center">
                            <span class="badge" style="background:#f8fafc;color:#64748b;border:1px solid #e2e8f0;padding:0.35rem 0.6rem;">Day {{ $log->original_day }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:0.35rem 0.6rem;">Day {{ $log->shifted_to_day }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge" style="padding:0.35rem 0.6rem;background:{{ $delay > 3 ? '#fef2f2' : '#fffbeb' }};color:{{ $delay > 3 ? '#b91c1c' : '#b45309' }};border:1px solid {{ $delay > 3 ? '#fecaca' : '#fde68a' }};">
                                {{ $delay > 0 ? '+' . $delay . 'd' : ($delay < 0 ? $delay.'d earlier' : '—') }}
                            </span>
                        </td>
                        <td>
                            @if($log->reason_type === 'urgent_task')
                                <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Urgent</span>
                            @else
                                <span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;"><i class="bi bi-tools me-1"></i>Downtime</span>
                            @endif
                        </td>
                        <td style="max-width:260px;white-space:normal;font-size:.78rem;color:#475569;">{{ $log->reason_detail }}</td>
                        <td>
                            <form action="{{ route('schedule.delay-log.delete', $log->id) }}" method="POST" class="d-inline" data-confirm="Are you sure you want to delete this delay log entry?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="Delete Log">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 p-3 rounded d-flex align-items-center shadow-sm" style="background:linear-gradient(90deg, #f8fafc 0%, #fff 100%);border:1px solid #e2e8f0;">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;flex-shrink:0;">
                <i class="bi bi-info-lg fs-5"></i>
            </div>
            <div style="font-size:.85rem;color:#334155;">
                Total delay: <strong class="text-primary fs-6">+{{ $totalDelayDays }} working day(s)</strong> across
                <strong>{{ $logs->count() }}</strong> events.<br>
                <span class="text-muted">
                    This includes <strong>{{ $logs->where('reason_type','urgent_task')->count() }}</strong> urgent interruptions and
                    <strong>{{ $logs->where('reason_type','machine_downtime')->count() }}</strong> downtime events.
                </span>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
    <!-- CSRF Token for Telegram POST request -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
    function updatePaperSize() {
        const size = document.getElementById('paperSize').value;
        let style = document.getElementById('dynamicPageStyle');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dynamicPageStyle';
            document.head.appendChild(style);
        }
        style.innerHTML = `@media print { @page { size: ${size} portrait !important; margin: 8mm; } }`;
    }

    function sendDelayToTelegram() {
    const btn = document.getElementById('btnTelegramDelay');
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Sending...';
    btn.disabled = true;

    // Temporarily hide the action buttons
    const actions = document.querySelector('.cal-actions');
    const originalDisplay = actions.style.display;
    actions.style.display = 'none';

    setTimeout(() => {
        const targetElement = document.querySelector('.page-content') || document.body;
        
        html2canvas(targetElement, { scale: 3 }).then(canvas => {
            actions.style.display = originalDisplay;

            const imageData = canvas.toDataURL('image/png');
            const monthName = '{{ $monthName }} (Work Summary)';
            const groupId = document.getElementById('telegramGroupId').value;

            fetch('{{ route('schedule.export-telegram') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    image: imageData,
                    monthName: monthName,
                    group_id: groupId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Successfully sent Work Summary to Telegram!');
                } else {
                    alert('❌ Failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('❌ Network error when sending to Telegram.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }, 100);
}
</script>
@endpush
