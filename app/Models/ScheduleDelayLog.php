<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleDelayLog extends Model
{
    protected $fillable = [
        'year', 'month', 'process', 'original_task',
        'original_day', 'shifted_to_day',
        'reason_type', 'reason_detail',
    ];
}
