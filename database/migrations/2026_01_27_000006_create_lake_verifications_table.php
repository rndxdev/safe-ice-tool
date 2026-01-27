<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lake_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lake_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('verdict', 16);
            $table->timestamps();
            $table->unique(['lake_id', 'user_id']);
            $table->index(['lake_id', 'verdict']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lake_verifications');
    }
};
