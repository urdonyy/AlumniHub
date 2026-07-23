<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relax users.role from an enum('alumni','student','admin') to a plain
     * string so it can hold 'superadmin' (the PUP-ITECH Official institution
     * account). Using ->change() keeps this portable: it drops the MySQL enum
     * and the SQLite CHECK constraint the original enum produced (tests use
     * SQLite). Application-level role checks remain the source of truth.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Leaving role as a string is harmless; recreating the enum would fail
        // for any existing 'superadmin' rows, so this is intentionally a no-op.
    }
};
