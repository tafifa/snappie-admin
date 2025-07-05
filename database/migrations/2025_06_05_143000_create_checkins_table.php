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
            $table->timestamp('time')->useCurrent();
            $table->jsonb('location')->nullable();
            $table->enum('check_in_status', ['done', 'notdone', 'pending'])->default('pending');
            $table->text('mission_image_url')->nullable();
            $table->enum('mission_status', ['completed', 'pending', 'failed'])->default('pending');
            $table->timestamp('mission_completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'place_id']);
            $table->index('check_in_status');
            $table->index('mission_status');
            $table->index('time');
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
