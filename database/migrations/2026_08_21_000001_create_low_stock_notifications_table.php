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
        if (!Schema::hasTable('low_stock_notifications')) {
            Schema::create('low_stock_notifications', function (Blueprint $table) {
                $table->id();
                $table->date('report_date');
                $table->string('category', 50)->nullable();
                $table->unsignedBigInteger('destination_group_id')->nullable();
                $table->string('destination_group_name', 255)->nullable();
                $table->integer('items_count')->default(0);
                $table->json('items_payload')->nullable();
                $table->text('message');
                $table->enum('status', ['PENDING', 'SENT', 'FAILED', 'CANCELLED'])->default('PENDING');
                $table->string('sent_by', 100)->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['report_date', 'status']);
                $table->index('category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('low_stock_notifications');
    }
};
