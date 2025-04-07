<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('review_id')->primary();
            $table->uuid('user_id');
            $table->uuid('place_id');
            $table->float('rating')->nullable();
            $table->text('content')->nullable();
            $table->json('images')->nullable();
            $table->integer('upvotes')->default(0);
            $table->timestamp('date')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users_app')
                  ->onDelete('cascade');

            $table->foreign('place_id')
                  ->references('place_id')
                  ->on('places')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
