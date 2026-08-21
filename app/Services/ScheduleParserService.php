<?php

namespace App\Services;

use Carbon\Carbon;

class ScheduleParserService
{
    /**
     * Parse task string and calculate schedule layout.
     *
     * @param string $taskString
     * @param int $year
     * @param int $month
     * @param int $startDay
     * @param bool $includeSundays
     * @return array [day => [label1, label2...]]
     */
    public function parseTasks(string $taskString, int $year, int $month, int $startDay, bool $includeSundays = false): array
    {
        $tasks       = array_map('trim', explode(',', $taskString));
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // Find first working day from clicked day (skip Sunday if not allowed)
        if (!$includeSundays) {
            while ($startDay <= $daysInMonth) {
                $dow = Carbon::createFromDate($year, $month, $startDay)->dayOfWeek;
                if ($dow !== 0) break;
                $startDay++;
            }
        }

        $cellsToCreate = [];

        foreach ($tasks as $taskStr) {
            if (empty(trim($taskStr))) continue;

            // 1. Extract span
            $days = 1;
            if (preg_match('/\[(\d+)d\]/', $taskStr, $m)) {
                $days = max(1, (int) $m[1]);
            } elseif (preg_match('/\{(\d+(?:\.\d+)?)\|(\d+(?:\.\d+)?)\/day\}/', $taskStr, $cm)) {
                $ch = (float) $cm[1];
                $dy = (float) $cm[2];
                if ($dy > 0) $days = (int) ceil($ch / $dy);
            }

            // 2. Strip metadata from display name
            $taskName = trim(preg_replace('/\[\d+d\]|\{[^}]+\}/', '', $taskStr));

            // 3. Place task across $days working days — ALL starting from $startDay
            $placed    = 0;
            $targetDay = $startDay; // reset to start for every task

            while ($placed < $days && $targetDay <= $daysInMonth) {
                if (!$includeSundays) {
                    $dow = Carbon::createFromDate($year, $month, $targetDay)->dayOfWeek;
                    if ($dow === 0) { 
                        $targetDay++; 
                        continue; 
                    }
                }

                $label = $days > 1
                    ? $taskName . ' (' . ($placed + 1) . '/' . $days . ')'
                    : $taskName;

                $cellsToCreate[$targetDay][] = $label;
                $placed++;
                $targetDay++;
            }
        }

        return [
            'cells' => $cellsToCreate,
            'startDay' => $startDay,
            'tasks' => $tasks,
        ];
    }
}
