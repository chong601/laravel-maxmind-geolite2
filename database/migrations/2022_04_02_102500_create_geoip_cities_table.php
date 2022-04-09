<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeoipCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geoip_cities', function (Blueprint $table) {
            $table->string('network')->primary();
            $table->integer('geoname_id')->nullable()->default(-1);
            $table->integer('registered_country_geoname_id')->nullable()->default(-1);
            $table->integer('represented_country_geoname_id')->nullable()->default(-1);
            $table->boolean('is_anonymous_proxy')->nullable();
            $table->boolean('is_satellite_provider')->nullable();
            $table->text('postal_code')->nullable();
            $table->float('latitude', 8, 4)->nullable();
            $table->float('longitude', 8, 4)->nullable();
            $table->integer('accuracy_radius')->nullable();
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
        Schema::dropIfExists('geoip_cities');
    }
}
