<?php

namespace App\Http\Controllers;

use App\Models\DailyReportSubmission;
use App\Models\ReportRequirement;
use App\Models\TelegramGroup;
use App\Services\DailyReportTrackerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyReportTrackingController extends Controller
{
    /**
     * Daily Tracking Dashboard.
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->input('date', Carbon::now('Asia/Phnom_Penh')->toDateString());
        $carbonDate   = Carbon::parse($selectedDate, 'Asia/Phnom_Penh');

        $staffFilter      = $request->input('staff');
        $reportTypeFilter = $request->input('report_type');
        $statusFilter     = $request->input('status');

        // All active requirements
        $requirementsQuery = ReportRequirement::query();
        if ($staffFilter) {
            $requirementsQuery->where('telegram_user_id', $staffFilter);
        }
        if ($reportTypeFilter) {
            $requirementsQuery->where('report_type', $reportTypeFilter);
        }
        $requirements = $requirementsQuery->orderBy('deadline_time')->get();

        // Filter requirements that are due on this date
        $dueRequirements = $requirements->filter(fn($r) => $r->isDueOn($carbonDate));

        // Submissions for this date
        $submissions = DailyReportSubmission::with('requirement')
            ->where('report_date', $selectedDate)
            ->get()
            ->keyBy('report_requirement_id');

        // Matrix Data: Group by staff member
        $staffMatrix = [];
        foreach ($dueRequirements as $req) {
            $staffKey = $req->telegram_user_id ?: $req->staff_name;
            if (!isset($staffMatrix[$staffKey])) {
                $staffMatrix[$staffKey] = [
                    'staff_name'        => $req->staff_name,
                    'telegram_user_id'  => $req->telegram_user_id,
                    'telegram_username' => $req->telegram_username,
                    'slots'             => [],
                ];
            }

            $submission = $submissions->get($req->id);

            // Determine status
            $status = $submission ? $submission->status : ($carbonDate->isToday() && Carbon::now('Asia/Phnom_Penh')->lt($req->calculateDeadlineForDate($carbonDate)) ? 'pending' : 'missed');

            $staffMatrix[$staffKey]['slots'][$req->deadline_time] = [
                'requirement' => $req,
                'submission'  => $submission,
                'status'      => $status,
                'submitted_at'=> $submission?->formatted_submitted_at ?? '—',
                'late_minutes'=> $submission?->late_minutes ?? 0,
            ];
        }

        // Summary Statistics
        $totalRequired = $dueRequirements->count();
        $submittedCount = 0;
        $lateCount      = 0;
        $missedCount    = 0;
        $pendingCount   = 0;

        foreach ($dueRequirements as $req) {
            $sub = $submissions->get($req->id);
            if ($sub && $sub->status === 'submitted') {
                $submittedCount++;
            } elseif ($sub && $sub->status === 'late') {
                $lateCount++;
            } elseif ($sub && $sub->status === 'missed') {
                $missedCount++;
            } else {
                // If past deadline today or past date -> missed, else pending
                if ($carbonDate->isPast() && !$carbonDate->isToday()) {
                    $missedCount++;
                } elseif (Carbon::now('Asia/Phnom_Penh')->gte($req->calculateDeadlineForDate($carbonDate))) {
                    $missedCount++;
                } else {
                    $pendingCount++;
                }
            }
        }

        // All distinct staff for filter dropdown
        $allStaff = ReportRequirement::select('staff_name', 'telegram_user_id')
            ->distinct()
            ->orderBy('staff_name')
            ->get();

        // Distinct report types
        $reportTypes = ReportRequirement::select('report_type', 'report_title')
            ->distinct()
            ->get();

        return view('reports.tracking.index', compact(
            'selectedDate',
            'carbonDate',
            'staffMatrix',
            'submissions',
            'dueRequirements',
            'totalRequired',
            'submittedCount',
            'lateCount',
            'missedCount',
            'pendingCount',
            'allStaff',
            'reportTypes',
            'staffFilter',
            'reportTypeFilter',
            'statusFilter'
        ));
    }

    /**
     * Requirements Management Screen.
     */
    public function requirements(): View
    {
        $requirements  = ReportRequirement::orderBy('staff_name')->orderBy('deadline_time')->get();
        $telegramGroups = TelegramGroup::orderBy('chat_id')->orderBy('message_thread_id')->get();
        $groupedChats   = $telegramGroups->groupBy('chat_id');
        $telegramUsers  = \App\Models\TelegramUser::orderBy('display_name')->get();

        return view('reports.tracking.requirements', compact('requirements', 'telegramGroups', 'groupedChats', 'telegramUsers'));
    }

    /**
     * Scan Telegram groups and auto-fetch users.
     */
    public function fetchTelegramUsers(DailyReportTrackerService $service, Request $request)
    {
        $count = $service->fetchUsersFromAllGroups();

        if ($request->wantsJson()) {
            $users = \App\Models\TelegramUser::orderBy('display_name')->get();
            return response()->json([
                'success' => true,
                'message' => "បានទាញយក និងធ្វើបច្ចុប្បន្នភាពគណនី Telegram ចំនួន {$count} នាក់",
                'count'   => $count,
                'users'   => $users,
            ]);
        }

        return back()->with('success', "បានទាញយក និងធ្វើបច្ចុប្បន្នភាពគណនី Telegram ចំនួន {$count} នាក់ដោយជោគជ័យ (Fetched/Updated {$count} users from Telegram groups).");
    }

    /**
     * Store a new requirement.
     */
    public function storeRequirement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_name'        => 'required|string|max:255',
            'telegram_user_id'  => 'required|string|max:255',
            'telegram_username' => 'nullable|string|max:255',
            'report_type'       => 'required|string|max:50',
            'report_title'      => 'required|string|max:255',
            'identifier_tag'    => 'required|string|max:255',
            'deadline_time'     => 'required|string|regex:/^\d{2}:\d{2}$/',
            'required_days'     => 'nullable|array',
            'alert_chat_id'     => 'nullable|string|max:255',
            'alert_thread_id'   => 'nullable|integer',
            'active'            => 'nullable|boolean',
            'send_ack'          => 'nullable|boolean',
            'notes'             => 'nullable|string',
        ]);

        $validated['active']   = $request->boolean('active', true);
        $validated['send_ack'] = $request->boolean('send_ack', true);

        // Normalize identifier tag with brackets if missing
        $tag = trim($validated['identifier_tag']);
        if (!str_starts_with($tag, '[') && !str_ends_with($tag, ']')) {
            $tag = "[{$tag}]";
        }
        $validated['identifier_tag'] = $tag;

        ReportRequirement::create($validated);

        return redirect()->route('reports.requirements.index')->with('success', 'បានបង្កើតកាលវិភាគរបាយការណ៍ដោយជោគជ័យ (Report requirement created successfully).');
    }

    /**
     * Update an existing requirement.
     */
    public function updateRequirement(Request $request, ReportRequirement $requirement): RedirectResponse
    {
        $validated = $request->validate([
            'staff_name'        => 'required|string|max:255',
            'telegram_user_id'  => 'required|string|max:255',
            'telegram_username' => 'nullable|string|max:255',
            'report_type'       => 'required|string|max:50',
            'report_title'      => 'required|string|max:255',
            'identifier_tag'    => 'required|string|max:255',
            'deadline_time'     => 'required|string|regex:/^\d{2}:\d{2}$/',
            'required_days'     => 'nullable|array',
            'alert_chat_id'     => 'nullable|string|max:255',
            'alert_thread_id'   => 'nullable|integer',
            'active'            => 'nullable|boolean',
            'send_ack'          => 'nullable|boolean',
            'notes'             => 'nullable|string',
        ]);

        $validated['active']   = $request->boolean('active', true);
        $validated['send_ack'] = $request->boolean('send_ack', true);

        $tag = trim($validated['identifier_tag']);
        if (!str_starts_with($tag, '[') && !str_ends_with($tag, ']')) {
            $tag = "[{$tag}]";
        }
        $validated['identifier_tag'] = $tag;

        $requirement->update($validated);

        return redirect()->route('reports.requirements.index')->with('success', 'បានកែប្រែកាលវិភាគរបាយការណ៍ដោយជោគជ័យ (Report requirement updated).');
    }

    /**
     * Toggle requirement active state.
     */
    public function toggleRequirement(ReportRequirement $requirement): RedirectResponse
    {
        $requirement->update(['active' => !$requirement->active]);
        $statusStr = $requirement->active ? 'បើកដំណើរការ (Activated)' : 'ផ្អាក (Deactivated)';
        return back()->with('success', "ស្ថានភាព: {$statusStr}");
    }

    /**
     * Delete requirement.
     */
    public function destroyRequirement(ReportRequirement $requirement): RedirectResponse
    {
        $requirement->delete();
        return redirect()->route('reports.requirements.index')->with('success', 'បានលុបកាលវិភាគរបាយការណ៍ជោគជ័យ (Requirement deleted).');
    }

    /**
     * Trigger manual deadline check now.
     */
    public function checkNow(DailyReportTrackerService $service): RedirectResponse
    {
        $alerts = $service->checkDeadlines();
        $count = count($alerts);

        if ($count > 0) {
            return back()->with('success', "បានពិនិត្យ និងផ្ញើសេចក្តីជូនដំណឹងចំនួន {$count} ក្រុមជោគជ័យ (Dispatched {$count} alert(s)).");
        }

        return back()->with('info', 'បានពិនិត្យរួចរាល់ — មិនមានរបាយការណ៍យឺតថ្មីដែលត្រូវជូនដំណឹងឡើយ (All checked, no new alerts needed).');
    }

    /**
     * Send daily summary now.
     */
    public function sendSummaryNow(Request $request, DailyReportTrackerService $service): RedirectResponse
    {
        $date = $request->input('date', Carbon::now('Asia/Phnom_Penh')->toDateString());
        $success = $service->sendDailySummary($date);

        if ($success) {
            return back()->with('success', 'បានផ្ញើសេចក្តីសង្ខេបប្រចាំថ្ងៃទៅ Telegram រួចរាល់ (Daily summary sent).');
        }

        return back()->with('error', 'មិនអាចផ្ញើសេចក្តីសង្ខេបបានទេ សូមពិនិត្យការកំណត់ Telegram Alert Chat ID។');
    }

    /**
     * Re-initialize / sync today's report tracking records.
     */
    public function syncToday(DailyReportTrackerService $service): RedirectResponse
    {
        $created = $service->initializeDayRecords();
        return back()->with('success', "បានធ្វើសមកាលកម្មទិន្នន័យ {$created} កំណត់ត្រាសម្រាប់ថ្ងៃនេះ (Synced {$created} records for today).");
    }

    /**
     * Reset today's alert status for maintenance / testing.
     */
    public function resetTodayAlerts(): RedirectResponse
    {
        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();

        $updated = DailyReportSubmission::where('report_date', $today)
            ->whereNotNull('alert_sent_at')
            ->update([
                'alert_sent_at' => null,
                'status'        => 'pending',
            ]);

        return back()->with('success', "បានកំណត់សេចក្តីជូនដំណឹងឡើងវិញចំនួន {$updated} កំណត់ត្រា (Reset alerts for {$updated} records. You can now re-test alerts).");
    }

    /**
     * Get submission content for modal.
     */
    public function getSubmission(DailyReportSubmission $submission): JsonResponse
    {
        $submission->load('requirement');

        return response()->json([
            'id'             => $submission->id,
            'staff_name'     => $submission->requirement->staff_name,
            'report_title'   => $submission->requirement->report_title,
            'status'         => $submission->status,
            'khmer_status'   => $submission->khmer_status,
            'submitted_at'   => $submission->formatted_submitted_at,
            'deadline_at'    => $submission->formatted_deadline,
            'late_minutes'   => $submission->late_minutes,
            'revision_count' => $submission->revision_count,
            'message_text'   => $submission->message_text,
        ]);
    }
}