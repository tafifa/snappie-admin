<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->uuid('place_id')->primary();
            $table->string('name');
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->float('rating')->default(0);
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
