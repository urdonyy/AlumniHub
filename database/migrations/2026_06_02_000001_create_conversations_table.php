<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            // Normalized pair (min/max of the two user ids) guarantees a single
            // conversation row per pair, mirroring the connections table convention.
            $table->unsignedBigInteger('user_low_id');
            $table->unsignedBigInteger('user_high_id');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('user_low_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_high_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_low_id', 'user_high_id']);
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
