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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('place_id')->constrained('places')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->integer('rating')->default(5); // Star rating 1-5
            $table->jsonb('image_urls')->nullable();
            $table->integer('vote')->default(0); // Like count
            $table->enum('status', ['approved', 'rejected', 'pending'])->default('pending');
            $table->timestamps();
            
            $table->index(['user_id', 'place_id']);
            $table->index('status');
            $table->index('rating');
            $table->index('vote');
            $table->index('created_at');
            
            // Add constraint for rating range
            $table->check('rating >= 1 AND rating <= 5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
