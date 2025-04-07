<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_plans', function (Blueprint $table) {
            $table->uuid('plan_id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->boolean('is_done')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users_app')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_plans');
    }
};
