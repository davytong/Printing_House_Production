<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update materials where name_km contains "ហ្វីម" to "ស្គុត"
        DB::table('materials')
            ->where('category', 'film')
            ->where('name_km', 'LIKE', '%ហ្វីម%')
            ->update([
                'name_km' => DB::raw("REPLACE(name_km, 'ហ្វីម', 'ស្គុត')")
            ]);
        
        // Update category label setting if it exists
        DB::table('settings')
            ->where('key', 'category_label_film')
            ->where('value', 'LIKE', '%Film%')
            ->update([
                'value' => DB::raw("REPLACE(REPLACE(value, 'Film (ហ្វីម)', 'Laminate (ស្គុត)'), 'Film', 'Laminate')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to Film terminology
        DB::table('materials')
            ->where('category', 'film')
            ->where('name_km', 'LIKE', '%ស្គុត%')
            ->update([
                'name_km' => DB::raw("REPLACE(name_km, 'ស្គុត', 'ហ្វីម')")
            ]);
        
        DB::table('settings')
            ->where('key', 'category_label_film')
            ->where('value', 'LIKE', '%Laminate%')
            ->update([
                'value' => DB::raw("REPLACE(REPLACE(value, 'Laminate (ស្គុត)', 'Film (ហ្វីម)'), 'Laminate', 'Film')")
            ]);
    }
};
