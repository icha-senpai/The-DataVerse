<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexSpaceStationsTable extends Migration
{
    public function up()
    {
        Schema::create('uex_space_stations', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('id_star_system')->nullable();
            $t->unsignedInteger('id_planet')->nullable();
            $t->string('name')->index();
            $t->string('nickname')->nullable();
            $t->boolean('has_trade_terminal')->default(false);
            $t->boolean('has_refinery')->default(false);
            $t->boolean('is_landable')->default(false);
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_space_stations');
    }
}
