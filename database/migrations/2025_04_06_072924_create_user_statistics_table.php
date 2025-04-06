<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStatisticsTable extends Migration
{
    public function up()
    {
        Schema::create('user_statistics', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->integer('total_points')->default(0);
            $table->integer('total_coins')->default(0);
            $table->integer('total_challenges')->default(0);
            $table->integer('total_achievements')->default(0);
            $table->integer('total_missions')->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('total_upvotes')->default(0);

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users_app')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_statistics');
    }
}
