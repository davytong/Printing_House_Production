<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Production Processes table (Dynamic configurable processes)
        if (!Schema::hasTable('production_processes')) {
            Schema::create('production_processes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();                 // Design, Press, Folding, Binding, etc.
                $table->string('code')->nullable();               // PR-01
                $table->integer('sequence')->default(0);          // Step order
                $table->integer('default_capacity')->default(5000); // units/day
                $table->integer('estimated_duration_hours')->default(8);
                $table->string('color')->nullable();              // Hex color
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Production Templates table (e.g. Textbook, Workbook, Cover)
        if (!Schema::hasTable('production_templates')) {
            Schema::create('production_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Production Template Processes (Stages in a template)
        if (!Schema::hasTable('production_template_processes')) {
            Schema::create('production_template_processes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('production_templates')->onDelete('cascade');
                $table->string('process_name');                   // Name matching process
                $table->integer('sequence')->default(1);          // Step order
                $table->integer('capacity')->nullable();          // Override capacity
                $table->integer('estimated_days')->default(1);
                $table->timestamps();
            });
        }

        // 4. Enhance Machines table with capacity & process mappings
        Schema::table('machines', function (Blueprint $table) {
            if (!Schema::hasColumn('machines', 'process_name')) {
                $table->string('process_name')->nullable()->after('type'); // Printing, Folding, Binding, etc.
            }
            if (!Schema::hasColumn('machines', 'daily_capacity')) {
                $table->integer('daily_capacity')->default(8000)->after('process_name');
            }
            if (!Schema::hasColumn('machines', 'working_hours')) {
                $table->integer('working_hours')->default(8)->after('daily_capacity');
            }
            if (!Schema::hasColumn('machines', 'working_days')) {
                $table->json('working_days')->nullable()->after('working_hours'); // [1,2,3,4,5,6] (Mon-Sat)
            }
        });

        // 5. Production Jobs table (Parent entity for planned work)
        if (!Schema::hasTable('production_jobs')) {
            Schema::create('production_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('job_number')->unique();            // JOB-2026-0001
                $table->string('name');
                $table->foreignId('book_id')->nullable()->constrained('books')->nullOnDelete();
                $table->foreignId('print_request_id')->nullable()->constrained('print_requests')->nullOnDelete();
                $table->foreignId('template_id')->nullable()->constrained('production_templates')->nullOnDelete();
                $table->integer('quantity')->default(1000);
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->date('start_date');
                $table->date('due_date')->nullable();
                $table->enum('status', ['draft', 'review', 'confirmed', 'in_progress', 'completed', 'delayed', 'cancelled', 'locked'])->default('confirmed');
                $table->text('notes')->nullable();
                $table->string('created_by')->nullable();
                $table->timestamps();

                $table->index(['status', 'start_date']);
                $table->index('job_number');
            });
        }

        // 6. Enhance Production Schedules (Monthly Grid cells)
        Schema::table('production_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('production_schedules', 'production_job_id')) {
                $table->foreignId('production_job_id')->nullable()->after('day')->constrained('production_jobs')->nullOnDelete();
            }
            if (!Schema::hasColumn('production_schedules', 'machine_id')) {
                $table->foreignId('machine_id')->nullable()->after('production_job_id')->constrained('machines')->nullOnDelete();
            }
            if (!Schema::hasColumn('production_schedules', 'planned_qty')) {
                $table->integer('planned_qty')->nullable()->after('task');
            }
            if (!Schema::hasColumn('production_schedules', 'actual_qty')) {
                $table->integer('actual_qty')->default(0)->after('planned_qty');
            }
            if (!Schema::hasColumn('production_schedules', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('status');
            }
            if (!Schema::hasColumn('production_schedules', 'schedule_date')) {
                $table->date('schedule_date')->nullable()->after('day');
                $table->index('schedule_date');
            }
        });

        // 7. Production Actuals (Daily Output log for Planned vs Actual tracking)
        if (!Schema::hasTable('production_actuals')) {
            Schema::create('production_actuals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('production_schedule_id')->constrained('production_schedules')->cascadeOnDelete();
                $table->foreignId('production_job_id')->nullable()->constrained('production_jobs')->nullOnDelete();
                $table->date('production_date');
                $table->integer('planned_qty')->default(0);
                $table->integer('actual_qty')->default(0);
                $table->integer('variance')->default(0); // actual - planned
                $table->text('notes')->nullable();
                $table->string('recorded_by')->nullable();
                $table->timestamps();

                $table->index(['production_schedule_id', 'production_date']);
            });
        }

        // 8. Schedule Audits (Audit trail for moved, rescheduled, locked actions)
        if (!Schema::hasTable('schedule_audits')) {
            Schema::create('schedule_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('production_job_id')->nullable()->constrained('production_jobs')->nullOnDelete();
                $table->foreignId('production_schedule_id')->nullable()->constrained('production_schedules')->nullOnDelete();
                $table->string('user_name')->nullable();
                $table->string('action');                         // created, moved, locked, rescheduled, actual_recorded
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_audits');
        Schema::dropIfExists('production_actuals');

        Schema::table('production_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('production_schedules', 'production_job_id')) {
                $table->dropForeign(['production_job_id']);
                $table->dropColumn('production_job_id');
            }
            if (Schema::hasColumn('production_schedules', 'machine_id')) {
                $table->dropForeign(['machine_id']);
                $table->dropColumn('machine_id');
            }
            if (Schema::hasColumn('production_schedules', 'planned_qty')) {
                $table->dropColumn('planned_qty');
            }
            if (Schema::hasColumn('production_schedules', 'actual_qty')) {
                $table->dropColumn('actual_qty');
            }
            if (Schema::hasColumn('production_schedules', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
            if (Schema::hasColumn('production_schedules', 'schedule_date')) {
                $table->dropColumn('schedule_date');
            }
        });

        Schema::dropIfExists('production_jobs');

        Schema::table('machines', function (Blueprint $table) {
            if (Schema::hasColumn('machines', 'process_name')) $table->dropColumn('process_name');
            if (Schema::hasColumn('machines', 'daily_capacity')) $table->dropColumn('daily_capacity');
            if (Schema::hasColumn('machines', 'working_hours')) $table->dropColumn('working_hours');
            if (Schema::hasColumn('machines', 'working_days')) $table->dropColumn('working_days');
        });

        Schema::dropIfExists('production_template_processes');
        Schema::dropIfExists('production_templates');
        Schema::dropIfExists('production_processes');
    }
};
