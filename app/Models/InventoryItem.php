<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'unit', 'quantity_in_stock',
        'minimum_stock', 'unit_cost', 'location',
        'supplier_id', 'description', 'status',
    ];

    protected $casts = [
        'quantity_in_stock' => 'decimal:2',
        'minimum_stock'     => 'decimal:2',
        'unit_cost'         => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (InventoryItem $m) {
            if (! $m->code) {
                $m->updateQuietly(['code' => 'INV-' . str_pad($m->id, 3, '0', STR_PAD_LEFT)]);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->quantity_in_stock <= (float) $this->minimum_stock;
    }

    public function totalValue(): float
    {
        return (float) ($this->quantity_in_stock * $this->unit_cost);
    }
}
