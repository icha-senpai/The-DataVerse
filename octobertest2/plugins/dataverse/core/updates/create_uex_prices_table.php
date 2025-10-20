<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUexPricesTable extends Migration
{
    public function up()
    {
        Schema::create('uex_prices', function ($t) {
            $t->increments('id');
            $t->unsignedInteger('commodity_id')->index();
            $t->unsignedInteger('terminal_id')->nullable();
            $t->decimal('price_buy', 10, 2)->nullable();
            $t->decimal('price_sell', 10, 2)->nullable();
            $t->timestamp('fetched_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_prices');
    }
}
