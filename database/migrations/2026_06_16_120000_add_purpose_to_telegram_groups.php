<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            // Maps this group/topic to a specific purpose so reports auto-send correctly
            // Values: paper_stock, press_report, finishing_report, consumable_stock, general, procurement
            $table->string('purpose')->nullable()->after('topic_name');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_groups', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });
    }
};
