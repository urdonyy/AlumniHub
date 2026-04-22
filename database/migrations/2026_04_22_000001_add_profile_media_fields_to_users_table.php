<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('avatar_path')->nullable()->after('program_course');
            $table->string('banner_path')->nullable()->after('avatar_path');
            $table->timestamp('avatar_uploaded_at')->nullable()->after('banner_path');
            $table->timestamp('banner_uploaded_at')->nullable()->after('avatar_uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'avatar_path',
                'banner_path',
                'avatar_uploaded_at',
                'banner_uploaded_at',
            ]);
        });
    }
};
