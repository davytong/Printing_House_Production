<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->enum('category', ['paper', 'film', 'offset']);
            $table->string('sub_type')->nullable();       // e.g. Glossy 150gsm, Matt film, Cyan ink
            $table->string('size')->nullable();            // e.g. 65x90cm, A4, Roll 32"
            $table->string('unit')->default('sheet');      // sheet, kg, liter, roll, pcs, box
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->string('location')->nullable();        // warehouse shelf
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
