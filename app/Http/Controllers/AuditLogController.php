<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Display enterprise audit logs.
     */
    public function index(Request $request): View
    {
        if (!RoleService::can('view_audit_logs')) {
            abort(403, 'Unauthorized access to Audit Trail.');
        }

        $module   = $request->query('module', 'all');
        $search   = $request->query('search');
        $user     = $request->query('user');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $query = ActivityLog::query()
            ->forModule($module)
            ->search($search)
            ->dateRange($dateFrom, $dateTo);

        if ($user && trim($user) !== '') {
            $query->where('user_name', $user);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        // Distinct user names for filter dropdown
        $users = ActivityLog::select('user_name')
            ->distinct()
            ->orderBy('user_name')
            ->pluck('user_name');

        // Audit Summary Stats
        $todayStart = today()->startOfDay();
        $stats = [
            'total_today'      => ActivityLog::where('created_at', '>=', $todayStart)->count(),
            'production_today' => ActivityLog::where('created_at', '>=', $todayStart)->where('module', 'production')->count(),
            'inventory_today'  => ActivityLog::where('created_at', '>=', $todayStart)->where('module', 'inventory')->count(),
            'security_alerts'  => ActivityLog::where('created_at', '>=', $todayStart)->where('action', 'like', '%Failed%')->count(),
        ];

        return view('audit.index', compact('logs', 'module', 'search', 'user', 'dateFrom', 'dateTo', 'users', 'stats'));
    }

    /**
     * Export audit trail to CSV format.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        if (!RoleService::can('view_audit_logs')) {
            abort(403, 'Unauthorized.');
        }

        $module   = $request->query('module', 'all');
        $search   = $request->query('search');
        $user     = $request->query('user');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $filename = 'audit-log-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($module, $search, $user, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Khmer text in Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, ['ID', 'Date & Time', 'User Name', 'Position', 'Module', 'Action', 'Details', 'IP Address']);

            ActivityLog::query()
                ->forModule($module)
                ->search($search)
                ->dateRange($dateFrom, $dateTo)
                ->when($user, fn($q) => $q->where('user_name', $user))
                ->latest()
                ->chunk(200, function ($chunkLogs) use ($handle) {
                    foreach ($chunkLogs as $log) {
                        fputcsv($handle, [
                            $log->id,
                            $log->created_at->format('Y-m-d H:i:s'),
                            $log->user_name,
                            $log->position,
                            $log->module,
                            $log->action,
                            $log->details,
                            $log->ip_address,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Clean old audit logs (Admins only).
     */
    public function cleanOld(Request $request)
    {
        if (!RoleService::can('manage_audit_logs')) {
            abort(403, 'Only Admins can clear audit logs.');
        }

        $days = (int) $request->input('days', 90);
        $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        ActivityLog::record('Purged Audit Logs', "Removed {$deleted} audit entries older than {$days} days", 'settings');

        return back()->with('success', "បានលុបកំណត់ត្រាចាស់ៗចំនួន {$deleted} ដោយជោគជ័យ! / Successfully purged {$deleted} old log entries.");
    }
}
