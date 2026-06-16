<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ice reports are read by lake + visibility, ordered by recency
        // (dashboard feed, lake page, LakeSafetyService 10-day window).
        Schema::table('ice_reports', function (Blueprint $table) {
            $table->index(['lake_id', 'is_hidden', 'created_at'], 'ice_reports_lake_hidden_created_index');
        });

        // The public lake listing filters on is_active + status.
        Schema::table('lakes', function (Blueprint $table) {
            $table->index(['is_active', 'status'], 'lakes_active_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('ice_reports', function (Blueprint $table) {
            $table->dropIndex('ice_reports_lake_hidden_created_index');
        });

        Schema::table('lakes', function (Blueprint $table) {
            $table->dropIndex('lakes_active_status_index');
        });
    }
};
