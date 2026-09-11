<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAudit extends Model
{
    protected $fillable = [
        'production_job_id',
        'production_schedule_id',
        'user_name',
        'action',
        'old_values',
        'new_values',
        'reason',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProductionJob::class, 'production_job_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ProductionSchedule::class, 'production_schedule_id');
    }

    public static function log(string $action, ?int $jobId = null, ?int $scheduleId = null, ?array $old = null, ?array $new = null, ?string $reason = null): self
    {
        $userName = session('user_name') ?? (auth()->check() ? auth()->user()->name : 'System');
        
        return static::create([
            'production_job_id'      => $jobId,
            'production_schedule_id' => $scheduleId,
            'user_name'              => $userName,
            'action'                 => $action,
            'old_values'             => $old,
            'new_values'             => $new,
            'reason'                 => $reason,
        ]);
    }
}
