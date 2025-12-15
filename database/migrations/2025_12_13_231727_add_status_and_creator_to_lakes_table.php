<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::table('lakes', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('county');
            $table->unsignedBigInteger('created_by_user_id')->nullable()->after('status');

            $table->index(['status']);
            $table->index(['created_by_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('lakes', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by_user_id']);
            $table->dropColumn(['status', 'created_by_user_id']);
        });
    }
};
