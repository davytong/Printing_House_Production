<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // grade: e.g. "1","2",...,"12", "មត្តេយ្យ", "មូលដ្ឋាន", "មធ្យម", etc.
            $table->string('grade')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('grade');
        });
    }
};
