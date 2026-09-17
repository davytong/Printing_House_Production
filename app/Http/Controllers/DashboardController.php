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
        // ── Production (Batch-Aware) ──────────────
        $currentBatch = \App\Models\ProductionBatch::current();
        $allBatches   = \App\Models\ProductionBatch::with('books')->orderBy('id', 'desc')->get();
        
        // Overall totals (all batches) — single SQL aggregate, cached for 5 minutes
        $bookAgg = \Illuminate\Support\Facades\Cache::remember('dashboard_book_agg', 300, function () {
            return Book::selectRaw(
                'COUNT(*) as cnt, '
                . 'COALESCE(SUM(total_printed),0) as printed, '
                . 'COALESCE(SUM(target_qty),0) as target, '
                . 'SUM(CASE WHEN total_printed >= target_qty THEN 1 ELSE 0 END) as done_cnt, '
                . 'SUM(CASE WHEN total_printed > 0 AND total_printed < target_qty THEN 1 ELSE 0 END) as prog_cnt'
            )->first();
        });

        $totalBooks    = (int) $bookAgg->cnt;
        $totalPrinted  = (int) $bookAgg->printed;
        $totalTarget   = (int) $bookAgg->target;
        $overallPct    = $totalTarget > 0 ? round($totalPrinted / $totalTarget * 100) : 0;
        $doneCount     = (int) $bookAgg->done_cnt;
        $inProgress    = (int) $bookAgg->prog_cnt;

        // Current batch statistics
        $currentBatchBooks = $currentBatch->books;
        $currentBatchTotal = $currentBatchBooks->sum('target_qty');
        $currentBatchPrinted = $currentBatchBooks->sum('total_printed');
        $currentBatchPct = $currentBatchTotal > 0 ? round($currentBatchPrinted / $currentBatchTotal * 100) : 0;
        
        // Batch breakdown for display
        $batchStats = $allBatches->map(function($batch) {
            $books = $batch->books;
            $target = $books->sum('target_qty');
            $printed = $books->sum('total_printed');
            $pct = $target > 0 ? round($printed / $target * 100) : 0;
            
            return [
                'id' => $batch->id,
                'name' => $batch->name,
                'status' => $batch->status,
                'book_count' => $books->count(),
                'target' => $target,
                'printed' => $printed,
                'percentage' => $pct,
                'started_at' => $batch->started_at,
                'completed_at' => $batch->completed_at,
            ];
        });

        // Daily trend: last 7 days, cached for 5 minutes
        $trend = \Illuminate\Support\Facades\Cache::remember('dashboard_trend', 300, function () {
            return DailyPrint::selectRaw('date, SUM(printed_today) as total')
                ->where('date', '>=', now()->subDays(6)->toDateString())
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date');
        });

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
        $machineStats = Machine::selectRaw("
            SUM(CASE WHEN status = 'operational' THEN 1 ELSE 0 END) as operational,
            SUM(CASE WHEN status = 'breakdown' THEN 1 ELSE 0 END) as breakdown,
            COUNT(*) as total
        ")->first();

        $operationalMachines = (int) ($machineStats->operational ?? 0);
        $breakdowns          = (int) ($machineStats->breakdown ?? 0);
        $totalMachines       = (int) ($machineStats->total ?? 0);

        $maintenanceDue      = Machine::whereDate('next_maintenance', '<=', now()->addDays(7))
            ->where('status', '!=', 'retired')
            ->count();

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

        // ── Material Low Stock Summary (Category & Critical breakdown) ──
        $allActiveMaterials = \App\Models\Material::where('status', 'active')->get();
        $movesByMat = \App\Models\StockMovement::whereIn('material_id', $allActiveMaterials->pluck('id'))->get()->groupBy('material_id');

        $criticalCount  = 0;
        $lowCount       = 0;
        $outOfStockCount= 0;
        $materialLowStockAlerts = collect();

        foreach ($allActiveMaterials as $m) {
            $stock = \App\Models\Material::currentStockFromMovements($movesByMat->get($m->id, collect()));
            $m->calculated_stock = $stock;
            $status = $m->stockStatus($stock);

            if ($status !== 'NORMAL') {
                if ($status === 'OUT_OF_STOCK') $outOfStockCount++;
                elseif ($status === 'CRITICAL') $criticalCount++;
                elseif ($status === 'LOW_STOCK') $lowCount++;

                $materialLowStockAlerts->push($m);
            }
        }

        $totalLowStockMaterials = $materialLowStockAlerts->count();
        $materialLowStockAlerts = $materialLowStockAlerts->take(5);

        // ── Telegram Bot Status ──────────────────
        $telegramToken = config('services.telegram.bot_token');
        $telegramAlertChatId = \App\Models\Setting::get('alert_chat_id', config('services.telegram.alert_chat_id'));
        $telegramStatus = $telegramToken && $telegramAlertChatId ? 'active' : ($telegramToken ? 'pending' : 'disconnected');

        // ── Weekly Top Takers ─────────────────────
        $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $weekTopTakers = \App\Models\StockMovement::selectRaw(
                'performed_by, COUNT(*) as move_count, SUM(CASE WHEN type="out" THEN quantity ELSE 0 END) as out_qty'
            )
            ->where('movement_date', '>=', $weekStart)
            ->whereNotNull('performed_by')
            ->where('performed_by', '!=', '')
            ->groupBy('performed_by')
            ->orderByDesc('move_count')
            ->limit(5)
            ->get();
        $todayOutput = (int) DailyPrint::whereDate('date', today())->sum('printed_today');
        $recentActivities = \App\Models\ActivityLog::latest()->take(6)->get();

        return view('dashboard.index', compact(
            'totalBooks', 'totalPrinted', 'totalTarget', 'overallPct',
            'doneCount', 'inProgress',
            'currentBatch', 'currentBatchTotal', 'currentBatchPrinted', 'currentBatchPct',
            'batchStats', 'allBatches',
            'trendLabels', 'trendValues',
            'pendingRequests', 'urgentRequests', 'recentRequests',
            'lowStockItems', 'inventoryAlerts',
            'totalLowStockMaterials', 'criticalCount', 'lowCount', 'outOfStockCount', 'materialLowStockAlerts',
            'operationalMachines', 'totalMachines', 'maintenanceDue', 'breakdowns',
            'pendingPOs', 'overduePOs',
            'unreadNotifs', 'notifications',
            'upcomingMaintenance', 'telegramStatus',
            'weekTopTakers', 'todayOutput', 'recentActivities'
        ));
    }
}
