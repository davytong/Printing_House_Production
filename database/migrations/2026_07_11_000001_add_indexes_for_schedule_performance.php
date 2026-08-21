<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing indexes for schedule performance
     */
    public function up(): void
    {
        Schema::table('production_schedules', function (Blueprint $table) {
            // Add index on status for filtering
            $table->index('status', 'idx_production_schedules_status');
            
            // Composite index for date range queries
            $table->index(['year', 'month', 'day'], 'idx_production_schedules_date');
            
            // Composite index for process queries
            $table->index(['year', 'month', 'process'], 'idx_production_schedules_process');
            
            // Full-text index for search (MySQL only)
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE production_schedules ADD FULLTEXT idx_task_search (task, note)');
            }
        });

        Schema::table('schedule_delay_logs', function (Blueprint $table) {
            // Add index on reason_type for filtering
            $table->index('reason_type', 'idx_delay_logs_reason_type');
            
            // Composite index for date queries
            $table->index(['year', 'month'], 'idx_delay_logs_date');
        });

        Schema::table('production_task_progress', function (Blueprint $table) {
            // Composite index for progress lookups
            $table->index(['year', 'month', 'process', 'task_name'], 'idx_task_progress_lookup');
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
