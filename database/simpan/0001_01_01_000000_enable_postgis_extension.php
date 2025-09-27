<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable PostGIS extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis_topology');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop PostGIS extensions with CASCADE to handle dependencies
        DB::statement('DROP EXTENSION IF EXISTS postgis_tiger_geocoder CASCADE');
        DB::statement('DROP EXTENSION IF EXISTS postgis_topology CASCADE');
        DB::statement('DROP EXTENSION IF EXISTS postgis CASCADE');
    }
};
