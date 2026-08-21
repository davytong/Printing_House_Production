<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds indexes on frequently filtered / grouped columns to speed up
 * reports, dashboards and stock lookups. FK columns are already indexed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_prints', function (Blueprint $table) {
            $table->index('date', 'dp_date_idx');
            $table->index(['book_id', 'date'], 'dp_book_date_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('movement_date', 'sm_date_idx');
            $table->index('type', 'sm_type_idx');
            $table->index(['material_id', 'type'], 'sm_mat_type_idx');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->index('status', 'mat_status_idx');
            $table->index('category', 'mat_category_idx');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->index('grade', 'book_grade_idx');
        });
    }

    public function down(): void
    {
        Schema::table('daily_prints', function (Blueprint $table) {
            $table->dropIndex('dp_date_idx');
            $table->dropIndex('dp_book_date_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('sm_date_idx');
            $table->dropIndex('sm_type_idx');
            $table->dropIndex('sm_mat_type_idx');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex('mat_status_idx');
            $table->dropIndex('mat_category_idx');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('book_grade_idx');
        });
    }
};
