<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexCommoditiesTable extends Migration
{
    public function up()
    {
        Schema::create('uex_commodities', function ($t) {
            $t->increments('id');
            $t->string('name')->index();
            $t->string('code')->nullable();
            $t->string('kind')->nullable();
            $t->boolean('is_buyable')->default(false);
            $t->boolean('is_sellable')->default(false);
            $t->boolean('is_illegal')->default(false);
            $t->boolean('is_refinable')->default(false);
            $t->boolean('is_raw')->default(false);
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_commodities');
    }
}
