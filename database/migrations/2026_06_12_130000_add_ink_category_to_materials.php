<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN — modify inline via raw SQL
        // For MySQL/MariaDB (used in this project)
        DB::statement("ALTER TABLE materials MODIFY COLUMN category ENUM('paper','film','offset','ink') NOT NULL");
        DB::statement("ALTER TABLE stock_reports MODIFY COLUMN category VARCHAR(20) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE materials MODIFY COLUMN category ENUM('paper','film','offset') NOT NULL");
    }
};
