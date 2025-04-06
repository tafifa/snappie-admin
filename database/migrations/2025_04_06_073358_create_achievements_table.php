<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAchievementsTable extends Migration
{
    public function up()
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->uuid('achievement_id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('level')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('achievements');
    }
}
