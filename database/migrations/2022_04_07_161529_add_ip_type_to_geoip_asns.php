<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpTypeToGeoipAsns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('geoip_asns', function (Blueprint $table) {
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
        Schema::table('geoip_asns', function (Blueprint $table) {
            $table->dropColumn('ipType');
        });
    }
}
