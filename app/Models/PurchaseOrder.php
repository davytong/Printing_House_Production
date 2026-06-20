<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_id', 'status', 'order_date',
        'expected_date', 'received_date', 'total_amount',
        'currency', 'created_by', 'notes',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'total_amount'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (PurchaseOrder $m) {
            if (! $m->po_number) {
                $year = now()->format('Y');
                $m->updateQuietly([
                    'po_number' => 'PO-' . $year . '-' . str_pad($m->id, 4, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isOverdue(): bool
    {
        return $this->expected_date
            && $this->expected_date->isPast()
            && ! in_array($this->status, ['received', 'cancelled']);
    }
}
