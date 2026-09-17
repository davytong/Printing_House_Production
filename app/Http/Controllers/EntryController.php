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
            'full_name'  => 'required|string|max:255',
            'position'   => 'required|in:paper_report,press_report,finishing_report,procurement,store,admin',
            'admin_pin'  => 'nullable|string',
        ]);

        // Sanitize name to prevent XSS
        $fullName = strip_tags(trim($data['full_name']));
        if (empty($fullName)) {
            return back()->withErrors(['full_name' => 'Invalid name format. / ឈ្មោះមិនត្រឹមត្រូវ។'])->withInput();
        }

        // Secure Admin verification
        if ($data['position'] === 'admin') {
            $expectedPin = (string) (\App\Models\Setting::get('admin_pin') ?: env('ADMIN_PIN', '1234'));
            $enteredPin  = (string) $request->input('admin_pin', '');

            if ($enteredPin !== $expectedPin) {
                // Log unauthorized attempt to audit logs
                ActivityLog::record(
                    'Failed Admin Login',
                    "Unauthorized admin login attempt by '{$fullName}' (IP: {$request->ip()})",
                    'auth'
                );

                return back()
                    ->withErrors(['admin_pin' => 'លេខកូដសម្ងាត់ Admin មិនត្រឹមត្រូវទេ។ / Incorrect Admin PIN.'])
                    ->withInput();
            }
        }

        // Get role for position
        $role = \App\Services\RoleService::getRoleForPosition($data['position']);

        // Prevent Session Fixation by regenerating the session identifier
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        // Store in session
        session([
            'user_name'     => $fullName,
            'user_position' => $data['position'],
            'user_role'     => $role,
            'logged_in_at'  => now()->toDateTimeString(),
        ]);

        // Log successful activity
        ActivityLog::record(
            'Login',
            "Entered system as " . self::positionLabel($data['position']) . " (Role: {$role})",
            'auth'
        );

        // Redirect by position
        return redirect(self::dashboardRoute($data['position']));
    }

    /**
     * Logout — clear session.
     */
    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record('Logout', 'Left the system', 'auth');
        session()->forget(['user_name', 'user_position', 'user_role']);
        if ($request->hasSession()) {
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
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
