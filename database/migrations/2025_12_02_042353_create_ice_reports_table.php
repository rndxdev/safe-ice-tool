<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ice_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lake_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->decimal('thickness_inches', 4, 1);
            $table->string('ice_type')->nullable();
            $table->string('traffic_type')->nullable();
            $table->boolean('has_slush')->default(false);
            $table->boolean('has_pressure_cracks')->default(false);
            $table->text('notes')->nullable();

            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);

            $table->boolean('is_flagged')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ice_reports');
    }
};
