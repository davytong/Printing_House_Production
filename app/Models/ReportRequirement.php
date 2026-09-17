<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReportRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_name',
        'telegram_user_id',
        'telegram_username',
        'report_type',
        'report_title',
        'identifier_tag',
        'deadline_time',
        'required_days',
        'alert_chat_id',
        'alert_thread_id',
        'active',
        'send_ack',
        'notes',
    ];

    protected $casts = [
        'required_days' => 'array',
        'active'        => 'boolean',
        'send_ack'      => 'boolean',
        'alert_thread_id' => 'integer',
    ];

    /**
     * All submissions for this requirement.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(DailyReportSubmission::class, 'report_requirement_id');
    }

    /**
     * Today submission relation helper.
     */
    public function todaySubmission(): HasOne
    {
        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();
        return $this->hasOne(DailyReportSubmission::class, 'report_requirement_id')
                    ->where('report_date', $today);
    }

    /**
     * Active requirements scope.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Filter by matching deadline time (e.g. 07:00, 15:10, 23:50).
     */
    public function scopeDueAt($query, string $time)
    {
        return $query->where('deadline_time', $time);
    }

    /**
     * Check if this report requirement is required on a given date.
     */
    public function isDueOn(Carbon $date): bool
    {
        if (!$this->active) {
            return false;
        }

        $days = $this->required_days;
        // Default to Monday through Saturday if not specified
        if (empty($days)) {
            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        }

        $dayOfWeek = strtolower($date->format('D')); // mon, tue, wed, thu, fri, sat, sun
        return in_array($dayOfWeek, array_map('strtolower', $days), true);
    }

    /**
     * Calculate exact Carbon deadline datetime for a given report date.
     */
    public function calculateDeadlineForDate(Carbon $date): Carbon
    {
        $timeParts = explode(':', $this->deadline_time);
        $hour   = (int) ($timeParts[0] ?? 0);
        $minute = (int) ($timeParts[1] ?? 0);

        return $date->copy()->setTimezone('Asia/Phnom_Penh')->setTime($hour, $minute, 0);
    }

    /**
     * Check if a message text or caption matches this requirement's identifier tag.
     */
    public function matchesTag(string $text): bool
    {
        $tag = trim($this->identifier_tag);
        if ($tag === '') {
            return false;
        }

        // Clean brackets or special characters for regex matching
        $cleanTag = preg_quote($tag, '/');

        // Check exact or bracketed pattern, case-insensitive
        return (bool) preg_match("/{$cleanTag}/iu", $text);
    }
}