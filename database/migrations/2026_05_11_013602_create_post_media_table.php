<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            $table->string('file_path'); // stored path in storage/app/public/posts
            $table->string('file_type'); // image/jpeg, image/png, etc.
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->json('meta')->nullable(); // width, height, thumbnail_path
            $table->unsignedInteger('order')->default(0); // display order
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['post_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
