<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use October\Rain\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        Schema::create('uex_planets', function (Blueprint $t) {
            $t->increments('id');
            $t->string('code')->nullable()->index();
            $t->unsignedBigInteger('date_added')->nullable()->comment('UEX unix ts');
            $t->unsignedBigInteger('date_modified')->nullable()->comment('UEX unix ts');
            $t->string('faction_name')->nullable();
            $t->unsignedInteger('id_faction')->nullable()->index();
            $t->unsignedInteger('id_jurisdiction')->nullable()->index();
            $t->unsignedInteger('id_star_system')->nullable()->index();
            $t->boolean('is_available')->default(false);
            $t->boolean('is_available_live')->default(false);
            $t->boolean('is_default')->default(false);
            $t->boolean('is_visible')->default(false);
            $t->string('jurisdiction_name')->nullable();
            $t->string('name')->nullable()->index();
            $t->string('name_origin')->nullable();
            $t->string('star_system_name')->nullable();
            $t->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('uex_planets'); }
};
