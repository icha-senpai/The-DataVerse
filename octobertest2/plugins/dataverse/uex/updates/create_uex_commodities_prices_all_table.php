<?php namespace Dataverse\Uex\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uex_commodities_prices_all', function (Blueprint $t) {
            $t->id(); // Only if you want your own autoincrement ID
            $t->string('uex_id', 255)->nullable(); // avoid collision
            $t->string('id_commodity', 255)->nullable();
            $t->decimal('id_terminal', 12, 4)->nullable();
            $t->decimal('price_buy', 12, 4)->nullable();
            $t->decimal('price_buy_avg', 12, 4)->nullable();
            $t->decimal('price_sell', 12, 4)->nullable();
            $t->decimal('price_sell_avg', 12, 4)->nullable();
            $t->string('scu_buy', 255)->nullable();
            $t->decimal('scu_buy_avg', 12, 4)->nullable();
            $t->string('scu_sell_stock', 255)->nullable();
            $t->decimal('scu_sell_stock_avg', 12, 4)->nullable();
            $t->string('scu_sell', 255)->nullable();
            $t->decimal('scu_sell_avg', 12, 4)->nullable();
            $t->string('status_buy', 255)->nullable();
            $t->string('status_sell', 255)->nullable();
            $t->string('container_sizes', 255)->nullable();
            $t->timestamp('date_added')->nullable();
            $t->timestamp('date_modified')->nullable();
            $t->string('commodity_name', 255)->nullable();
            $t->decimal('terminal_name', 12, 4)->nullable();
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_commodities_prices_all');
    }
};