<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flair_post', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flair_id');
            $table->unsignedBigInteger('post_id');
            $table->timestamps();

            $table->foreign('flair_id')->references('id')->on('flairs')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->unique(['flair_id', 'post_id']);
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flair_post');
    }
};
