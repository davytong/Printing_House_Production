<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementRequest extends Model
{
    protected $fillable = [
        'request_number', 'request_date', 'requester', 'department',
        'supplier_name', 'category', 'item_name', 'item_description',
        'quantity', 'unit', 'unit_price', 'total_amount',
        'priority', 'due_date', 'status', 'remarks',
    ];

    protected $casts = [
        'request_date' => 'date',
        'due_date'     => 'date',
        'quantity'     => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (self $m) {
            if (! $m->request_number) {
                $m->updateQuietly([
                    'request_number' => 'PR-' . now()->format('Y') . '-' . str_pad($m->id, 4, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProcurementAttachment::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProcurementLog::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['completed', 'cancelled', 'received']);
    }

    /**
     * Total value from all items in this request.
     */
    public function itemsTotal(): float
    {
        return (float) $this->items()->sum('total_amount');
    }

    /**
     * Total items count.
     */
    public function itemsCount(): int
    {
        return (int) $this->items()->count();
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

    public function statusColor(): string
    {
        return match($this->status) {
            'pending'   => 'badge-pending',
            'approved'  => 'badge-binding',
            'ordered'   => 'badge-progress',
            'received'  => 'badge-staple',
            'completed' => 'badge-done',
            'cancelled' => 'badge-pending',
            default     => '',
        };
    }

    public function priorityColor(): string
    {
        return match($this->priority) {
            'urgent' => '#dc2626',
            'high'   => '#f59e0b',
            'medium' => '#3b82f6',
            'low'    => '#6b7280',
            default  => '#6b7280',
        };
    }
}
