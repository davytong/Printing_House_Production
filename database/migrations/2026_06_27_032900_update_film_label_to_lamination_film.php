<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update category_label_film setting from "Laminate (ស្គុត)" to "Lamination Film (ស្គុត)"
        DB::table('settings')
            ->where('key', 'category_label_film')
            ->where('value', 'Laminate (ស្គុត)')
            ->update([
                'value' => 'Lamination Film (ស្គុត)'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to "Laminate (ស្គុត)"
        DB::table('settings')
            ->where('key', 'category_label_film')
            ->where('value', 'Lamination Film (ស្គុត)')
            ->update([
                'value' => 'Laminate (ស្គុត)'
            ]);
    }
};
