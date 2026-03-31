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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->enum('role', ['alumni', 'student', 'admin'])
                ->nullable()
                ->after('last_name');
            $table->enum('account_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('role');
            $table->unsignedSmallInteger('batch_year')
                ->nullable()
                ->after('account_status');
            $table->string('program_course')
                ->nullable()
                ->after('batch_year');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'role', 'account_status', 'batch_year', 'program_course']);
        });
    }
};