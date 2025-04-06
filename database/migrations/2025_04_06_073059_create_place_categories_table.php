<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaceCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('place_categories', function (Blueprint $table) {
            $table->uuid('place_id');
            $table->uuid('category_id');
            $table->primary(['place_id', 'category_id']);

            $table->foreign('place_id')
                  ->references('place_id')
                  ->on('places')
                  ->onDelete('cascade');

            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('categories')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('place_categories');
    }
}
