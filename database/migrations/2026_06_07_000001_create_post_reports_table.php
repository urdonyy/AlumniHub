<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->text('details')->nullable();
            // Resolution: null = pending in the review queue. Set when an admin
            // keeps or deletes the reported post.
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolution')->nullable(); // 'kept' | 'deleted'
            $table->timestamps();

            // One active report per user per post.
            $table->unique(['post_id', 'user_id']);
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reports');
    }
};
