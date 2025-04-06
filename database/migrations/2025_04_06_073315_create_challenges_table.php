<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateChallengesTable extends Migration
{
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->uuid('challenge_id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('points_reward')->nullable();
            $table->integer('coin_reward')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('challenges');
    }
}
