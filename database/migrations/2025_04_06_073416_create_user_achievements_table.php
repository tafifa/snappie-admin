<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('achievement_id');
            $table->integer('current_level')->nullable();
            $table->integer('progress')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->primary(['user_id', 'achievement_id']);

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users_app')
                  ->onDelete('cascade');

            $table->foreign('achievement_id')
                  ->references('achievement_id')
                  ->on('achievements')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
