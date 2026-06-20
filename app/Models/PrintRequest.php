<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintRequest extends Model
{
    protected $fillable = [
        'request_code', 'title', 'requester_name', 'department',
        'priority', 'status', 'quantity_requested', 'total_books_requested',
        'required_by', 'notes', 'attachments',
        'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'required_by' => 'date',
        'approved_at' => 'datetime',
        'attachments' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function (PrintRequest $m) {
            if (! $m->request_code) {
                $year = now()->format('Y');
                $m->updateQuietly([
                    'request_code' => 'REQ-' . $year . '-' . str_pad($m->id, 4, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrintRequestItem::class);
    }

    public function isOverdue(): bool
    {
        return $this->required_by
            && $this->required_by->isPast()
            && ! in_array($this->status, ['completed', 'cancelled']);
    }

    public function totalQty(): int
    {
        $fromItems = (int) $this->items()->sum('quantity_requested');
        return $fromItems > 0 ? $fromItems : (int) $this->quantity_requested;
    }
}
