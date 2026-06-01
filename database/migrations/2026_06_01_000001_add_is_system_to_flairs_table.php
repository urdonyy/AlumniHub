<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flairs', function (Blueprint $table) {
            // System flairs are auto-applied (e.g. "Event") and hidden from the
            // composer's flair picker, but remain available in the feed filter.
            $table->boolean('is_system')->default(false)->after('is_sticky');
        });
    }

    public function down(): void
    {
        Schema::table('flairs', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
