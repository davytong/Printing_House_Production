<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleShiftLog extends Model
{
    protected $fillable = [
        'task_id', 'trigger', 'original_start', 'new_start',
        'original_end', 'new_end', 'reason',
    ];

    protected $casts = [
        'original_start' => 'date',
        'new_start'      => 'date',
        'original_end'   => 'date',
        'new_end'        => 'date',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'task_id');
    }
}
