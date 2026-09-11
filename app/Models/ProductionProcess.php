<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionProcess extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sequence',
        'default_capacity',
        'estimated_duration_hours',
        'color',
        'is_active',
    ];

    protected $casts = [
        'sequence'                 => 'integer',
        'default_capacity'         => 'integer',
        'estimated_duration_hours' => 'integer',
        'is_active'                => 'boolean',
    ];

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class, 'process_name', 'name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sequence');
    }
}
