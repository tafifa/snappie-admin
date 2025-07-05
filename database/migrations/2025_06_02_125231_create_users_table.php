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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('image_url')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->integer('total_coin')->default(0);
            $table->integer('total_exp')->default(0);
            
            $table->jsonb('additional_info')->nullable(); // Stores flexible key-value pairs, e.g., {"bio": "User bio", "preferences": {"theme": "dark"}}
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('status'); // For filtering active/inactive users
            $table->index('total_exp'); // For leaderboards and ranking
            $table->index('total_coin'); // For leaderboards and ranking
            $table->index('last_login_at'); // For user activity tracking
            $table->index('created_at'); // For user registration analytics
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
