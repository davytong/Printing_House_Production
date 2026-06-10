<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique();
            $table->string('name')->nullable();
            $table->string('type')->nullable(); // group / supergroup
            $table->timestamps();
        });

        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
        Schema::dropIfExists('telegram_groups');
    }
};
