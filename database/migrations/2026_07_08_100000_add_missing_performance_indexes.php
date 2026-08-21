<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds missing indexes identified in performance audit:
 * - books.batch_id (most production queries filter by this)
 * - print_requests.(status, priority) for urgent count on dashboard
 * - purchase_orders.(status, expected_date) for overdue PO query on dashboard
 * - inventory_items.status (always filtered active)
 * - system_notifications.is_read (queried on every page load via sidebar)
 * - procurement_requests.(status, request_date)
 * - stock_movements.created_at (used as tiebreak in currentStockFromMovements)
 */
return new class extends Migration
{
    public function up(): void
    {
        // books.batch_id — core production filter
        Schema::table('books', function (Blueprint $table) {
            if (!$this->indexExists('books', 'books_batch_id_idx')) {
                $table->index('batch_id', 'books_batch_id_idx');
            }
            if (!$this->indexExists('books', 'books_batch_category_idx')) {
                $table->index(['batch_id', 'category'], 'books_batch_category_idx');
            }
        });

        // print_requests — dashboard urgent count
        Schema::table('print_requests', function (Blueprint $table) {
            if (!$this->indexExists('print_requests', 'pr_status_priority_idx')) {
                $table->index(['status', 'priority'], 'pr_status_priority_idx');
            }
        });

        // purchase_orders — overdue PO query on every dashboard load
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!$this->indexExists('purchase_orders', 'po_status_idx')) {
                $table->index('status', 'po_status_idx');
            }
            if (!$this->indexExists('purchase_orders', 'po_status_expected_idx')) {
                $table->index(['status', 'expected_date'], 'po_status_expected_idx');
            }
        });

        // inventory_items — always filtered by status = 'active'
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!$this->indexExists('inventory_items', 'inv_status_idx')) {
                $table->index('status', 'inv_status_idx');
            }
        });

        // system_notifications — queried on every page load
        Schema::table('system_notifications', function (Blueprint $table) {
            if (!$this->indexExists('system_notifications', 'sn_is_read_idx')) {
                $table->index('is_read', 'sn_is_read_idx');
            }
            if (!$this->indexExists('system_notifications', 'sn_read_created_idx')) {
                $table->index(['is_read', 'created_at'], 'sn_read_created_idx');
            }
        });

        // procurement_requests — index/analytics queries
        Schema::table('procurement_requests', function (Blueprint $table) {
            if (!$this->indexExists('procurement_requests', 'procr_status_idx')) {
                $table->index('status', 'procr_status_idx');
            }
            if (!$this->indexExists('procurement_requests', 'procr_date_idx')) {
                $table->index('request_date', 'procr_date_idx');
            }
        });

        // stock_movements.created_at — used as tiebreak sort in currentStockFromMovements
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!$this->indexExists('stock_movements', 'sm_created_at_idx')) {
                $table->index('created_at', 'sm_created_at_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('books_batch_id_idx');
            $table->dropIndex('books_batch_category_idx');
        });
        Schema::table('print_requests', function (Blueprint $table) {
            $table->dropIndex('pr_status_priority_idx');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('po_status_idx');
            $table->dropIndex('po_status_expected_idx');
        });
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropIndex('inv_status_idx');
        });
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->dropIndex('sn_is_read_idx');
            $table->dropIndex('sn_read_created_idx');
        });
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropIndex('procr_status_idx');
            $table->dropIndex('procr_date_idx');
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('sm_created_at_idx');
        });
    }

    /**
     * Check if an index already exists (safe re-run).
     * Uses a cross-database approach: information_schema for MySQL/MariaDB,
     * pragma_index_list for SQLite (used in tests).
     */
    private function indexExists(string $table, string $index): bool
    {
        $driver = \DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: query the pragma index list for the table
            $indexes = \DB::select(
                "SELECT name FROM pragma_index_list(?) WHERE name = ?",
                [$table, $index]
            );
            return count($indexes) > 0;
        }

        // MySQL / MariaDB
        $indexes = \DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$index]
        );
        return count($indexes) > 0;
    }
};
