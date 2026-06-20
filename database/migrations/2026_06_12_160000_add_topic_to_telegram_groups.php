<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            // For Telegram forum/topic groups — the topic's message_thread_id
            // null = send to General / no topic
            $table->unsignedBigInteger('message_thread_id')->nullable()->after('type');
            $table->string('topic_name')->nullable()->after('message_thread_id');
            $table->boolean('is_forum')->default(false)->after('topic_name');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            $table->dropColumn(['message_thread_id', 'topic_name', 'is_forum']);
        });
    }
};
