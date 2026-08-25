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
        if (!Schema::hasTable('production_task_progress')) {
            Schema::create('production_task_progress', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->integer('day');
            $table->string('process');
            $table->string('task_name');
            $table->integer('total_quantity')->nullable()->comment('Total copies/books to print');
            $table->integer('printed_quantity')->default(0)->comment('How many printed today');
            $table->text('note')->nullable();
            $table->timestamps();
            
            // Indexes for fast lookup
            $table->index(['year', 'month', 'day', 'process']);
            $table->index(['task_name']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_task_progress');
    }
};
