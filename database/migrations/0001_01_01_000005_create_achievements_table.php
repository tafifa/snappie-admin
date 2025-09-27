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
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->integer('coin_reward')->default(0);
            $table->boolean('status')->default(true); // e.g., active/inactive
            $table->jsonb('additional_info')->nullable(); // Stores flexible key-value pairs
            $table->timestamps();

            // Indexes for better performance
            $table->index('name');
            $table->index('status');
            
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
