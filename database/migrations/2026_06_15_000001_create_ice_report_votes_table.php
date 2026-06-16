<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ice_report_votes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ice_report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // +1 for an upvote, -1 for a downvote.
            $table->smallInteger('value');

            $table->timestamps();

            // One vote per user per report.
            $table->unique(['ice_report_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ice_report_votes');
    }
};
