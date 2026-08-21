<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            // Add 'suspended' and 'pending' to the status enum
            DB::statement("ALTER TABLE production_batches MODIFY COLUMN status ENUM('active', 'completed', 'suspended', 'pending') DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            // Revert to original enum values
            DB::statement("ALTER TABLE production_batches MODIFY COLUMN status ENUM('active', 'completed') DEFAULT 'active'");
        }
    }
};
