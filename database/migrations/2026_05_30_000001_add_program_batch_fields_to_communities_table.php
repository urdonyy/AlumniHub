<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->enum('type', ['program_batch', 'topic'])
                ->default('topic')
                ->after('system_key');
            $table->unsignedSmallInteger('batch_year')->nullable()->after('type');
            $table->string('program_course')->nullable()->after('batch_year');
            $table->string('year_section')->nullable()->after('program_course');
            $table->text('purpose')->nullable()->after('year_section');

            $table->index(['type', 'batch_year', 'program_course'], 'communities_program_batch_index');
        });
    }

    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropIndex('communities_program_batch_index');
            $table->dropColumn(['type', 'batch_year', 'program_course', 'year_section', 'purpose']);
        });
    }
};
