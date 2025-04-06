<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUsersAppTable extends Migration
{
    public function up()
    {
        Schema::create('users_app', function (Blueprint $table) {
            $table->uuid('user_id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name');
            $table->string('email')->unique();
            $table->text('profile_picture')->nullable();
            $table->timestamp('date_joined')->useCurrent();
            $table->integer('points')->default(0);
            $table->integer('coin')->default(0);
            // Opsional: jika ingin menyimpan created_at/updated_at
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_app');
    }
}
