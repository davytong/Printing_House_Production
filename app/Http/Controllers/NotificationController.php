<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = SystemNotification::latest()->paginate(30);
        $unread        = SystemNotification::where('is_read', false)->count();
        return view('notifications.index', compact('notifications', 'unread'));
    }

    public function markRead(SystemNotification $notification): RedirectResponse
    {
        $notification->markRead();
        return back()->with('success', 'ការជូនដំណឹងត្រូវបានអាន');
    }

    public function markAllRead(): RedirectResponse
    {
        SystemNotification::where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'ការជូនដំណឹងទាំងអស់ត្រូវបានអាន');
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'unread' => SystemNotification::where('is_read', false)->count(),
        ]);
    }

    public function destroy(SystemNotification $notification): RedirectResponse
    {
        $notification->delete();
        return back()->with('success', 'ការជូនដំណឹងត្រូវបានលុប');
    }
}
