<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\DailyPrint;
use App\Models\InventoryItem;
use App\Models\Machine;
use App\Models\MaintenanceSchedule;
use App\Models\PrintRequest;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', '30');
        $from   = now()->subDays((int) $period)->startOfDay();

        // ── Production trend ──────────────────────
        $productionTrend = DailyPrint::selectRaw('date, SUM(printed_today) as total')
            ->where('date', '>=', $from->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $values = [];
        for ($i = (int) $period - 1; $i >= 0; $i--) {
            $d        = now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($d)->format('d/m');
            $values[] = (int) ($productionTrend[$d] ?? 0);
        }

        // ── Top books by output ───────────────────
        $topBooks = DailyPrint::selectRaw('book_id, SUM(printed_today) as total_in_period')
            ->where('date', '>=', $from->toDateString())
            ->groupBy('book_id')
            ->orderByDesc('total_in_period')
            ->with('book')
            ->take(10)
            ->get();

        // ── Production by grade ───────────────────
        $byGrade = Book::selectRaw('grade, SUM(total_printed) as printed, SUM(target_qty) as target')
            ->whereNotNull('grade')
            ->groupBy('grade')
            ->orderBy('grade')
            ->get();

        // ── Production by category ────────────────
        $byCategory = Book::selectRaw('category, SUM(total_printed) as printed, SUM(target_qty) as target, COUNT(*) as books')
            ->groupBy('category')
            ->get();

        // ── Request stats ─────────────────────────
        $requestStats = PrintRequest::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        // ── Monthly production ────────────────────
        $monthlyProduction = DailyPrint::selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(printed_today) as total')
            ->where('date', '>=', now()->subMonths(6)->toDateString())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // ── Inventory overview ────────────────────
        $inventoryByType = InventoryItem::selectRaw('type, COUNT(*) as cnt, SUM(quantity_in_stock * unit_cost) as value')
            ->where('status', 'active')
            ->groupBy('type')
            ->get();

        // ── Machine uptime ────────────────────────
        $machineStats = Machine::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $maintenanceCosts = MaintenanceSchedule::where('status', 'completed')
            ->where('completed_date', '>=', $from->toDateString())
            ->sum('cost');

        // ── PO stats ──────────────────────────────
        $poStats = PurchaseOrder::selectRaw('status, COUNT(*) as cnt, SUM(total_amount) as amount')
            ->groupBy('status')
            ->get();

        return view('analytics.index', compact(
            'period', 'labels', 'values',
            'topBooks', 'byGrade', 'byCategory',
            'requestStats', 'monthlyProduction',
            'inventoryByType', 'machineStats', 'maintenanceCosts',
            'poStats'
        ));
    }
}
