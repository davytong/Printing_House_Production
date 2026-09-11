<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductionJob extends Model
{
    protected $fillable = [
        'job_number',
        'name',
        'book_id',
        'print_request_id',
        'template_id',
        'quantity',
        'priority',
        'start_date',
        'due_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'start_date' => 'date',
        'due_date'   => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProductionJob $job) {
            if (!$job->job_number) {
                $year = now()->format('Y');
                $count = static::whereYear('created_at', now()->year)->count() + 1;
                $job->job_number = 'JOB-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(3));
            }
        });
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function printRequest(): BelongsTo
    {
        return $this->belongsTo(PrintRequest::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductionTemplate::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ProductionSchedule::class, 'production_job_id');
    }

    public function actuals(): HasMany
    {
        return $this->hasMany(ProductionActual::class, 'production_job_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ScheduleAudit::class, 'production_job_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function totalPlannedQty(): int
    {
        return (int) $this->schedules()->sum('planned_qty');
    }

    public function totalActualQty(): int
    {
        return (int) $this->schedules()->sum('actual_qty');
    }

    public function progressPercent(): float
    {
        $planned = $this->totalPlannedQty();
        if ($planned <= 0) return 0;
        $actual = $this->totalActualQty();
        return min(100, round(($actual / $planned) * 100, 1));
    }
}
