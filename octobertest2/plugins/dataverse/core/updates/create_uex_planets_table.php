<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexPlanetsTable extends Migration
{
    public function up()
    {
        Schema::create('uex_planets', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('id_star_system')->nullable();
            $t->string('name')->index();
            $t->string('code')->nullable();
            $t->string('type')->nullable();
            $t->boolean('is_landable')->default(false);
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_planets');
    }
}
