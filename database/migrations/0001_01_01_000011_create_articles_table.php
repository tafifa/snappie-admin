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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // TODO: didnt use user table, just use author name
            // TODO: add column link to article source
            $table->string('title');
            $table->string('category');
            $table->text('content'); // TODO: change to short_description
            $table->json('image_urls')->nullable(); // TODO: just one image url
            $table->timestamps();

            // Indexes for better performance
            $table->index('user_id');
            $table->index('title');
            $table->index('category');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
