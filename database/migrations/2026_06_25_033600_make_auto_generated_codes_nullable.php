<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix all auto-generated code fields that should be nullable
        Schema::table('machines', function (Blueprint $table) {
            $table->string('code')->nullable()->change();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('code')->nullable()->change();
        });

        Schema::table('print_requests', function (Blueprint $table) {
            $table->string('request_code')->nullable()->change();
        });

        // procurement_requests and materials already nullable ✓
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->string('code')->nullable(false)->change();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('code')->nullable(false)->change();
        });

        Schema::table('print_requests', function (Blueprint $table) {
            $table->string('request_code')->nullable(false)->change();
        });
    }
};
