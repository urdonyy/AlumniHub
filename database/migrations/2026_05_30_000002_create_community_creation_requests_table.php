<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_creation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requestor_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->text('purpose');
            $table->unsignedSmallInteger('batch_year');
            $table->string('program_course');
            $table->string('year_section');
            $table->enum('status', ['pending_co_mods', 'pending_admin', 'approved', 'rejected', 'cancelled'])
                ->default('pending_co_mods');
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->foreignId('community_id')->nullable()->constrained('communities')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['requestor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_creation_requests');
    }
};
