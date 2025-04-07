<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_challenges', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('challenge_id');
            $table->timestamp('completed_at')->nullable();
            $table->integer('progress')->nullable();
            $table->primary(['user_id', 'challenge_id']);

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users_app')
                  ->onDelete('cascade');

            $table->foreign('challenge_id')
                  ->references('challenge_id')
                  ->on('challenges')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_challenges');
    }
};
