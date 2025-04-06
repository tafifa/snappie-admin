<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateLeaderboardsTable extends Migration
{
    public function up()
    {
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->uuid('leaderboard_id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaderboards');
    }
}
