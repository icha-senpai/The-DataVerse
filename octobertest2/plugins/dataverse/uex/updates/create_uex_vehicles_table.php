<?php namespace Dataverse\Uex\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use October\Rain\Database\Schema\Blueprint;

return new class extends Migration {
    public function up() {
        Schema::create('uex_vehicles', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('id_company')->nullable()->index();
            $t->unsignedInteger('id_parent')->nullable()->index();
            $t->string('company_name')->nullable();

            $t->string('name')->nullable()->index();
            $t->string('name_full')->nullable();
            $t->string('slug')->nullable()->index();
            $t->string('uuid')->nullable()->index();

            $t->float('scu')->nullable()->comment('Cargo capacity (SCU)');
            $t->string('crew')->nullable(); // API returns "1" sometimes as string
            $t->float('mass')->nullable();
            $t->float('width')->nullable();
            $t->float('height')->nullable();
            $t->float('length')->nullable();

            $t->float('fuel_quantum')->nullable();
            $t->float('fuel_hydrogen')->nullable();
            $t->string('container_sizes')->nullable();

            $t->string('pad_type')->nullable();
            $t->string('game_version')->nullable();

            // booleans (complete set)
            foreach ([
                'is_addon','is_boarding','is_bomber','is_cargo','is_carrier','is_civilian','is_concept','is_construction',
                'is_datarunner','is_docking','is_emp','is_exploration','is_ground_vehicle','is_hangar','is_industrial',
                'is_interdiction','is_loading_dock','is_medical','is_military','is_mining','is_passenger','is_qed',
                'is_quantum_capable','is_racing','is_refinery','is_refuel','is_repair','is_research','is_salvage',
                'is_scanning','is_science','is_showdown_winner','is_spaceship','is_starter','is_stealth','is_tractor_beam'
            ] as $flag) { $t->boolean($flag)->default(false); }

            $t->text('url_photo')->nullable();
            $t->text('url_photos')->nullable();
            $t->text('url_store')->nullable();
            $t->text('url_brochure')->nullable();
            $t->text('url_hotsite')->nullable();
            $t->text('url_video')->nullable();

            $t->unsignedBigInteger('date_added')->nullable();
            $t->unsignedBigInteger('date_modified')->nullable();

            $t->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('uex_vehicles'); }
};
