<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->uuid('mission_id')->primary();
            $table->uuid('place_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points_reward')->nullable();
            $table->integer('coin_reward')->nullable();
            $table->timestamps();

            $table->foreign('place_id')
                  ->references('place_id')
                  ->on('places')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
