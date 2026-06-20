<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementItem extends Model
{
    protected $fillable = [
        'procurement_request_id', 'item_name', 'item_description',
        'category', 'quantity', 'unit', 'unit_price', 'total_amount',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }

    public function categoryLabel(): string
    {
        return match($this->category) {
            'consumable'  => 'Consumable',
            'spare_part'  => 'Spare Part',
            'component'   => 'Component',
            'service'     => 'Service',
            'equipment'   => 'Equipment',
            default       => ucfirst($this->category),
        };
    }
}
