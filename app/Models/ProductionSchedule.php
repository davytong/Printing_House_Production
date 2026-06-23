<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionSchedule extends Model
{
    protected $fillable = [
        'year', 'month', 'process', 'day', 'task', 'note', 'color',
    ];

    /**
     * Get all entries for a given month grouped by process then day.
     */
    public static function forMonth(int $year, int $month)
    {
        return static::where('year', $year)
            ->where('month', $month)
            ->get()
            ->groupBy('process');
    }
}
