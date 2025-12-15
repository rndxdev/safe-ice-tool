<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('lake_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('trip_date')->nullable();
            $table->string('time_of_day')->nullable();  // morning, afternoon, evening

            // Safety preferences
            $table->decimal('min_thickness_inches', 4, 1)->nullable();
            $table->boolean('avoid_slush')->default(false);
            $table->boolean('avoid_pressure_cracks')->default(false);

            $table->string('target_species')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
