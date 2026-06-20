<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'inventory_item_id', 'type', 'quantity',
        'quantity_before', 'quantity_after',
        'reference', 'performed_by', 'notes', 'transacted_at',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after'  => 'decimal:2',
        'transacted_at'   => 'datetime',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
