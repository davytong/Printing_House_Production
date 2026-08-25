<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowStockNotification extends Model
{
    protected $table = 'low_stock_notifications';

    protected $fillable = [
        'report_date',
        'category',
        'destination_group_id',
        'destination_group_name',
        'items_count',
        'items_payload',
        'message',
        'status',
        'sent_by',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'report_date'   => 'date',
        'items_payload' => 'array',
        'sent_at'       => 'datetime',
    ];

    public function destinationGroup(): BelongsTo
    {
        return $this->belongsTo(TelegramGroup::class, 'destination_group_id');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'SENT');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'SENT'      => '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> បានផ្ញើ (Sent)</span>',
            'FAILED'    => '<span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i> បរាជ័យ (Failed)</span>',
            'PENDING'   => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> កំពុងរង់ចាំ (Pending)</span>',
            'CANCELLED' => '<span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i> បានបោះបង់ (Cancelled)</span>',
            default     => "<span class=\"badge bg-secondary\">{$this->status}</span>",
        };
    }
}
