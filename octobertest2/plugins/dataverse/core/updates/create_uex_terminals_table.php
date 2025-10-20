<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uex_terminals', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->unsignedInteger('id_city')->nullable();
            $table->unsignedInteger('id_outpost')->nullable();
            $table->unsignedInteger('id_space_station')->nullable();
            $table->string('name')->index();
            $table->string('type')->nullable();
            $table->boolean('is_trade')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_terminals');
    }
};
