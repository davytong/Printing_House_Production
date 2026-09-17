<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DailyReportSubmission;
use App\Models\ReportRequirement;
use App\Models\Setting;
use App\Models\TelegramGroup;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the Settings and Profile view.
     */
    public function index(Request $request): View
    {
        $userName = session('user_name', 'Guest / ភ្ញៀវ');
        $userPosition = session('user_position', 'unknown');
        $userRole = session('user_role', 'none');

        // Fetch recent activities of this user
        $activityLogs = ActivityLog::where('user_name', $userName)
            ->latest()
            ->take(15)
            ->get();

        // ── Daily Report Tracking Maintenance & Health Stats ──
        $today = Carbon::now('Asia/Phnom_Penh')->toDateString();

        $trackingStats = [
            'total_requirements'  => ReportRequirement::count(),
            'active_requirements' => ReportRequirement::active()->count(),
            'today_submitted'     => DailyReportSubmission::where('report_date', $today)->where('status', 'submitted')->count(),
            'today_late'          => DailyReportSubmission::where('report_date', $today)->where('status', 'late')->count(),
            'today_missed'        => DailyReportSubmission::where('report_date', $today)->where('status', 'missed')->count(),
            'today_pending'       => DailyReportSubmission::where('report_date', $today)->where('status', 'pending')->count(),
            'today_alerts_sent'   => DailyReportSubmission::where('report_date', $today)->whereNotNull('alert_sent_at')->count(),
            'local_time'          => Carbon::now('Asia/Phnom_Penh')->format('d/m/Y h:i:s A'),
        ];

        $reportSettings = [
            'duplicate_policy' => Setting::get('report_duplicate_policy', 'first_valid'),
            'send_ack'         => Setting::get('report_send_ack', '1'),
            'alert_chat_id'    => Setting::get('report_alert_chat_id', config('services.telegram.alert_chat_id')),
            'alert_thread_id'  => Setting::get('report_alert_thread_id', config('services.telegram.alert_thread_id')),
        ];

        $telegramGroups = TelegramGroup::orderBy('chat_id')->orderBy('message_thread_id')->get();
        $groupedChats   = $telegramGroups->groupBy('chat_id');
        $recentRequirements = ReportRequirement::orderBy('deadline_time')->take(5)->get();

        return view('settings.index', compact(
            'userName',
            'userPosition',
            'userRole',
            'activityLogs',
            'trackingStats',
            'reportSettings',
            'telegramGroups',
            'groupedChats',
            'recentRequirements'
        ));
    }

    /**
     * Save report tracking configuration settings.
     */
    public function saveReportSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_duplicate_policy' => 'required|in:first_valid,latest_valid',
            'report_send_ack'         => 'nullable|boolean',
            'report_alert_target'     => 'nullable|string|max:255',
            'report_alert_chat_id'    => 'nullable|string|max:255',
            'report_alert_thread_id'  => 'nullable|integer',
        ]);

        $chatId   = $validated['report_alert_chat_id'] ?? '';
        $threadId = $validated['report_alert_thread_id'] ?? null;

        // If a dropdown option was chosen (e.g. "chatId|threadId" or empty)
        if ($request->filled('report_alert_target') && $request->input('report_alert_target') !== 'custom') {
            $parts    = explode('|', $request->input('report_alert_target'), 2);
            $chatId   = $parts[0] ?? '';
            $threadId = (!empty($parts[1]) || (isset($parts[1]) && $parts[1] !== '')) ? (int) $parts[1] : null;
        } elseif ($request->input('report_alert_target') === '') {
            $chatId   = '';
            $threadId = null;
        }

        Setting::set('report_duplicate_policy', $validated['report_duplicate_policy']);
        Setting::set('report_send_ack', $request->has('report_send_ack') ? '1' : '0');
        Setting::set('report_alert_chat_id', (string) $chatId);
        Setting::set('report_alert_thread_id', $threadId !== null ? (string) $threadId : '');

        ActivityLog::record(
            'Updated Report Tracking Settings',
            'Administrator updated daily production report tracking configuration and alert destination.',
            'settings'
        );

        return back()->with('success', 'បានរក្សាទុកការកំណត់ប្រព័ន្ធតាមដានរបាយការណ៍ដោយជោគជ័យ (Report tracking settings saved).');
    }
}