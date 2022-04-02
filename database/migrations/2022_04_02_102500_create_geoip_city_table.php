<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeoipCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geoip_city', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('network')->index();
            $table->integer('geoname_id');
            $table->integer('represented_country_geoname_id');
            $table->boolean('is_anonymous_proxy');
            $table->boolean('is_satellite_provider');
            $table->text('postal_code');
            $table->float('latitude', 8, 4);
            $table->float('longitude', 8, 4);
            $table->integer('accuracy_radius');
            $table->decimal('ip_min_range', 40, 0, true)->index();
            $table->decimal('ip_max_range', 40, 0, true)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('geoip_city');
    }
}
