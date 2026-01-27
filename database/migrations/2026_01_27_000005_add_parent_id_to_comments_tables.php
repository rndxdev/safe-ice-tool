<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_post_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('user_id');
            $table->index(['trip_post_id', 'parent_id']);
        });

        Schema::table('feed_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('user_id');
            $table->index(['item_type', 'item_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('trip_post_comments', function (Blueprint $table) {
            $table->dropIndex(['trip_post_id', 'parent_id']);
            $table->dropColumn('parent_id');
        });

        Schema::table('feed_comments', function (Blueprint $table) {
            $table->dropIndex(['item_type', 'item_id', 'parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
