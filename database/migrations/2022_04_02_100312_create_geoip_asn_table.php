<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeoipAsnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geoip_asn', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('network')->index();
            $table->bigInteger('autonomous_system_number');
            $table->text('autonomous_system_organization');
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
        Schema::dropIfExists('geoip_asn');
    }
}
