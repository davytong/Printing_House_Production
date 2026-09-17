<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('activity_logs', 'module')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->string('module', 50)->default('general')->after('action');
                $table->index(['module', 'created_at']);
                $table->index(['user_name', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activity_logs', 'module')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropIndex(['module', 'created_at']);
                $table->dropIndex(['user_name', 'created_at']);
                $table->dropColumn('module');
            });
        }
    }
};
