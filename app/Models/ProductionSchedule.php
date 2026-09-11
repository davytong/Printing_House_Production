<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionSchedule extends Model
{
    protected $fillable = [
        'year',
        'month',
        'process',
        'day',
        'schedule_date',
        'production_job_id',
        'machine_id',
        'task',
        'planned_qty',
        'actual_qty',
        'note',
        'color',
        'status',
        'is_locked',
    ];

    protected $casts = [
        'year'        => 'integer',
        'month'       => 'integer',
        'day'         => 'integer',
        'planned_qty' => 'integer',
        'actual_qty'  => 'integer',
        'is_locked'   => 'boolean',
        'schedule_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductionSchedule $schedule) {
            if (!$schedule->schedule_date && $schedule->year && $schedule->month && $schedule->day) {
                try {
                    $schedule->schedule_date = Carbon::createFromDate($schedule->year, $schedule->month, $schedule->day)->toDateString();
                } catch (\Exception $e) {
                    // Ignore if invalid date
                }
            }
        });
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProductionJob::class, 'production_job_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function actuals(): HasMany
    {
        return $this->hasMany(ProductionActual::class, 'production_schedule_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ScheduleAudit::class, 'production_schedule_id');
    }

    public function isLocked(): bool
    {
        return (bool) $this->is_locked || $this->status === 'locked';
    }

    public function remainingQty(): int
    {
        if (!$this->planned_qty) return 0;
        return max(0, $this->planned_qty - ($this->actual_qty ?? 0));
    }

    public function progressPercent(): float
    {
        if (!$this->planned_qty || $this->planned_qty <= 0) return 0;
        return min(100, round((($this->actual_qty ?? 0) / $this->planned_qty) * 100, 1));
    }

    /**
     * Get all entries for a given month grouped by process then day.
     */
    public static function forMonth(int $year, int $month)
    {
        return static::with(['job', 'machine'])
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->groupBy('process');
    }
}
