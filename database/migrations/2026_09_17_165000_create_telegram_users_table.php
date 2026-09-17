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
        if (!Schema::hasTable('telegram_users')) {
            Schema::create('telegram_users', function (Blueprint $table) {
                $table->id();
                $table->string('telegram_user_id', 64)->unique();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('username')->nullable();
                $table->string('display_name');
                $table->string('source', 50)->default('telegram_group'); // e.g. group_admin, message, manual
                $table->string('last_chat_title')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();

                $table->index('telegram_user_id');
                $table->index('username');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_users');
    }
};