<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Items table — each request has many items
        Schema::create('procurement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->string('category');  // consumable, spare_part, component, service, equipment, other
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total_amount', 14, 2)->nullable();
            $table->timestamps();
        });

        // Make procurement_requests a header-only table:
        // Remove single-item fields that now belong in items table
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Keep: request_number, request_date, requester, department, supplier_name, 
            //        priority, due_date, status, remarks
            // These single-item fields will be ignored if items exist
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_items');
    }
};
