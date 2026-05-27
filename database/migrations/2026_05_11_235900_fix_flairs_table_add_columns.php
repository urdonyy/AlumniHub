<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('flairs')) {
            return;
        }

        Schema::table('flairs', function (Blueprint $table) {
            if (! Schema::hasColumn('flairs', 'community_id')) {
                $table->unsignedBigInteger('community_id')->nullable()->after('id')->index();
            }

            if (! Schema::hasColumn('flairs', 'name')) {
                $table->string('name')->after('community_id');
            }

            if (! Schema::hasColumn('flairs', 'slug')) {
                $table->string('slug')->after('name')->nullable();
            }

            if (! Schema::hasColumn('flairs', 'color')) {
                $table->string('color')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('flairs', 'icon')) {
                $table->string('icon')->nullable()->after('color');
            }

            if (! Schema::hasColumn('flairs', 'is_sticky')) {
                $table->boolean('is_sticky')->default(false)->after('icon');
            }
        });

        // Note: indexes and foreign key for (community_id, slug) are created in
        // the original create_flairs_table migration. Avoid re-creating them here
        // to prevent duplicate-key errors when running migrations after a
        // refresh. This migration only adds missing columns above.
    }

    public function down(): void
    {
        if (! Schema::hasTable('flairs')) {
            return;
        }

        Schema::table('flairs', function (Blueprint $table) {
            if (Schema::hasColumn('flairs', 'is_sticky')) {
                $table->dropColumn('is_sticky');
            }
            if (Schema::hasColumn('flairs', 'icon')) {
                $table->dropColumn('icon');
            }
            if (Schema::hasColumn('flairs', 'color')) {
                $table->dropColumn('color');
            }
            if (Schema::hasColumn('flairs', 'slug')) {
                try {
                    $table->dropIndex(['slug']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('flairs', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('flairs', 'community_id')) {
                try {
                    $table->dropForeign(['community_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('community_id');
            }
        });
    }
};
