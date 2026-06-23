<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineDowntime extends Model
{
    protected $fillable = [
        'machine_id', 'start_time', 'duration_hours', 'reason', 'resolved',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'resolved'   => 'boolean',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get end time of the downtime window.
     */
    public function endTime(): Carbon
    {
        return $this->start_time->copy()->addHours($this->duration_hours);
    }

    /**
     * How many working days this downtime covers.
     */
    public function durationInDays(): int
    {
        $hours = $this->duration_hours;
        return max(1, (int) ceil($hours / 8)); // assuming 8-hour workday
    }
}
