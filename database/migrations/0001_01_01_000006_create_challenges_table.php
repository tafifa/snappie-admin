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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->integer('exp_reward')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->enum('challenge_type', ['daily', 'weekly', 'special'])->nullable(); // e.g., 'daily', 'weekly', 'monthly'
            $table->boolean('status')->default(true); // e.g., active/inactive
            $table->json('additional_info')->nullable(); // Stores flexible key-value pairs
            $table->timestamps();

            // Indexes for better performance
            $table->index('name');
            $table->index('started_at');
            $table->index('ended_at');
            $table->index('challenge_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
