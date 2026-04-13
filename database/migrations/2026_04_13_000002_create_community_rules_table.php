<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('batch_year')->nullable();
            $table->string('program_course')->nullable();
            $table->timestamps();

            $table->unique(['community_id', 'batch_year', 'program_course'], 'community_rules_unique_match');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_rules');
    }
};
