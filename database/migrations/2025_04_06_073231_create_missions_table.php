<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMissionsTable extends Migration
{
    public function up()
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->uuid('mission_id')->primary()->default(DB::raw('uuid_generate_v4()'));
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

    public function down()
    {
        Schema::dropIfExists('missions');
    }
}
