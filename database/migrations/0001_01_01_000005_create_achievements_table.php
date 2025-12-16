<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->enum('type', ['achievement', 'challenge'])->default('achievement');
            $table->text('description')->nullable();
            
            // Simplified criteria structure
            $table->string('criteria_action', 50); // e.g., 'checkin', 'review', 'rating_5_star'
            $table->integer('criteria_target')->default(1); // e.g., 5, 10, 100
            
            $table->string('image_url', 500)->nullable();
            $table->integer('coin_reward')->default(0);
            $table->integer('reward_xp')->default(0);
            $table->boolean('status')->default(true);
            
            // Reset schedule for repeatable achievements/challenges
            $table->enum('reset_schedule', ['none', 'daily', 'weekly'])->default('none');
            
            $table->integer('display_order')->default(0);
            $table->json('additional_info')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('code');
            $table->index('type');
            $table->index('status');
            $table->index('criteria_action');
            $table->index('reset_schedule');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
