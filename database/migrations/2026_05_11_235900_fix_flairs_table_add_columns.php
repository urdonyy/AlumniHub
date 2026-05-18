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

        // Add foreign key and indexes safely
        Schema::table('flairs', function (Blueprint $table) {
            // Add foreign key if communities table and column exist
            if (Schema::hasTable('communities') && Schema::hasColumn('communities', 'id')) {
                try {
                    $table->foreign('community_id')->references('id')->on('communities')->onDelete('cascade');
                } catch (\Throwable $e) {
                    // ignore if foreign key exists or cannot be created
                }
            }

            // unique constraint on community_id + slug if slug exists
            if (Schema::hasColumn('flairs', 'slug')) {
                try {
                    $table->unique(['community_id', 'slug']);
                } catch (\Throwable $e) {
                }

                try {
                    $table->index('slug');
                } catch (\Throwable $e) {
                }
            }
        });
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
