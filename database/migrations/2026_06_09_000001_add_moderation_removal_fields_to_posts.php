<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Set when a MODERATOR removes someone else's post (vs the author
            // trashing their own). Drives: (a) the author may not restore it,
            // (b) the removal reason/note shown to the author.
            $table->unsignedBigInteger('removed_by_user_id')->nullable()->after('trashed_at');
            $table->string('removal_reason')->nullable()->after('removed_by_user_id');
            $table->string('removal_note', 500)->nullable()->after('removal_reason');

            $table->foreign('removed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['removed_by_user_id']);
            $table->dropColumn(['removed_by_user_id', 'removal_reason', 'removal_note']);
        });
    }
};
