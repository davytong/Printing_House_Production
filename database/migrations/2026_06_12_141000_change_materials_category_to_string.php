<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE materials MODIFY COLUMN category VARCHAR(50) NOT NULL DEFAULT 'paper'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE materials MODIFY COLUMN category ENUM('paper','film','offset','consumable') NOT NULL");
        }
    }
};
