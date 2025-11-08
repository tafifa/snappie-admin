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
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('place_id')->constrained('places')->onDelete('cascade');
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->text('image_url')->nullable();
            $table->boolean('status')->default(false);
            $table->json('additional_info')->nullable(); // Stores flexible key-value pairs, e.g., {"device": "mobile", "purpose": "leisure"}
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('user_id'); // For retrieving user's check-ins
            $table->index('place_id'); // For retrieving check-ins at a specific place
            $table->index(['latitude', 'longitude']); // For location-based queries
            $table->index('status'); // For filtering check-ins by status
            $table->index('created_at'); // For chronological ordering and time-based queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
