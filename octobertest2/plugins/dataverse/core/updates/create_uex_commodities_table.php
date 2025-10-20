<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use October\Rain\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        Schema::create('uex_commodities', function (Blueprint $t) {
            $t->increments('id');
            $t->string('code')->nullable()->index();
            $t->unsignedBigInteger('date_added')->nullable();
            $t->unsignedBigInteger('date_modified')->nullable();
            $t->unsignedInteger('id_parent')->nullable()->index();

            $t->boolean('is_available')->default(false);
            $t->boolean('is_available_live')->default(false);
            $t->boolean('is_buggy')->default(false);
            $t->boolean('is_buyable')->default(false);
            $t->boolean('is_explosive')->default(false);
            $t->boolean('is_extractable')->default(false);
            $t->boolean('is_fuel')->default(false);
            $t->boolean('is_harvestable')->default(false);
            $t->boolean('is_illegal')->default(false);
            $t->boolean('is_raw')->default(false);
            $t->boolean('is_refinable')->default(false);
            $t->boolean('is_refined')->default(false);
            $t->boolean('is_sellable')->default(false);
            $t->boolean('is_temporary')->default(false);
            $t->boolean('is_visible')->default(false);
            $t->boolean('is_volatile_qt')->default(false);
            $t->boolean('is_volatile_time')->default(false);

            $t->string('kind')->nullable();
            $t->string('name')->nullable()->index();
            $t->float('price_buy')->nullable();
            $t->float('price_sell')->nullable();
            $t->float('weight_scu')->nullable();
            $t->string('wiki')->nullable();

            $t->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('uex_commodities'); }
};
