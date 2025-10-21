<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uex_commodities_status_buy', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('code')->nullable();
            $t->string('name')->nullable();
            $t->string('name_short')->nullable();
            $t->string('name_abbr')->nullable();
            $t->string('percentage')->nullable();
            $t->integer('percentage_start')->nullable();
            $t->integer('percentage_end')->nullable();
            $t->string('colors')->nullable();
            $t->string('last_hash', 64)->nullable();
            $t->timestamps();
        });

        Schema::create('uex_commodities_status_sell', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('code')->nullable();
            $t->string('name')->nullable();
            $t->string('name_short')->nullable();
            $t->string('name_abbr')->nullable();
            $t->string('percentage')->nullable();
            $t->integer('percentage_start')->nullable();
            $t->integer('percentage_end')->nullable();
            $t->string('colors')->nullable();
            $t->string('last_hash', 64)->nullable();
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_commodities_status_buy');
        Schema::dropIfExists('uex_commodities_status_sell');
    }
};
