<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function processes(): HasMany
    {
        return $this->hasMany(ProductionTemplateProcess::class, 'template_id')->orderBy('sequence');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
