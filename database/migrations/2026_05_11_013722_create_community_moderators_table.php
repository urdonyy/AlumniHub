<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_moderators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default('moderator'); // moderator, senior_moderator, etc.
            $table->timestamp('promoted_at')->nullable();
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['community_id', 'user_id']);
            $table->index(['community_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_moderators');
    }
};
