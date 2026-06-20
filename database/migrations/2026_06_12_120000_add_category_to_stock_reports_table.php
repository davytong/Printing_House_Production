<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_reports', function (Blueprint $table) {
            // null = combined report (all categories); otherwise paper/film/offset
            $table->string('category')->nullable()->after('report_date');
        });
    }

    public function down(): void
    {
        Schema::table('stock_reports', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
