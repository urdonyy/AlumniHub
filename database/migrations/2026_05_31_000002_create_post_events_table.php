<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('event_type'); // online, in_person
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('external_link')->nullable();
            $table->string('address')->nullable();
            $table->string('venue')->nullable();
            $table->boolean('auto_invited')->default(false); // whether audience invites were dispatched
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->unique('post_id');
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_events');
    }
};
