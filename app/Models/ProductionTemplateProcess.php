<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionTemplateProcess extends Model
{
    protected $fillable = [
        'template_id',
        'process_name',
        'sequence',
        'capacity',
        'estimated_days',
    ];

    protected $casts = [
        'sequence'       => 'integer',
        'capacity'       => 'integer',
        'estimated_days' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductionTemplate::class, 'template_id');
    }
}
