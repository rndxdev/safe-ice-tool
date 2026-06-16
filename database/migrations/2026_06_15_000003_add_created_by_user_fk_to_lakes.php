<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot add a foreign key to an existing table via ALTER, and
        // the test suite runs on SQLite. The production database is PostgreSQL,
        // where the constraint matters, so apply it everywhere except SQLite.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('lakes', function (Blueprint $table) {
            $table->foreign('created_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('lakes', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
        });
    }
};
