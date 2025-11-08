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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->json('image_urls')->nullable();
            $table->integer('coin_reward')->default(0);
            $table->integer('exp_reward')->default(0);
            $table->integer('min_price')->nullable()->default(0); // Minimum price for the place
            $table->integer('max_price')->nullable()->default(0); // Maximum price for the place
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('total_review')->default(0);
            $table->integer('total_checkin')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('partnership_status')->default(false);
            $table->json('additional_info')->nullable(); // Stores flexible key-value pairs, e.g., {"website": "example.com", "capacity": 100, "opening_hours": "9 AM - 5 PM", "contact_number": "123-456-7890"}
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('name'); // For name-based searches
            $table->index(['latitude', 'longitude']); // For location-based queries
            $table->index(['min_price', 'max_price']); // For filtering by minimum price
            $table->index('avg_rating'); // For rating-based queries
            $table->index('total_review'); // For total reviews-based queries
            $table->index('total_checkin'); // For total checkins-based queries
            $table->index('status'); // For filtering active/inactive places
            $table->index('partnership_status'); // For filtering partner places
            $table->index('created_at'); // For chronological ordering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
