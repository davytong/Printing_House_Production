<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EntryController extends Controller
{
    /**
     * Show the entry screen (name + position).
     */
    public function show(): View
    {
        return view('entry');
    }

    /**
     * Process entry — store in session, redirect by position.
     */
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'position'  => 'required|in:paper_report,press_report,finishing_report,procurement,store,admin',
        ]);

        // Store in session
        session([
            'user_name'     => $data['full_name'],
            'user_position' => $data['position'],
            'logged_in_at'  => now()->toDateTimeString(),
        ]);

        // Log activity
        ActivityLog::record('Login', "Entered system as " . self::positionLabel($data['position']));

        // Redirect by position
        return redirect(self::dashboardRoute($data['position']));
    }

    /**
     * Logout — clear session.
     */
    public function logout(): RedirectResponse
    {
        ActivityLog::record('Logout', 'Left the system');
        session()->forget(['user_name', 'user_position', 'logged_in_at']);
        return redirect()->route('entry');
    }

    /**
     * Get the dashboard route for a position.
     */
    public static function dashboardRoute(string $position): string
    {
        return match($position) {
            'paper_report'     => '/stock/movements/daily?category=paper',
            'press_report'     => '/stock/movements/daily?category=consumable',
            'finishing_report' => '/stock/movements/daily?category=film',
            'procurement'      => '/procurement',
            'store'            => '/stock/materials',
            'admin'            => '/',
            default            => '/',
        };
    }

    /**
     * Human-readable position label.
     */
    public static function positionLabel(string $position): string
    {
        return match($position) {
            'paper_report'     => 'Paper Report',
            'press_report'     => 'Press Report',
            'finishing_report' => 'Finishing Report',
            'procurement'      => 'Procurement',
            'store'            => 'Store',
            'admin'            => 'Admin',
            default            => ucfirst($position),
        };
    }
}
