<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeoipAsnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geoip_asns', function (Blueprint $table) {
            $table->string('network')->primary();
            $table->bigInteger('autonomous_system_number')->nullable();
            $table->text('autonomous_system_organization')->nullable();
            $table->decimal('ip_min_range', 40, 0, true)->index()->nullable();
            $table->decimal('ip_max_range', 40, 0, true)->index()->nullable();
            $table->timestamp('created_at', 0)->nullable()->default(Carbon::now());
            $table->timestamp('updated_at', 0)->nullable()->default(Carbon::now());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('geoip_asns');
    }
}
