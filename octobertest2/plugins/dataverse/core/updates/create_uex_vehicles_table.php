<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use October\Rain\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uex_vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_company')->nullable();
            $table->unsignedInteger('id_parent')->nullable();
            $table->string('company_name')->nullable();
            $table->string('name')->nullable();
            $table->string('name_full')->nullable();
            $table->string('uuid')->nullable()->index();
            $table->string('slug')->nullable()->index();

            // numerical specs
            $table->float('scu')->nullable();
            $table->float('crew')->nullable();
            $table->float('mass')->nullable();
            $table->float('width')->nullable();
            $table->float('height')->nullable();
            $table->float('length')->nullable();
            $table->float('fuel_hydrogen')->nullable();
            $table->float('fuel_quantum')->nullable();

            $table->string('pad_type')->nullable();
            $table->string('game_version')->nullable();
            $table->string('container_sizes')->nullable();

            // feature flags
            $flags = [
                'is_addon','is_boarding','is_bomber','is_cargo','is_carrier','is_civilian','is_concept',
                'is_construction','is_datarunner','is_docking','is_emp','is_exploration','is_ground_vehicle',
                'is_hangar','is_industrial','is_interdiction','is_loading_dock','is_medical','is_military',
                'is_mining','is_passenger','is_qed','is_quantum_capable','is_racing','is_refinery','is_refuel',
                'is_repair','is_research','is_salvage','is_scanning','is_science','is_showdown_winner',
                'is_spaceship','is_starter','is_stealth','is_tractor_beam'
            ];
            foreach ($flags as $flag) {
                $table->boolean($flag)->default(false);
            }

            // media and references
            $table->text('url_photo')->nullable();
            $table->text('url_photos')->nullable();
            $table->text('url_store')->nullable();
            $table->text('url_brochure')->nullable();
            $table->text('url_hotsite')->nullable();
            $table->text('url_video')->nullable();

            // metadata
            $table->timestamp('date_added')->nullable();
            $table->timestamp('date_modified')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uex_vehicles');
    }
};
