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
        
        // ── Batch filter (NEW) ────────────────────
        $batchId = $request->get('batch');
        $batches = \App\Models\ProductionBatch::orderBy('id', 'desc')->get();
        $selectedBatch = $batchId ? \App\Models\ProductionBatch::find($batchId) : null;

        // ── Production trend ──────────────────────
        $productionQuery = DailyPrint::selectRaw('date, SUM(printed_today) as total')
            ->where('date', '>=', $from->toDateString());
        
        // Filter by batch if selected
        if ($batchId) {
            $productionQuery->whereHas('book', function($q) use ($batchId) {
                $q->where('batch_id', $batchId);
            });
        }
        
        $productionTrend = \Illuminate\Support\Facades\Cache::remember("analytics_trend_{$period}_{$batchId}", 300, function () use ($productionQuery) {
            return $productionQuery->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date');
        });

        $labels = [];
        $values = [];
        for ($i = (int) $period - 1; $i >= 0; $i--) {
            $d        = now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($d)->format('d/m');
            $values[] = (int) ($productionTrend[$d] ?? 0);
        }

        // ── Top books by output ───────────────────
        $topBooksQuery = DailyPrint::selectRaw('book_id, SUM(printed_today) as total_in_period')
            ->where('date', '>=', $from->toDateString());
            
        if ($batchId) {
            $topBooksQuery->whereHas('book', function($q) use ($batchId) {
                $q->where('batch_id', $batchId);
            });
        }
        
        $topBooks = $topBooksQuery->groupBy('book_id')
            ->orderByDesc('total_in_period')
            ->with('book')
            ->take(10)
            ->get();

        // ── Production by grade ───────────────────
        $byGradeQuery = Book::selectRaw('grade, SUM(total_printed) as printed, SUM(target_qty) as target')
            ->whereNotNull('grade');
            
        if ($batchId) {
            $byGradeQuery->where('batch_id', $batchId);
        }
        
        $byGrade = \Illuminate\Support\Facades\Cache::remember("analytics_by_grade_{$batchId}", 300, function () use ($byGradeQuery) {
            return $byGradeQuery->groupBy('grade')
                ->orderBy('grade')
                ->get();
        });

        // ── Production by category ────────────────
        $byCategoryQuery = Book::selectRaw('category, SUM(total_printed) as printed, SUM(target_qty) as target, COUNT(*) as books')
            ->groupBy('category');
            
        if ($batchId) {
            $byCategoryQuery->where('batch_id', $batchId);
        }
        
        $byCategory = \Illuminate\Support\Facades\Cache::remember("analytics_by_category_{$batchId}", 300, function () use ($byCategoryQuery) {
            return $byCategoryQuery->get();
        });

        // ── Request stats ─────────────────────────
        $requestStats = PrintRequest::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        // ── Monthly production ────────────────────
        $monthExpr = \DB::connection()->getDriverName() === 'sqlite'
            ? 'strftime("%Y-%m", date) as month'
            : 'DATE_FORMAT(date, "%Y-%m") as month';

        $monthlyProductionQuery = DailyPrint::selectRaw("{$monthExpr}, SUM(printed_today) as total")
            ->where('date', '>=', now()->subMonths(6)->toDateString());
            
        if ($batchId) {
            $monthlyProductionQuery->whereHas('book', function($q) use ($batchId) {
                $q->where('batch_id', $batchId);
            });
        }
        
        $monthlyProduction = \Illuminate\Support\Facades\Cache::remember("analytics_monthly_{$batchId}", 300, function () use ($monthlyProductionQuery) {
            return $monthlyProductionQuery->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month');
        });

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
            'poStats',
            'batches', 'selectedBatch', 'batchId'
        ));
    }
}
