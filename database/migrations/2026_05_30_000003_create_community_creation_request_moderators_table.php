<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_creation_request_moderators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                ->constrained('community_creation_requests')
                ->cascadeOnDelete();
            $table->foreignId('invited_user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['request_id', 'invited_user_id'], 'request_invitee_unique');
            $table->index(['invited_user_id', 'status'], 'ccrm_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_creation_request_moderators');
    }
};
