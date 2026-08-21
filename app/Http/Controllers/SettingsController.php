<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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

        return view('settings.index', compact('userName', 'userPosition', 'userRole', 'activityLogs'));
    }
}
