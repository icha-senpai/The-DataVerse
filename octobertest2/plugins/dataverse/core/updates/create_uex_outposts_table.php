<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexOutpostsTable extends Migration
{
    public function up()
    {
        Schema::create('uex_outposts', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('id_star_system')->nullable();
            $t->unsignedInteger('id_planet')->nullable();
            $t->string('name')->index();
            $t->string('type')->nullable();
            $t->boolean('is_mining')->default(false);
            $t->boolean('is_refinery')->default(false);
            $t->boolean('has_trade_terminal')->default(false);
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_outposts');
    }
}
