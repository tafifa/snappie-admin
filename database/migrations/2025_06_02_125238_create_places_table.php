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
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('name');
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->jsonb('image_urls')->nullable();
            $table->boolean('status')->default(true)->nullable();
            $table->boolean('partnership_status')->default(false)->nullable();
            $table->text('clue_mission')->nullable();
            $table->integer('exp_reward')->nullable();
            $table->integer('coin_reward')->nullable();
            
            // Stores flexible key-value pairs, e.g., {"website": "example.com", "capacity": 100, "opening_hours": "9 AM - 5 PM", "contact_number": "123-456-7890"}
            $table->jsonb('additional_info')->nullable(); 
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('status'); // For filtering active/inactive places
            $table->index('partnership_status'); // For filtering partner places
            $table->index('category'); // For category-based searches
            $table->index(['latitude', 'longitude']); // For location-based queries
            $table->index('exp_reward'); // For sorting by rewards
            $table->index('coin_reward'); // For sorting by rewards
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
