<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'material_id', 'type', 'quantity',
        'reference', 'performed_by', 'notes', 'movement_date',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'movement_date' => 'date',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'in'     => '📥 Stock In',
            'out'    => '📤 Stock Out',
            'adjust' => '🔧 Adjust',
            default  => $this->type,
        };
    }
}
