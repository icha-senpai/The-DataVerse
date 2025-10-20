<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexStarSystemsTable extends Migration
{
    public function up()
    {
        Schema::create('uex_star_systems', function ($t) {
            $t->increments('id');
            $t->string('name')->index();
            $t->string('code')->nullable();
            $t->string('description', 1024)->nullable();
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_star_systems');
    }
}
