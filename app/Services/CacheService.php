<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache key definitions with TTL (in seconds)
     */
    const KEYS = [
        'dashboard:stats' => 300,        // 5 minutes
        'dashboard:trend' => 300,        // 5 minutes
        'batch:' => 60,                  // 1 minute per batch
        'materials:low_stock' => 60,     // 1 minute
        'machines:status' => 120,        // 2 minutes
        'requests:pending' => 60,        // 1 minute
        'inventory:alerts' => 60,        // 1 minute
    ];

    /**
     * Clear dashboard-related caches
     */
    public static function invalidateDashboard(): void
    {
        Cache::forget('dashboard_book_agg');
        Cache::forget('dashboard_trend');
        Cache::forget('dashboard:stats');
        Cache::forget('dashboard:trend');
    }

    /**
     * Clear batch-specific caches
     */
    public static function invalidateBatch(?int $batchId = null): void
    {
        if ($batchId) {
            Cache::forget("batch:{$batchId}");
            Cache::forget("batch:{$batchId}:books");
            Cache::forget("batch:{$batchId}:stats");
        }
        
        // Always clear dashboard when batch changes
        static::invalidateDashboard();
    }

    /**
     * Clear material/inventory caches
     */
    public static function invalidateInventory(): void
    {
        Cache::forget('materials:low_stock');
        Cache::forget('inventory:alerts');
        static::invalidateDashboard(); // Affects dashboard low stock widget
    }

    /**
     * Clear request caches
     */
    public static function invalidateRequests(): void
    {
        Cache::forget('requests:pending');
        Cache::forget('requests:urgent');
        static::invalidateDashboard(); // Affects dashboard request counter
    }

    /**
     * Clear machine caches
     */
    public static function invalidateMachines(): void
    {
        Cache::forget('machines:status');
        Cache::forget('machines:maintenance_due');
        static::invalidateDashboard(); // Affects dashboard machine stats
    }

    /**
     * Clear all application caches
     */
    public static function invalidateAll(): void
    {
        Cache::flush();
    }

    /**
     * Warm up common caches
     */
    public static function warmUp(): void
    {
        // This can be called after batch changes or nightly
        Cache::remember('dashboard_book_agg', 300, function () {
            return \App\Models\Book::selectRaw(
                'COUNT(*) as cnt, '
                . 'COALESCE(SUM(total_printed),0) as printed, '
                . 'COALESCE(SUM(target_qty),0) as target, '
                . 'SUM(CASE WHEN total_printed >= target_qty THEN 1 ELSE 0 END) as done_cnt, '
                . 'SUM(CASE WHEN total_printed > 0 AND total_printed < target_qty THEN 1 ELSE 0 END) as prog_cnt'
            )->first();
        });
    }
}
