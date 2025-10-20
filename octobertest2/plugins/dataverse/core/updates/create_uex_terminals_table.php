<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use October\Rain\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        Schema::create('uex_terminals', function (Blueprint $t) {
            $t->increments('id');
            $t->string('city_name')->nullable();
            $t->string('code')->nullable()->index();
            $t->string('company_name')->nullable();
            $t->text('contact_url')->nullable();
            $t->unsignedBigInteger('date_added')->nullable();
            $t->unsignedBigInteger('date_modified')->nullable();
            $t->string('displayname')->nullable();
            $t->string('faction_name')->nullable();
            $t->string('game_version')->nullable();

            $t->boolean('has_docking_port')->default(false);
            $t->boolean('has_freight_elevator')->default(false);
            $t->boolean('has_loading_dock')->default(false);

            $t->unsignedInteger('id_city')->nullable()->index();
            $t->unsignedInteger('id_moon')->nullable()->index();
            $t->unsignedInteger('id_outpost')->nullable()->index();
            $t->unsignedInteger('id_planet')->nullable()->index();
            $t->unsignedInteger('id_space_station')->nullable()->index();
            $t->unsignedInteger('id_star_system')->nullable()->index();

            $t->boolean('is_available')->default(false);
            $t->boolean('is_available_live')->default(false);
            $t->boolean('is_cargo_center')->default(false);
            $t->boolean('is_clinic')->default(false);
            $t->boolean('is_food')->default(false);
            $t->boolean('is_gravity')->default(false);
            $t->boolean('is_habitation')->default(false);
            $t->boolean('is_nqa')->default(false);
            $t->boolean('is_player_owned')->default(false);
            $t->boolean('is_refinery')->default(false);
            $t->boolean('is_refuel')->default(false);
            $t->boolean('is_repair')->default(false);

            $t->string('max_container_size')->nullable();
            $t->string('mcs')->nullable();

            $t->string('moon_name')->nullable();
            $t->string('name')->nullable()->index();
            $t->string('nickname')->nullable();
            $t->string('orbit_name')->nullable();
            $t->string('outpost_name')->nullable();
            $t->string('planet_name')->nullable();

            $t->string('screenshot')->nullable();
            $t->string('screenshot_author')->nullable();
            $t->text('screenshot_full')->nullable();

            $t->string('space_station_name')->nullable();
            $t->string('star_system_name')->nullable();
            $t->string('type')->nullable();

            $t->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('uex_terminals'); }
};
