<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpTypeToGeoipCities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('geoip_cities', function (Blueprint $table) {
            // Only works on MySQL
            $table->integer('ipType')->after('network');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('geoip_cities', function (Blueprint $table) {
            $table->dropColumn('ipType');
        });
    }
}
