<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReportSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_requirement_id',
        'report_date',
        'telegram_user_id',
        'telegram_chat_id',
        'telegram_message_id',
        'submitted_at',
        'deadline_at',
        'status',
        'late_minutes',
        'alert_sent_at',
        'message_text',
        'revision_count',
    ];

    protected $casts = [
        'report_date'   => 'date:Y-m-d',
        'submitted_at'  => 'datetime',
        'deadline_at'   => 'datetime',
        'alert_sent_at' => 'datetime',
        'late_minutes'  => 'integer',
        'revision_count'=> 'integer',
    ];

    /**
     * Parent requirement definition.
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ReportRequirement::class, 'report_requirement_id');
    }

    /**
     * Scope for a specific date.
     */
    public function scopeForDate($query, string $date)
    {
        return $query->where('report_date', $date);
    }

    /**
     * Status checkers.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    public function isMissed(): bool
    {
        return $this->status === 'missed';
    }

    /**
     * Khmer display status.
     */
    public function getKhmerStatusAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'បានផ្ញើទាន់ពេល',
            'late'      => 'បានផ្ញើយឺត (' . $this->late_minutes . ' នាទី)',
            'missed'    => 'មិនបានផ្ញើ',
            default     => 'កំពុងរង់ចាំ',
        };
    }

    /**
     * Formatted HTML badge for UI.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'submitted' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-circle me-1"></i>On time</span>',
            'late'      => '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i class="bi bi-clock-history me-1"></i>Late (' . $this->late_minutes . 'm)</span>',
            'missed'    => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-x-circle me-1"></i>Missed</span>',
            default     => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Pending</span>',
        };
    }

    /**
     * Formatted submission time in Asia/Phnom_Penh.
     */
    public function getFormattedSubmittedAtAttribute(): string
    {
        if (!$this->submitted_at) {
            return '—';
        }

        return $this->submitted_at->timezone('Asia/Phnom_Penh')->format('h:i A');
    }

    /**
     * Formatted deadline time in Asia/Phnom_Penh.
     */
    public function getFormattedDeadlineAttribute(): string
    {
        if (!$this->deadline_at) {
            return $this->requirement?->deadline_time ?? '—';
        }

        return $this->deadline_at->timezone('Asia/Phnom_Penh')->format('h:i A');
    }
}