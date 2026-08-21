@php
    use Carbon\Carbon;

    $firstDay = Carbon::createFromDate($year, $month, 1);
    $daysInMonth = $firstDay->daysInMonth;
    $startDow = $firstDay->dayOfWeek; // 0=Sun
    $monthName = $firstDay->format('F Y');

    // Process colors
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
    ];

    // Group entries by day
    $entriesByDay = $entries->groupBy('day');

    // Build calendar grid (6 weeks max)
    $weeks = [];
    $currentDay = 1;
    $week = array_fill(0, 7, null);

    // Fill first week
    for ($i = $startDow; $i < 7 && $currentDay <= $daysInMonth; $i++) {
        $week[$i] = $currentDay++;
    }
    $weeks[] = $week;

    // Fill remaining weeks
    while ($currentDay <= $daysInMonth) {
        $week = array_fill(0, 7, null);
        for ($i = 0; $i < 7 && $currentDay <= $daysInMonth; $i++) {
            $week[$i] = $currentDay++;
        }
        $weeks[] = $week;
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Production Schedule — {{ $monthName }}</title>
    <!-- Use Inter font for premium typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #f1f5f9; /* Softer border */
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --radius-lg: 16px;
            --radius-md: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            color: var(--text-main);
            padding: 40px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        .calendar-wrapper {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 100%; /* Stretch to fit more text */
            padding: 30px 40px;
            border: 1px solid rgba(255,255,255,0.7);
        }

        /* Header */
        .cal-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .header-title-container {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .cal-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .cal-header .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .cal-header .subtitle::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
        }

        /* Buttons & Actions */
        .btn-print {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: #fff;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s ease;
            box-shadow: var(--shadow-sm);
        }
        .btn-print:hover { background: #f8fafc; transform: translateY(-1px); box-shadow: var(--shadow-md); }
        
        .btn-primary-print {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            border: none;
        }
        .btn-primary-print:hover { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); color: white; }
        
        .btn-telegram {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            color: white;
            border: none;
        }
        .btn-telegram:hover { background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%); color: white; }

        .telegram-select {
            height: 35px;
            font-size: 13px;
            padding: 4px 28px 4px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background-color: #f8fafc;
            color: var(--text-main);
            font-weight: 500;
            outline: none;
            cursor: pointer;
            margin-right: 12px;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.01);
            font-family: inherit;
        }
        .telegram-select:focus { border-color: #94a3b8; }

        /* Calendar Grid */
        .calendar-table-wrapper {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .calendar {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            background: var(--card-bg);
        }

        .calendar thead {
            background: #f8fafc;
        }

        .calendar thead th {
            padding: 14px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
        }
        .calendar thead th:last-child { border-right: none; }

        .calendar td {
            border-bottom: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            vertical-align: top;
            height: 140px;
            padding: 8px;
            position: relative;
            transition: background 0.2s ease;
        }
        .calendar td:last-child { border-right: none; }
        .calendar tr:last-child td { border-bottom: none; }

        .calendar td.empty {
            background: #f8fafc;
        }
        
        .calendar td.sunday {
            background: #fff1f2;
        }
        
        .calendar thead th.sunday {
            color: #e11d48;
            background: #fff1f2;
        }

        .calendar td.today {
            background: #f0f9ff;
        }

        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .day-number {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .calendar td.weekend .day-number {
            color: #94a3b8;
        }

        .calendar td.sunday .day-number {
            color: #ef4444;
        }

        .calendar td.today .day-number {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }

        /* Tasks */
        .day-tasks {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .day-task-item {
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 6px;
            color: #fff;
            white-space: normal;
            word-wrap: break-word;
            font-weight: 600;
            line-height: 1.4;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            display: block;
            text-align: left;
        }
        
        .day-task-item strong {
            font-weight: 700;
            opacity: 0.95;
            letter-spacing: 0.2px;
        }

        /* Legend */
        .cal-legend {
            margin-top: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            padding: 16px 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .legend-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 1px;
            margin-right: 8px;
        }

        .legend-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-main);
            background: #fff;
            padding: 4px 10px 4px 6px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .legend-chip span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        @media print {
            @page { size: landscape; margin: 8mm; }
            body { 
                background: #fff; 
                padding: 0; 
                display: block; 
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-size: 0.95em;
            }
            .calendar-wrapper { 
                box-shadow: none; 
                border: none; 
                padding: 0; 
                max-width: none; 
                height: 100%;
            }
            .cal-header {
                margin-bottom: 10px;
                padding-bottom: 10px;
            }
            .cal-header h1 { font-size: 24px; }
            .cal-actions { display: none !important; }
            
            .calendar td { padding: 4px; }
            
            /* Keep task font readable */
            .day-task-item { font-size: 10.5px; padding: 3px 6px; }
            .cal-legend { background: none; border: none; padding: 5px 0 0; margin-top: 10px; }
            .legend-chip { padding: 2px 8px 2px 4px; font-size: 11px; }
        }
    </style>
</head>
<body>
    <div class="calendar-wrapper">
        <div class="cal-header">
            <div class="header-title-container">
                <h1>{{ $monthName }}</h1>
                <p class="subtitle">Production Schedule &mdash; Printing Tracker</p>
            </div>
            <div class="cal-actions" style="display: flex; align-items: center;">
                <select id="telegramGroupId" class="telegram-select">
                    <option value="all">📢 All Groups</option>
                    @foreach(\App\Models\TelegramGroup::all() as $g)
                        <option value="{{ $g->id }}">{{ $g->displayLabel() }}</option>
                    @endforeach
                </select>
                <button class="btn-print btn-telegram" id="btnTelegram" onclick="sendToTelegram()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    Send to Telegram
                </button>
                <button class="btn-print btn-primary-print" onclick="window.print()" style="margin-left: 12px;">🖨️ Print / Save PDF</button>
            </div>
        </div>

        <div class="calendar-table-wrapper">
            <table class="calendar">
                <thead>
                    <tr>
                        <th class="sunday">Sunday</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeks as $week)
                        <tr>
                            @foreach($week as $dow => $day)
                                @if($day === null)
                                    <td class="empty"></td>
                                @else
                                    @php
                                        $isWeekend = in_array($dow, [0, 6]);
                                        $isSunday = ($dow === 0);
                                        $isToday = ($year == now()->year && $month == now()->month && $day == now()->day);
                                        $dayTasks = $entriesByDay->get($day, collect());
                                        $cellClass = '';
                                        if ($isWeekend) $cellClass .= ' weekend';
                                        if ($isSunday) $cellClass .= ' sunday';
                                        if ($isToday) $cellClass .= ' today';
                                    @endphp
                                    <td class="{{ $cellClass }}">
                                        <div class="day-header">
                                            <span class="day-number">{{ $day }}</span>
                                        </div>
                                        <div class="day-tasks">
                                            @php
                                                $tasksByProcess = [];
                                                foreach ($dayTasks as $t) {
                                                    $subTasks = array_filter(array_map('trim', explode(',', $t->task)));
                                                    if (!isset($tasksByProcess[$t->process])) {
                                                        $tasksByProcess[$t->process] = [];
                                                    }
                                                    $tasksByProcess[$t->process] = array_merge($tasksByProcess[$t->process], $subTasks);
                                                }
                                            @endphp
                                            @foreach($tasksByProcess as $process => $tasks)
                                                @php 
                                                    $pColor = $processColors[$process] ?? '#64748b'; 
                                                    $uniqueTasks = array_values(array_unique($tasks));
                                                @endphp
                                                <div class="day-task-item" style="background: linear-gradient(135deg, {{ $pColor }}ee 0%, {{ $pColor }} 100%); border: 1px solid rgba(255,255,255,0.2);">
                                                    @if(count($uniqueTasks) == 1)
                                                        <strong>{{ $process }}:</strong> 
                                                        <span style="opacity: 0.95;">{{ $uniqueTasks[0] }}</span>
                                                    @else
                                                        <div style="display: flex; align-items: flex-start;">
                                                            <strong style="white-space: nowrap; margin-right: 4px;">{{ $process }}:</strong>
                                                            <div style="display: flex; flex-direction: column; opacity: 0.95;">
                                                                @foreach($uniqueTasks as $index => $taskName)
                                                                    <div>{{ $index + 1 }}/ {{ $taskName }}</div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="cal-legend">
            <span class="legend-label">LEGEND</span>
            @foreach($processColors as $proc => $clr)
                <span class="legend-chip">
                    <span style="background: {{ $clr }}; box-shadow: 0 0 0 1px rgba(0,0,0,0.05);"></span>
                    {{ $proc }}
                </span>
            @endforeach
        </div>
    </div>

    <!-- CSRF Token for Telegram POST request -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function sendToTelegram() {
            const btn = document.getElementById('btnTelegram');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Sending...';
            btn.disabled = true;

            // Temporarily hide the action buttons so they aren't in the screenshot
            const actions = document.querySelector('.cal-actions');
            actions.style.display = 'none';

            // Wait a moment for UI to update, then capture
            setTimeout(() => {
                const targetElement = document.querySelector('.calendar-wrapper') || document.body;
                html2canvas(targetElement, { scale: 1.5 }).then(canvas => {
                    // Show actions again
                    actions.style.display = 'flex';

                    // Convert to base64 (JPEG for smaller size, to avoid Telegram limits)
                    const imageData = canvas.toDataURL('image/jpeg', 0.85);
                    const monthName = '{{ $monthName }}';
                    const groupId = document.getElementById('telegramGroupId').value;

                    // Post to server
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
                            alert('✅ Successfully sent to Telegram!');
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
</body>
</html>
