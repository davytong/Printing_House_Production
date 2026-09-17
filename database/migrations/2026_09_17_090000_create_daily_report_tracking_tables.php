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
        if (!Schema::hasTable('report_requirements')) {
            Schema::create('report_requirements', function (Blueprint $table) {
                $table->id();
                $table->string('staff_name');
                $table->string('telegram_user_id');
                $table->string('telegram_username')->nullable();
                $table->string('report_type', 50)->default('morning_1');
                $table->string('report_title');
                $table->string('identifier_tag');
                $table->string('deadline_time', 10);
                $table->json('required_days')->nullable();
                $table->string('alert_chat_id')->nullable();
                $table->unsignedBigInteger('alert_thread_id')->nullable();
                $table->boolean('active')->default(true);
                $table->boolean('send_ack')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['telegram_user_id', 'active'], 'idx_req_user_active');
                $table->index(['deadline_time', 'active'], 'idx_req_time_active');
                $table->index('report_type', 'idx_req_type');
            });
        }

        Schema::dropIfExists('daily_report_submissions');

        Schema::create('daily_report_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_requirement_id')->constrained('report_requirements')->onDelete('cascade');
            $table->date('report_date');
            $table->string('telegram_user_id');
            $table->string('telegram_chat_id')->nullable();
            $table->unsignedBigInteger('telegram_message_id')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->enum('status', ['pending', 'submitted', 'late', 'missed'])->default('pending');
            $table->integer('late_minutes')->default(0);
            $table->dateTime('alert_sent_at')->nullable();
            $table->text('message_text')->nullable();
            $table->integer('revision_count')->default(0);
            $table->timestamps();

            $table->unique(['report_requirement_id', 'report_date'], 'uniq_drs_req_date');
            $table->index(['report_date', 'status'], 'idx_drs_date_status');
            $table->index(['telegram_user_id', 'report_date'], 'idx_drs_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_submissions');
        Schema::dropIfExists('report_requirements');
    }
};