<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMissionsTable extends Migration
{
    public function up()
    {
        Schema::create('user_missions', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('mission_id');
            $table->timestamp('completed_at')->nullable();
            $table->text('image_taken')->nullable();
            $table->primary(['user_id', 'mission_id']);

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users_app')
                  ->onDelete('cascade');

            $table->foreign('mission_id')
                  ->references('mission_id')
                  ->on('missions')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_missions');
    }
}
