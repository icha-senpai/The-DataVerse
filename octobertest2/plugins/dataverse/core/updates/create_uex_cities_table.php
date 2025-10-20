<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use October\Rain\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        Schema::create('uex_cities', function (Blueprint $t) {
            $t->increments('id');
            $t->string('code')->nullable()->index();
            $t->unsignedBigInteger('date_added')->nullable();
            $t->unsignedBigInteger('date_modified')->nullable();
            $t->string('faction_name')->nullable();

            // known has_* from scan segment
            $t->boolean('has_cargo_center')->default(false);
            $t->boolean('has_clinic')->default(false);
            $t->boolean('has_docking_port')->default(false);
            $t->boolean('has_food')->default(false);
            $t->boolean('has_freight_elevator')->default(false);
            $t->boolean('has_gravity')->default(false);
            $t->boolean('has_habitation')->default(false);
            $t->boolean('has_loading_dock')->default(false);
            $t->boolean('has_quantum_marker')->default(false);
            $t->boolean('has_refinery')->default(false);

            // availability flags
            $t->boolean('is_available')->default(false);
            $t->boolean('is_available_live')->default(false);
            $t->boolean('is_decommissioned')->default(false);
            $t->boolean('is_default')->default(false);
            $t->boolean('is_landable')->default(false);
            $t->boolean('is_monitored')->default(false);
            $t->boolean('is_visible')->default(false);

            $t->string('jurisdiction_name')->nullable();
            $t->string('moon_name')->nullable();
            $t->string('name')->nullable()->index();
            $t->string('orbit_name')->nullable();
            $t->string('pad_types')->nullable();
            $t->string('planet_name')->nullable();
            $t->string('star_system_name')->nullable();
            $t->string('wiki')->nullable();

            $t->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('uex_cities'); }
};
