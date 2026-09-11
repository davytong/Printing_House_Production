<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionActual extends Model
{
    protected $fillable = [
        'production_schedule_id',
        'production_job_id',
        'production_date',
        'planned_qty',
        'actual_qty',
        'variance',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'production_date' => 'date',
        'planned_qty'     => 'integer',
        'actual_qty'      => 'integer',
        'variance'        => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductionActual $actual) {
            $actual->variance = $actual->actual_qty - $actual->planned_qty;
        });
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ProductionSchedule::class, 'production_schedule_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProductionJob::class, 'production_job_id');
    }
}
