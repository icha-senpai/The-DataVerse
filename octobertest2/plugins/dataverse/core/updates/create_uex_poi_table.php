<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexPoiTable extends Migration
{
    public function up()
    {
        Schema::create('uex_poi', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('id_planet')->nullable();
            $t->string('name')->index();
            $t->string('type')->nullable();
            $t->string('description', 1024)->nullable();
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_poi');
    }
}
