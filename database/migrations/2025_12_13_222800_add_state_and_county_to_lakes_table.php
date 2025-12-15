<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lakes', function (Blueprint $table) {
            $table->string('state', 100)->nullable()->after('region');
            $table->string('county', 150)->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('lakes', function (Blueprint $table) {
            $table->dropColumn(['state', 'county']);
        });
    }
};
