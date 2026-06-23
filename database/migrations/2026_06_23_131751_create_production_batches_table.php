<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A production batch = one full printing round/cycle for all books.
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // e.g. "Batch 1", "June 2026 Round"
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->string('notes', 500)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Frozen result of each book when a batch is completed (history).
        Schema::create('batch_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('production_batches')->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained('books')->nullOnDelete();
            $table->string('title');
            $table->string('grade', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->unsignedInteger('target_qty')->default(0);
            $table->unsignedInteger('printed_qty')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_snapshots');
        Schema::dropIfExists('production_batches');
    }
};
