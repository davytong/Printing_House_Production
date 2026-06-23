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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fff;
            color: #333;
            padding: 30px;
        }

        .cal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4285f4;
        }

        .cal-header h1 {
            font-size: 24px;
            font-weight: 300;
            color: #1a73e8;
        }

        .cal-header .subtitle {
            font-size: 12px;
            color: #666;
        }

        .cal-actions {
            display: flex;
            gap: 10px;
        }

        .btn-print {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            font-size: 13px;
            transition: all .2s;
        }
        .btn-print:hover { background: #f0f0f0; }
        .btn-primary-print {
            background: #1a73e8;
            color: #fff;
            border-color: #1a73e8;
        }
        .btn-primary-print:hover { background: #1557b0; }

        /* Calendar Grid */
        .calendar {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .calendar thead th {
            padding: 10px;
            text-align: center;
            font-weight: 500;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid #e0e0e0;
        }

        .calendar td {
            border: 1px solid #e8e8e8;
            vertical-align: top;
            height: 120px;
            padding: 0;
            position: relative;
        }

        .calendar td.empty {
            background: #f9f9f9;
        }

        .day-number {
            display: block;
            padding: 6px 8px;
            font-size: 13px;
            font-weight: 500;
            color: #333;
        }

        .calendar td.weekend .day-number {
            color: #999;
        }

        .calendar td.today .day-number {
            background: #1a73e8;
            color: #fff;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px;
        }

        .day-tasks {
            padding: 0 4px 4px;
            overflow: hidden;
        }

        .day-task-item {
            font-size: 10px;
            padding: 2px 5px;
            margin-bottom: 2px;
            border-radius: 3px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Legend */
        .cal-legend {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .legend-label {
            font-size: 11px;
            font-weight: 600;
            color: #555;
        }

        .legend-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            color: #555;
        }

        .legend-chip span {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            display: inline-block;
        }

        @media print {
            @page { size: landscape; margin: 12mm; }
            .cal-actions { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="cal-header">
        <div>
            <h1>{{ $monthName }}</h1>
            <p class="subtitle">Production Schedule — Printing Tracker</p>
        </div>
        <div class="cal-actions">
            <button class="btn-print" onclick="window.close()">✕ Close</button>
            <button class="btn-print btn-primary-print" onclick="window.print()">🖨️ Print / Save PDF</button>
        </div>
    </div>

    <table class="calendar">
        <thead>
            <tr>
                <th>Sunday</th>
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
                                $isToday = ($year == now()->year && $month == now()->month && $day == now()->day);
                                $dayTasks = $entriesByDay->get($day, collect());
                                $cellClass = '';
                                if ($isWeekend) $cellClass .= ' weekend';
                                if ($isToday) $cellClass .= ' today';
                            @endphp
                            <td class="{{ $cellClass }}">
                                <span class="day-number">{{ $day }}</span>
                                <div class="day-tasks">
                                    @foreach($dayTasks->take(6) as $task)
                                        <div class="day-task-item" style="background: {{ $processColors[$task->process] ?? '#666' }};">
                                            {{ $task->process }}: {{ $task->task }}
                                        </div>
                                    @endforeach
                                    @if($dayTasks->count() > 6)
                                        <div style="font-size:9px; color:#666; padding:1px 5px;">+{{ $dayTasks->count() - 6 }} more</div>
                                    @endif
                                </div>
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="cal-legend">
        <span class="legend-label">LEGEND:</span>
        @foreach($processColors as $proc => $clr)
            <span class="legend-chip">
                <span style="background: {{ $clr }};"></span>
                {{ $proc }}
            </span>
        @endforeach
    </div>
</body>
</html>
