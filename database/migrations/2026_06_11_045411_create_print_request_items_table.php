<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('print_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->string('book_title');          // snapshot of title (even if book deleted)
            $table->string('grade')->nullable();
            $table->string('category')->nullable(); // perfect_binding | staple
            $table->integer('quantity_requested');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Also add total_books_requested to print_requests for quick summary
        Schema::table('print_requests', function (Blueprint $table) {
            $table->integer('total_books_requested')->default(1)->after('quantity_requested');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_request_items');
        Schema::table('print_requests', function (Blueprint $table) {
            $table->dropColumn('total_books_requested');
        });
    }
};
