<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('post_type')->default('text')->index()->after('status'); // text, media, event
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->text('body_markdown')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->text('body_markdown')->nullable(false)->change();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['post_type']);
            $table->dropColumn('post_type');
        });
    }
};
