<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('print_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();             // REQ-2026-0001
            $table->string('title');                              // What is being requested
            $table->string('requester_name');
            $table->string('department')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_production', 'completed', 'cancelled'])->default('pending');
            $table->integer('quantity_requested');
            $table->string('book_type')->nullable();              // textbook, workbook, etc.
            $table->string('grade')->nullable();
            $table->date('required_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_requests');
    }
};
