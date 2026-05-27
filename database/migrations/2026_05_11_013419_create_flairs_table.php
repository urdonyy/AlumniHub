<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flairs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->string('color')->nullable(); // hex color e.g., #FF5733
            $table->string('icon')->nullable(); // FontAwesome or emoji
            $table->boolean('is_sticky')->default(false);
            $table->timestamps();

            $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
            $table->unique(['community_id', 'slug']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flairs');
    }
};
