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
            $table->text('content');
            $table->json('image_urls')->nullable();
            $table->integer('rating')->default(0); // Rating from 1 to 5
            $table->integer('total_like')->default(0); // Number of likes
            $table->boolean('status')->default(true); // For moderation
            $table->json('additional_info')->nullable(); // Stores flexible key-value pairs
            $table->timestamps();

            // Indexes for better performance

            $table->index('user_id');
            $table->index('place_id');
            $table->index('rating');
            $table->index('total_like');
            $table->index('status');
            $table->index('created_at');
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
