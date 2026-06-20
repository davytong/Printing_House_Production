<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\DailyPrint;
use App\Models\InventoryItem;
use App\Models\Machine;
use App\Models\MaintenanceSchedule;
use App\Models\PrintRequest;
use App\Models\PurchaseOrder;
use App\Models\SystemNotification;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Production ────────────────────────────
        $books         = Book::all();
        $totalBooks    = $books->count();
        $totalPrinted  = $books->sum('total_printed');
        $totalTarget   = $books->sum('target_qty');
        $overallPct    = $totalTarget > 0 ? round($totalPrinted / $totalTarget * 100) : 0;
        $doneCount     = $books->filter(fn($b) => $b->total_printed >= $b->target_qty)->count();
        $inProgress    = $books->filter(fn($b) => $b->total_printed > 0 && $b->total_printed < $b->target_qty)->count();

        // Daily trend: last 7 days
        $trend = DailyPrint::selectRaw('date, SUM(printed_today) as total')
            ->where('date', '>=', now()->subDays(6)->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $trendLabels = collect();
        $trendValues = collect();
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $trendLabels->push(Carbon::parse($d)->format('d/m'));
            $trendValues->push((int) ($trend[$d] ?? 0));
        }

        // ── Requests ─────────────────────────────
        $pendingRequests  = PrintRequest::where('status', 'pending')->count();
        $urgentRequests   = PrintRequest::where('status', 'pending')->where('priority', 'urgent')->count();
        $recentRequests   = PrintRequest::latest()->take(5)->get();

        // ── Inventory ────────────────────────────
        $lowStockItems    = InventoryItem::where('status', 'active')
            ->whereColumn('quantity_in_stock', '<=', 'minimum_stock')
            ->count();
        $inventoryAlerts  = InventoryItem::where('status', 'active')
            ->whereColumn('quantity_in_stock', '<=', 'minimum_stock')
            ->take(5)->get();

        // ── Machines ─────────────────────────────
        $operationalMachines = Machine::where('status', 'operational')->count();
        $totalMachines       = Machine::count();
        $maintenanceDue      = Machine::whereDate('next_maintenance', '<=', now()->addDays(7))
            ->where('status', '!=', 'retired')
            ->count();
        $breakdowns          = Machine::where('status', 'breakdown')->count();

        // ── Purchase Orders ───────────────────────
        $pendingPOs   = PurchaseOrder::whereIn('status', ['draft', 'sent'])->count();
        $overduePOs   = PurchaseOrder::whereIn('status', ['sent', 'partially_received'])
            ->whereDate('expected_date', '<', today())
            ->count();

        // ── Notifications ─────────────────────────
        $unreadNotifs = SystemNotification::where('is_read', false)->count();
        $notifications = SystemNotification::where('is_read', false)
            ->latest()->take(8)->get();

        // ── Upcoming maintenance ──────────────────
        $upcomingMaintenance = MaintenanceSchedule::with('machine')
            ->whereIn('status', ['scheduled'])
            ->whereDate('scheduled_date', '<=', now()->addDays(14))
            ->orderBy('scheduled_date')
            ->take(5)->get();

        return view('dashboard.index', compact(
            'totalBooks', 'totalPrinted', 'totalTarget', 'overallPct',
            'doneCount', 'inProgress',
            'trendLabels', 'trendValues',
            'pendingRequests', 'urgentRequests', 'recentRequests',
            'lowStockItems', 'inventoryAlerts',
            'operationalMachines', 'totalMachines', 'maintenanceDue', 'breakdowns',
            'pendingPOs', 'overduePOs',
            'unreadNotifs', 'notifications',
            'upcomingMaintenance'
        ));
    }
}
