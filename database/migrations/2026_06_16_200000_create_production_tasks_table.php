<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Tasks: the core unit of work ─────────────────────────
        Schema::create('production_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('process');                               // Design, Press, Folding, etc.
            $table->integer('duration_days')->default(1);            // how many working days
            $table->integer('duration_hours')->nullable();           // optional: hours within a day
            $table->enum('status', ['pending', 'in_progress', 'paused', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['standard', 'urgent'])->default('standard');
            $table->unsignedBigInteger('assigned_machine_id')->nullable();
            $table->date('scheduled_start_date');
            $table->date('scheduled_end_date')->nullable();          // auto-calculated
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->date('due_date')->nullable();                    // deadline
            $table->string('assigned_to')->nullable();               // operator name
            $table->integer('sort_order')->default(0);               // position in day queue
            $table->unsignedBigInteger('preempted_by')->nullable();  // if paused by urgent task
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['scheduled_start_date', 'status']);
            $table->index(['assigned_machine_id', 'scheduled_start_date']);
            $table->index(['status', 'priority']);
        });

        // ── Machine Downtime Events ──────────────────────────────
        Schema::create('machine_downtimes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->datetime('start_time');
            $table->integer('duration_hours');                       // hours of downtime
            $table->string('reason')->nullable();                    // maintenance, breakdown, etc.
            $table->boolean('resolved')->default(false);
            $table->timestamps();

            $table->index(['machine_id', 'start_time']);
        });

        // ── Schedule Shift Log (audit trail) ─────────────────────
        Schema::create('schedule_shift_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->string('trigger');                               // urgent_preemption, machine_downtime, manual
            $table->date('original_start');
            $table->date('new_start');
            $table->date('original_end')->nullable();
            $table->date('new_end')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_shift_logs');
        Schema::dropIfExists('machine_downtimes');
        Schema::dropIfExists('production_tasks');
    }
};
