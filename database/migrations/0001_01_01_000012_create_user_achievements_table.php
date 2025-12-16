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
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained('achievements')->onDelete('cascade');
            
            // Progress tracking
            $table->integer('current_progress')->default(0);
            $table->integer('target_progress')->default(1);
            
            // Status and completion
            $table->boolean('status')->default(false);
            $table->timestamp('completed_at')->nullable();
            
            // For resetable challenges (daily/weekly)
            $table->date('period_date')->nullable();
            
            $table->json('additional_info')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('user_id');
            $table->index('achievement_id');
            $table->index('status');
            $table->index('period_date');
            $table->index('created_at');
            
            // Unique constraint: one record per user per achievement per period
            $table->unique(['user_id', 'achievement_id', 'period_date'], 'user_achievement_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
