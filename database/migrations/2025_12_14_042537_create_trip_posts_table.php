<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->foreignId('lake_id')->nullable()->constrained('lakes')->nullOnDelete();

            $table->text('caption')->nullable();

            // simple tags (JSON array of strings)
            $table->json('people_tags')->nullable();
            $table->json('location_tags')->nullable();

            // visibility + share
            $table->boolean('is_public')->default(true);
            $table->string('share_token', 64)->nullable()->unique();

            $table->timestamps();

            $table->index(['is_public', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_posts');
    }
};
