<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            // Drop the old single-column unique index on chat_id
            $table->dropUnique('telegram_groups_chat_id_unique');

            // Add composite unique: a group can have many topics (same chat_id, different thread_id)
            // NULL thread_id = the group's General (no topic)
            $table->unique(['chat_id', 'message_thread_id'], 'telegram_groups_chat_thread_unique');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            $table->dropUnique('telegram_groups_chat_thread_unique');
            $table->unique('chat_id');
        });
    }
};
