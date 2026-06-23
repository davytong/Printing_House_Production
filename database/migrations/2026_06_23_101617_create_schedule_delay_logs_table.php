<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_delay_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('process', 50);
            $table->string('original_task', 255);
            $table->unsignedTinyInteger('original_day');
            $table->unsignedTinyInteger('shifted_to_day');
            $table->enum('reason_type', ['urgent_task', 'machine_downtime']);
            $table->string('reason_detail', 500)->nullable(); // e.g. "Urgent: Emergency Reprint" or "Machine PRESS-001 down 8h"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_delay_logs');
    }
};
