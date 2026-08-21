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
        'currency', 'created_by', 'notes', 'attachments',
        // Enhanced fields
        'priority', 'reason', 'requested_by', 'approved_by', 'approved_at',
        'payment_method', 'payment_status', 'paid_amount', 'paid_at', 'invoice_path',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'approved_at'   => 'datetime',
        'paid_at'       => 'datetime',
        'total_amount'  => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'attachments'   => 'array',
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
            && ! in_array($this->status, ['received', 'cancelled', 'completed']);
    }

    /**
     * Get priority badge class
     */
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

    /**
     * Get priority label
     */
    public function priorityLabel(): string
    {
        return match($this->priority) {
            'urgent' => '🔴 Urgent',
            'high'   => '🟠 High',
            'medium' => '🟡 Medium',
            'low'    => '🟢 Low',
            default  => ucfirst($this->priority ?? 'Medium'),
        };
    }

    /**
     * Get status badge class
     */
    public function statusBadge(): string
    {
        return match($this->status) {
            'draft'             => 'badge-pending',
            'pending_approval'  => 'badge-pending',
            'approved'          => 'badge-binding',
            'sent'              => 'badge-progress',
            'in_transit'        => 'badge-progress',
            'partial'           => 'badge-staple',
            'received'          => 'badge-done',
            'completed'         => 'badge-done',
            'cancelled'         => 'badge-pending',
            default             => 'badge',
        };
    }

    /**
     * Get status label
     */
    public function statusLabel(): string
    {
        return match($this->status) {
            'draft'             => '📝 Draft',
            'pending_approval'  => '⏳ Pending Approval',
            'approved'          => '✅ Approved',
            'sent'              => '✉️ Sent to Supplier',
            'in_transit'        => '🚚 In Transit',
            'partial'           => '📦 Partial Received',
            'received'          => '✅ Received',
            'completed'         => '✅ Completed',
            'cancelled'         => '❌ Cancelled',
            default             => ucfirst($this->status ?? 'Draft'),
        };
    }

    /**
     * Check if PO needs approval
     */
    public function needsApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Check if PO is approved
     */
    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'sent', 'in_transit', 'partial', 'received', 'completed']);
    }

    /**
     * Check if PO can be edited
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    /**
     * Check if PO can be deleted
     */
    public function canDelete(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval', 'cancelled']);
    }

    /**
     * Get remaining balance
     */
    public function remainingBalance(): float
    {
        return (float) ($this->total_amount - $this->paid_amount);
    }

    /**
     * Check if fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Get approver name
     */
    public function approverName(): ?string
    {
        // approved_by is stored as a name string (from session), not a User ID
        return $this->approved_by ?: null;
    }
}
