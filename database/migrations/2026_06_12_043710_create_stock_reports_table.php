<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();      // compressed image
            $table->json('summary_data')->nullable();      // cached snapshot of stock at report time
            $table->boolean('telegram_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reports');
    }
};
