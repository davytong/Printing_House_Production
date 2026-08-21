<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'code', 'name', 'contact_person', 'phone',
        'email', 'address', 'supply_type', 'status', 'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Supplier $m) {
            if (! $m->code) {
                $last = static::orderBy('id', 'desc')->first();
                $next = $last ? $last->id + 1 : 1;
                $m->code = 'SUP-' . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }
}
