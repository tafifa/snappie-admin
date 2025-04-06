<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePlacesTable extends Migration
{
    public function up()
    {
        Schema::create('places', function (Blueprint $table) {
            $table->uuid('place_id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name');
            // Kolom location: gunakan tipe spatial jika tersedia, atau simpan sebagai point dengan raw statement
            $table->point('location')->nullable();
            $table->float('rating')->default(0);
            $table->text('description')->nullable();
            // Gunakan JSON untuk menyimpan array URL gambar dan tag
            $table->json('images')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('places');
    }
}
