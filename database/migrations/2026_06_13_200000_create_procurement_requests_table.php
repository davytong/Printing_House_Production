<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('procurement_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique()->nullable();
            $table->date('request_date');
            $table->string('requester');
            $table->string('department')->nullable();
            $table->string('supplier_name');                       // store / vendor name
            $table->string('category');                            // consumable, spare_part, component, service, equipment, other
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total_amount', 14, 2)->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'ordered', 'received', 'completed', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('procurement_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');                           // pdf, image, doc, excel, other
            $table->unsignedInteger('file_size')->default(0);     // bytes
            $table->string('uploaded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('procurement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained()->cascadeOnDelete();
            $table->string('action');                              // created, updated, status_changed, file_uploaded, etc.
            $table->string('performed_by')->nullable();
            $table->text('details')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_logs');
        Schema::dropIfExists('procurement_attachments');
        Schema::dropIfExists('procurement_requests');
    }
};
