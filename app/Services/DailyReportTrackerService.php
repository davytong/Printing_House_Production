<?php

namespace App\Services;

use App\Models\DailyReportSubmission;
use App\Models\ReportRequirement;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DailyReportTrackerService
{
    public function __construct(
        private TelegramService $telegramService
    ) {}

    /**
     * Process an incoming message from Telegram Webhook or Poller.
     * Returns true if the message was identified and processed as a daily report.
     */
    public function processIncomingMessage(array $message): bool
    {
        $from = $message['from'] ?? null;
        if (!$from || empty($from['id'])) {
            return false;
        }

        $telegramUserId = (string) $from['id'];
        $text = trim($message['text'] ?? $message['caption'] ?? '');
        $chatId = (string) ($message['chat']['id'] ?? '');

        // Reports must contain content (minimum 15 characters)
        if (mb_strlen($text) < 15) {
            return false;
        }

        // Check if this Telegram User ID has any active report requirements
        $requirements = ReportRequirement::active()
            ->where('telegram_user_id', $telegramUserId)
            ->get();

        if ($requirements->isEmpty()) {
            return false;
        }

        $now = Carbon::now('Asia/Phnom_Penh');
        $today = $now->toDateString();

        // Find which requirement matches the message via tag or smart Khmer shift indicators
        $matchedRequirement = $this->resolveMatchingRequirement($requirements, $text, $now);

        if (!$matchedRequirement) {
            return false;
        }

        // Verify that today is a working / required reporting day
        if (!$matchedRequirement->isDueOn($now)) {
            Log::info("DailyReportTracker: Received report from {$matchedRequirement->staff_name}, but today is not a required day.");
        }

        $deadlineAt = $matchedRequirement->calculateDeadlineForDate($now);

        // Fetch or create submission record for today
        $submission = DailyReportSubmission::firstOrNew([
            'report_requirement_id' => $matchedRequirement->id,
            'report_date'           => $today,
        ]);

        $duplicatePolicy = Setting::get('report_duplicate_policy', 'first_valid');

        // Handle duplicate / revised submission
        if ($submission->exists && $submission->submitted_at !== null) {
            if ($duplicatePolicy === 'first_valid') {
                $submission->message_text        = $text;
                $submission->telegram_message_id = $message['message_id'] ?? $submission->telegram_message_id;
                $submission->telegram_chat_id    = $chatId;
                $submission->revision_count      += 1;
                $submission->save();

                Log::info("DailyReportTracker: Revision #{$submission->revision_count} recorded for {$matchedRequirement->staff_name} ({$matchedRequirement->report_title}).");

                if ($matchedRequirement->send_ack) {
                    $this->sendRevisionAcknowledgement($submission, $chatId, $message['message_thread_id'] ?? null);
                }

                return true;
            }
        }

        // First submission (or latest_valid policy overwrite)
        $messageId = $message['message_id'] ?? null;

        $submission->telegram_user_id    = $telegramUserId;
        $submission->telegram_chat_id    = $chatId;
        $submission->telegram_message_id = $messageId;
        $submission->submitted_at        = $now;
        $submission->deadline_at         = $deadlineAt;
        $submission->message_text        = $text;

        // Calculate on-time vs late status
        if ($now->lte($deadlineAt)) {
            $submission->status       = 'submitted';
            $submission->late_minutes = 0;
        } else {
            $submission->status       = 'late';
            $submission->late_minutes = max(1, (int) $deadlineAt->diffInMinutes($now, false));
        }

        $submission->save();

        Log::info("DailyReportTracker: Recorded submission for {$matchedRequirement->staff_name} ({$matchedRequirement->report_title}). Status: {$submission->status}, Late: {$submission->late_minutes}m.");

        // Send Telegram acknowledgement (both on-time and late if send_ack is enabled)
        if ($matchedRequirement->send_ack) {
            $this->sendSubmissionAcknowledgement($submission, $chatId, $message['message_thread_id'] ?? null);
        }

        return true;
    }

    /**
     * Check if text contains generic production report keywords.
     */
    private function containsReportKeywords(string $text): bool
    {
        $keywords = [
            'របាយការណ៍',
            'រាយការណ៍',
            'បោះពុម្ពចំនួន',
            'បោះពុម្ពបាន',
            'ក្បាល',
            'កូន',
            'Production Report',
        ];

        foreach ($keywords as $kw) {
            if (mb_stripos($text, $kw) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve which requirement a report message corresponds to.
     * 1. Exact or partial tag match (e.g. "[Morning Production Report]", "[ក្រុមពេលព្រឹក]").
     * 2. Khmer Shift / Slot Indicators (ព្រឹក = morning_1, រសៀល = morning_2, យប់/ល្ងាច = evening_3).
     * 3. Earliest unsubmitted / pending requirement due today.
     * 4. Single requirement fallback.
     */
    protected function resolveMatchingRequirement($requirements, string $text, Carbon $now): ?ReportRequirement
    {
        // 1. Check explicit tag match first
        foreach ($requirements as $requirement) {
            if ($requirement->matchesTag($text)) {
                return $requirement;
            }
        }

        // Only proceed to smart matching if message looks like a production report
        if (! $this->containsReportKeywords($text)) {
            return null;
        }

        // 2. Khmer Shift / Slot Indicators in message text:
        // Morning shift: "ព្រឹក" or "morning"
        if (preg_match('/(ព្រឹក|morning)/iu', $text)) {
            $morningReq = $requirements->first(function ($r) {
                return $r->report_type === 'morning_1'
                    || mb_stripos($r->report_title, 'ព្រឹក') !== false
                    || mb_stripos($r->identifier_tag, 'ព្រឹក') !== false
                    || $r->deadline_time <= '12:00';
            });
            if ($morningReq) {
                return $morningReq;
            }
        }

        // Afternoon shift / Second report: "រសៀល" or "afternoon" or "second"
        if (preg_match('/(រសៀល|afternoon|second)/iu', $text)) {
            $afternoonReq = $requirements->first(function ($r) {
                return $r->report_type === 'morning_2'
                    || mb_stripos($r->report_title, 'រសៀល') !== false
                    || mb_stripos($r->identifier_tag, 'រសៀល') !== false
                    || ($r->deadline_time > '12:00' && $r->deadline_time <= '18:00');
            });
            if ($afternoonReq) {
                return $afternoonReq;
            }
        }

        // Evening shift / Night: "យប់" or "ល្ងាច" or "evening" or "night"
        if (preg_match('/(យប់|ល្ងាច|evening|night)/iu', $text)) {
            $eveningReq = $requirements->first(function ($r) {
                return $r->report_type === 'evening_3'
                    || mb_stripos($r->report_title, 'យប់') !== false
                    || mb_stripos($r->report_title, 'ល្ងាច') !== false
                    || mb_stripos($r->identifier_tag, 'យប់') !== false
                    || mb_stripos($r->identifier_tag, 'ល្ងាច') !== false
                    || $r->deadline_time > '18:00';
            });
            if ($eveningReq) {
                return $eveningReq;
            }
        }

        // 3. Fallback: Check if user has an unsubmitted / pending requirement due today
        $today = $now->toDateString();
        $dueReqs = $requirements->filter(fn($r) => $r->isDueOn($now));

        $unsubmitted = $dueReqs->first(function ($r) use ($today) {
            $sub = DailyReportSubmission::where('report_requirement_id', $r->id)
                ->where('report_date', $today)
                ->first();
            return ! $sub || $sub->submitted_at === null;
        });

        if ($unsubmitted) {
            return $unsubmitted;
        }

        // 4. Single requirement fallback
        if ($requirements->count() === 1) {
            return $requirements->first();
        }

        return null;
    }

    /**
     * Send Khmer receipt for a report submission (on-time or late).
     */
    private function sendSubmissionAcknowledgement(DailyReportSubmission $submission, string $chatId, ?int $threadId = null): void
    {
        $staffName   = $submission->requirement->staff_name;
        $reportTitle = $submission->requirement->report_title;
        $timeStr     = $submission->submitted_at->timezone('Asia/Phnom_Penh')->format('h:i A');
        $lateMinutes = $submission->late_minutes;

        if ($submission->status === 'submitted') {
            $msg = "✅ <b>របាយការណ៍បានទទួល (On Time)</b>\n\n"
                 . "👤 <b>អ្នកផ្ញើ:</b> " . htmlspecialchars($staffName) . "\n"
                 . "📋 <b>របាយការណ៍:</b> " . htmlspecialchars($reportTitle) . "\n"
                 . "⏰ <b>ម៉ោងផ្ញើ:</b> {$timeStr}\n"
                 . "✨ <b>ស្ថានភាព:</b> បានផ្ញើទាន់ពេល";
        } else {
            $msg = "⚠️ <b>របាយការណ៍បានទទួល (Late)</b>\n\n"
                 . "👤 <b>អ្នកផ្ញើ:</b> " . htmlspecialchars($staffName) . "\n"
                 . "📋 <b>របាយការណ៍:</b> " . htmlspecialchars($reportTitle) . "\n"
                 . "⏰ <b>ម៉ោងផ្ញើ:</b> {$timeStr}\n"
                 . "⚠️ <b>ស្ថានភាព:</b> យឺត {$lateMinutes} នាទី";
        }

        // Send to chat where message was posted
        if (!empty($chatId)) {
            $this->telegramService->sendMessage($chatId, $msg, $threadId, 'HTML');
        }
    }

    /**
     * Send Khmer receipt for a report revision / update.
     */
    private function sendRevisionAcknowledgement(DailyReportSubmission $submission, string $chatId, ?int $threadId = null): void
    {
        $staffName   = $submission->requirement->staff_name;
        $reportTitle = $submission->requirement->report_title;
        $timeStr     = Carbon::now('Asia/Phnom_Penh')->format('h:i A');
        $revCount    = $submission->revision_count;

        $msg = "📝 <b>បានធ្វើបច្ចុប្បន្នភាពរបាយការណ៍ (Revision #{$revCount})</b>\n\n"
             . "👤 <b>អ្នកផ្ញើ:</b> " . htmlspecialchars($staffName) . "\n"
             . "📋 <b>របាយការណ៍:</b> " . htmlspecialchars($reportTitle) . "\n"
             . "⏰ <b>ម៉ោងកែប្រែ:</b> {$timeStr}\n"
             . "ℹ️ ខ្លឹមសារត្រូវបានកត់ត្រាចូលប្រព័ន្ធដោយជោគជ័យ។";

        if (!empty($chatId)) {
            $this->telegramService->sendMessage($chatId, $msg, $threadId, 'HTML');
        }
    }

    /**
     * Check deadlines across all requirements and alert unsubmitted staff.
     * Runs every minute via Laravel Scheduler.
     */
    public function checkDeadlines(): array
    {
        $now = Carbon::now('Asia/Phnom_Penh');
        $today = $now->toDateString();
        $currentTimeStr = $now->format('H:i');

        $activeRequirements = ReportRequirement::active()->get();
        $alertsDispatched = [];

        // Group pending misses by target chat and thread
        $pendingByDestination = [];

        foreach ($activeRequirements as $req) {
            // Check if due today
            if (!$req->isDueOn($now)) {
                continue;
            }

            $deadlineAt = $req->calculateDeadlineForDate($now);

            // Deadline has arrived or passed today
            if ($now->lessThan($deadlineAt)) {
                continue;
            }

            // Look up or initialize submission record
            $submission = DailyReportSubmission::firstOrCreate(
                [
                    'report_requirement_id' => $req->id,
                    'report_date'           => $today,
                ],
                [
                    'telegram_user_id' => $req->telegram_user_id,
                    'deadline_at'      => $deadlineAt,
                    'status'           => 'pending',
                ]
            );

            // If already submitted (on time or late), no alert needed
            if ($submission->submitted_at !== null || in_array($submission->status, ['submitted', 'late'])) {
                continue;
            }

            // If alert has already been sent today for this requirement, do NOT repeat
            if ($submission->alert_sent_at !== null) {
                continue;
            }

            // Target destination
            $defaultChat    = Setting::get('report_alert_chat_id') ?: config('services.telegram.alert_chat_id');
            $defaultThread  = Setting::get('report_alert_thread_id') ?: config('services.telegram.alert_thread_id');
            $targetChatId   = $req->alert_chat_id ?: $defaultChat;
            $targetThreadId = $req->alert_thread_id ?: $defaultThread;

            if (!$targetChatId) {
                Log::warning("DailyReportTracker: No alert chat ID configured for requirement #{$req->id} ({$req->staff_name})");
                continue;
            }

            $destKey = "{$targetChatId}:" . ($targetThreadId ?? 'main') . ":{$req->report_type}";
            $pendingByDestination[$destKey]['chat_id']   = $targetChatId;
            $pendingByDestination[$destKey]['thread_id'] = $targetThreadId;
            $pendingByDestination[$destKey]['type']      = $req->report_type;
            $pendingByDestination[$destKey]['title']     = $req->report_title;
            $pendingByDestination[$destKey]['deadline']  = $req->deadline_time;
            $pendingByDestination[$destKey]['items'][]   = [
                'req'        => $req,
                'submission' => $submission,
            ];
        }

        // Send grouped alerts
        foreach ($pendingByDestination as $destKey => $destData) {
            $lockKey = "daily_report_alert_lock_{$destKey}_{$today}";

            // Acquire 2-minute cache lock to guarantee zero duplicate alerts
            Cache::lock($lockKey, 120)->get(function () use ($destData, $now, &$alertsDispatched) {
                $chatId    = $destData['chat_id'];
                $threadId  = $destData['thread_id'] ? (int) $destData['thread_id'] : null;
                $title     = $destData['title'];
                $deadline  = Carbon::createFromFormat('H:i', $destData['deadline'])->format('h:i A');
                $dateKhmer = $now->format('d-m-Y');

                $staffLines = [];
                $submissionsToUpdate = [];

                foreach ($destData['items'] as $item) {
                    $r = $item['req'];
                    $s = $item['submission'];

                    // Double-check alert_sent_at hasn't been set by another process
                    $s->refresh();
                    if ($s->alert_sent_at !== null || $s->submitted_at !== null) {
                        continue;
                    }

                    $staffLines[] = "• <b>" . htmlspecialchars($r->staff_name) . "</b>";
                    $submissionsToUpdate[] = $s;
                }

                if (empty($staffLines)) {
                    return;
                }

                $message = "⚠️ <b>{$title}</b>\n\n"
                         . "📅 <b>កាលបរិច្ឆេទ:</b> {$dateKhmer}\n"
                         . "⏰ <b>កំណត់ម៉ោង:</b> {$deadline}\n\n"
                         . "❌ <b>មិនទាន់បានផ្ញើ:</b>\n"
                         . implode("\n", $staffLines) . "\n\n"
                         . "សូមមេត្តាពិនិត្យ និងផ្ញើរបាយការណ៍។";

                $success = $this->telegramService->sendMessage($chatId, $message, $threadId, 'HTML');

                if ($success) {
                    foreach ($submissionsToUpdate as $sub) {
                        $sub->update([
                            'status'        => 'missed',
                            'alert_sent_at' => $now,
                        ]);
                    }

                    $alertsDispatched[] = [
                        'destination' => "{$chatId} (thread: {$threadId})",
                        'title'       => $title,
                        'count'       => count($staffLines),
                    ];

                    Log::info("DailyReportTracker: Alert sent for {$title} to {$chatId}. Missing: " . count($staffLines));
                }
            });
        }

        return $alertsDispatched;
    }

    /**
     * Send Daily Summary to Telegram group or admin.
     */
    public function sendDailySummary(?string $date = null, ?string $chatId = null, ?int $threadId = null): bool
    {
        $reportDate = $date ?: Carbon::now('Asia/Phnom_Penh')->toDateString();
        $carbonDate = Carbon::parse($reportDate, 'Asia/Phnom_Penh');
        $dateFormatted = $carbonDate->format('d-m-Y');

        $activeReqs = ReportRequirement::active()->get()->filter(fn($r) => $r->isDueOn($carbonDate));
        if ($activeReqs->isEmpty()) {
            return false;
        }

        $submissions = DailyReportSubmission::where('report_date', $reportDate)->get()->keyBy('report_requirement_id');

        $onTimeCount = 0;
        $lateCount   = 0;
        $missedCount = 0;
        $pendingList = [];

        foreach ($activeReqs as $req) {
            $sub = $submissions->get($req->id);

            if ($sub && $sub->status === 'submitted') {
                $onTimeCount++;
            } elseif ($sub && $sub->status === 'late') {
                $lateCount++;
            } else {
                $missedCount++;
                $timeFormatted = Carbon::createFromFormat('H:i', $req->deadline_time)->format('h:i A');
                $pendingList[] = "• " . htmlspecialchars($req->staff_name) . " — " . htmlspecialchars($req->report_title) . " ({$timeFormatted})";
            }
        }

        $msg = "📊 <b>ស្ថានភាពរបាយការណ៍ប្រចាំថ្ងៃ</b>\n"
             . "📅 <b>កាលបរិច្ឆេទ:</b> {$dateFormatted}\n\n"
             . "✅ <b>បានផ្ញើទាន់ពេល:</b> {$onTimeCount}\n"
             . "⏰ <b>បានផ្ញើយឺត:</b> {$lateCount}\n"
             . "❌ <b>មិនទាន់ផ្ញើ:</b> {$missedCount}\n";

        if (!empty($pendingList)) {
            $msg .= "\n<b>មិនទាន់ផ្ញើ:</b>\n" . implode("\n", $pendingList);
        }

        $targetChat   = $chatId ?: (Setting::get('report_alert_chat_id') ?: config('services.telegram.alert_chat_id'));
        $targetThread = $threadId ?: (Setting::get('report_alert_thread_id') ?: config('services.telegram.alert_thread_id'));

        if (!$targetChat) {
            Log::warning('DailyReportTracker: Cannot send daily summary, no target chat ID configured.');
            return false;
        }

        return $this->telegramService->sendMessage($targetChat, $msg, $targetThread, 'HTML');
    }

    /**
     * Pre-initialize pending records for today.
     */
    public function initializeDayRecords(?string $date = null): int
    {
        $reportDate = $date ?: Carbon::now('Asia/Phnom_Penh')->toDateString();
        $carbonDate = Carbon::parse($reportDate, 'Asia/Phnom_Penh');

        $activeReqs = ReportRequirement::active()->get();
        $createdCount = 0;

        foreach ($activeReqs as $req) {
            if (!$req->isDueOn($carbonDate)) {
                continue;
            }

            $deadlineAt = $req->calculateDeadlineForDate($carbonDate);

            $sub = DailyReportSubmission::firstOrCreate(
                [
                    'report_requirement_id' => $req->id,
                    'report_date'           => $reportDate,
                ],
                [
                    'telegram_user_id' => $req->telegram_user_id,
                    'deadline_at'      => $deadlineAt,
                    'status'           => 'pending',
                ]
            );

            if ($sub->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        return $createdCount;
    }
    /**
     * Scan all registered Telegram groups and auto-fetch staff/users.
     */
    public function fetchUsersFromAllGroups(): int
    {
        $uniqueChats = \App\Models\TelegramGroup::select('chat_id', 'name')
            ->distinct()
            ->get();

        $discoveredCount = 0;

        foreach ($uniqueChats as $grp) {
            $admins = $this->telegramService->getChatAdministrators((string) $grp->chat_id);
            foreach ($admins as $item) {
                $user = $item['user'] ?? null;
                if ($user && empty($user['is_bot'])) {
                    $u = \App\Models\TelegramUser::capture($user, $grp->name, 'group_admin');
                    if ($u) {
                        $discoveredCount++;
                    }
                }
            }
        }

        return $discoveredCount;
    }
}
