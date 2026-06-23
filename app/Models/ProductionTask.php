<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionTask extends Model
{
    protected $fillable = [
        'name', 'description', 'process', 'duration_days', 'duration_hours',
        'status', 'priority', 'assigned_machine_id',
        'scheduled_start_date', 'scheduled_end_date',
        'actual_start_date', 'actual_end_date',
        'due_date', 'assigned_to', 'sort_order',
        'preempted_by', 'notes',
    ];

    protected $casts = [
        'scheduled_start_date' => 'date',
        'scheduled_end_date'   => 'date',
        'actual_start_date'    => 'date',
        'actual_end_date'      => 'date',
        'due_date'             => 'date',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'assigned_machine_id');
    }

    public function shiftLogs(): HasMany
    {
        return $this->hasMany(ScheduleShiftLog::class, 'task_id');
    }

    /**
     * Calculate scheduled_end_date from start + duration (skipping weekends).
     */
    public function calculateEndDate(): Carbon
    {
        $start = $this->scheduled_start_date->copy();
        $remaining = $this->duration_days - 1; // start day counts as day 1

        while ($remaining > 0) {
            $start->addDay();
            if (! $start->isWeekend()) {
                $remaining--;
            }
        }

        return $start;
    }

    /**
     * Check if task is within the critical buffer zone (due within 2 days).
     */
    public function isCritical(): bool
    {
        if (! $this->due_date) return false;
        return $this->due_date->diffInDays(now(), false) <= 2;
    }

    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    /**
     * Scope: tasks on a specific machine for a date range.
     */
    public function scopeForMachineInRange($query, int $machineId, Carbon $from, Carbon $to)
    {
        return $query->where('assigned_machine_id', $machineId)
            ->where('scheduled_start_date', '<=', $to)
            ->where(function ($q) use ($from) {
                $q->where('scheduled_end_date', '>=', $from)
                  ->orWhereNull('scheduled_end_date');
            })
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope: active/pending tasks for a given date.
     */
    public function scopeForDate($query, Carbon $date)
    {
        return $query->where('scheduled_start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->where('scheduled_end_date', '>=', $date)
                  ->orWhereNull('scheduled_end_date');
            })
            ->whereNotIn('status', ['completed', 'cancelled']);
    }
}
