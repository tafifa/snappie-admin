<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTravelPlanPlacesTable extends Migration
{
    public function up()
    {
        Schema::create('travel_plan_places', function (Blueprint $table) {
            $table->uuid('travel_plan_id');
            $table->uuid('place_id');
            $table->integer('sequence');
            $table->primary(['travel_plan_id', 'place_id']);

            $table->foreign('travel_plan_id')
                  ->references('plan_id')
                  ->on('travel_plans')
                  ->onDelete('cascade');

            $table->foreign('place_id')
                  ->references('place_id')
                  ->on('places')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('travel_plan_places');
    }
}
