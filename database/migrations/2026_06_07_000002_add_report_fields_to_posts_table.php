<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Denormalized count of pending (unresolved) reports — cheap threshold
            // checks and queue display without aggregating post_reports each time.
            $table->unsignedInteger('reports_count')->default(0)->after('trashed_at');
            // Set when the post crosses the report threshold and enters the admin
            // review queue; cleared when an admin keeps the post.
            $table->timestamp('flagged_at')->nullable()->after('reports_count')->index();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['flagged_at']);
            $table->dropColumn(['reports_count', 'flagged_at']);
        });
    }
};
