<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexCitiesTable extends Migration
{
    public function up()
    {
        Schema::create('uex_cities', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('id_star_system')->nullable();
            $t->unsignedInteger('id_planet')->nullable();
            $t->string('name')->index();
            $t->string('code')->nullable();
            $t->boolean('is_landable')->default(false);
            $t->boolean('has_trade_terminal')->default(false);
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_cities');
    }
}
