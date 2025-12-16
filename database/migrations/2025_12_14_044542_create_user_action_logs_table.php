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
        Schema::create('user_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action_type', 50); // 'checkin', 'review', 'rate_5_star', 'upload_photo', 'post'
            $table->json('action_data')->nullable(); // Stores additional context like place_id, rating value, etc.
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['user_id', 'action_type', 'created_at'], 'idx_user_actions');
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_action_logs');
    }
};
