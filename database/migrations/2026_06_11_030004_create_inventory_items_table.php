<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                     // INV-001
            $table->string('name');
            $table->enum('type', ['paper', 'ink', 'plate', 'spare_part', 'chemical', 'other'])->default('other');
            $table->string('unit')->default('pcs');               // pcs, kg, liter, ream
            $table->decimal('quantity_in_stock', 12, 2)->default(0);
            $table->decimal('minimum_stock', 12, 2)->default(0);  // reorder threshold
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->string('location')->nullable();               // shelf / warehouse location
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
