<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexTerminalsTable extends Migration
{
    public function up()
    {
        Schema::create('uex_terminals', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('id_city')->nullable();
            $t->unsignedInteger('id_outpost')->nullable();
            $t->unsignedInteger('id_space_station')->nullable();
            $t->string('name')->index();
            $t->string('type')->nullable();
            $t->boolean('is_trade')->default(false);
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_terminals');
    }
}
