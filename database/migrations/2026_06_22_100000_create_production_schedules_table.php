<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->string('process');  // Design, Press, Folding, Gathering, Staple, Binding, Cutting, Packaging, Delivery
            $table->integer('day');     // 1-31
            $table->string('task')->nullable(); // e.g. "Listening Textbook", "All Cover"
            $table->string('note')->nullable(); // e.g. holiday name
            $table->string('color')->nullable(); // custom color for this cell
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->unique(['year', 'month', 'process', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_schedules');
    }
};
