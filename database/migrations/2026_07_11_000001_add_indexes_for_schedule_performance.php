<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing indexes for schedule performance
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $conn = Schema::getConnection();
            $dbName = $conn->getDatabaseName();
            $result = $conn->select(
                "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$dbName, $table, $index]
            );
            return ($result[0]->cnt ?? 0) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function up(): void
    {
        Schema::table('production_schedules', function (Blueprint $table) {
            if (!$this->indexExists('production_schedules', 'idx_production_schedules_status')) {
                $table->index('status', 'idx_production_schedules_status');
            }
            if (!$this->indexExists('production_schedules', 'idx_production_schedules_date')) {
                $table->index(['year', 'month', 'day'], 'idx_production_schedules_date');
            }
            if (!$this->indexExists('production_schedules', 'idx_production_schedules_process')) {
                $table->index(['year', 'month', 'process'], 'idx_production_schedules_process');
            }
            if (DB::getDriverName() === 'mysql' && !$this->indexExists('production_schedules', 'idx_task_search')) {
                DB::statement('ALTER TABLE production_schedules ADD FULLTEXT idx_task_search (task, note)');
            }
        });

        Schema::table('schedule_delay_logs', function (Blueprint $table) {
            if (!$this->indexExists('schedule_delay_logs', 'idx_delay_logs_reason_type')) {
                $table->index('reason_type', 'idx_delay_logs_reason_type');
            }
            if (!$this->indexExists('schedule_delay_logs', 'idx_delay_logs_date')) {
                $table->index(['year', 'month'], 'idx_delay_logs_date');
            }
        });

        Schema::table('production_task_progress', function (Blueprint $table) {
            if (!$this->indexExists('production_task_progress', 'idx_task_progress_lookup')) {
                $table->index(['year', 'month', 'process', 'task_name'], 'idx_task_progress_lookup');
            }
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('production_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_production_schedules_status');
            $table->dropIndex('idx_production_schedules_date');
            $table->dropIndex('idx_production_schedules_process');
            
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE production_schedules DROP INDEX idx_task_search');
            }
        });

        Schema::table('schedule_delay_logs', function (Blueprint $table) {
            $table->dropIndex('idx_delay_logs_reason_type');
            $table->dropIndex('idx_delay_logs_date');
        });

        Schema::table('production_task_progress', function (Blueprint $table) {
            $table->dropIndex('idx_task_progress_lookup');
        });
    }
};
