<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Vehicle extends Model
{
    use Validation;

    protected $table = 'uex_vehicles';
    protected $guarded = ['*'];
    protected $fillable = [
        'id_company','id_parent','company_name','name','name_full','slug','uuid',
        'scu','crew','mass','width','height','length','fuel_quantum','fuel_hydrogen','container_sizes',
        'pad_type','game_version',
        'is_addon','is_boarding','is_bomber','is_cargo','is_carrier','is_civilian','is_concept','is_construction',
        'is_datarunner','is_docking','is_emp','is_exploration','is_ground_vehicle','is_hangar','is_industrial','is_interdiction',
        'is_loading_dock','is_medical','is_military','is_mining','is_passenger','is_qed','is_quantum_capable','is_racing',
        'is_refinery','is_refuel','is_repair','is_research','is_salvage','is_scanning','is_science','is_showdown_winner',
        'is_spaceship','is_starter','is_stealth','is_tractor_beam',
        'url_photo','url_photos','url_store','url_brochure','url_hotsite','url_video',
        'date_added','date_modified'
    ];
    public $rules = [];
    protected $casts = [
        'scu'=>'float','mass'=>'float','width'=>'float','height'=>'float','length'=>'float',
        'fuel_quantum'=>'float','fuel_hydrogen'=>'float','date_added'=>'integer','date_modified'=>'integer',
        'is_addon'=>'boolean','is_boarding'=>'boolean','is_bomber'=>'boolean','is_cargo'=>'boolean','is_carrier'=>'boolean',
        'is_civilian'=>'boolean','is_concept'=>'boolean','is_construction'=>'boolean','is_datarunner'=>'boolean',
        'is_docking'=>'boolean','is_emp'=>'boolean','is_exploration'=>'boolean','is_ground_vehicle'=>'boolean',
        'is_hangar'=>'boolean','is_industrial'=>'boolean','is_interdiction'=>'boolean','is_loading_dock'=>'boolean',
        'is_medical'=>'boolean','is_military'=>'boolean','is_mining'=>'boolean','is_passenger'=>'boolean','is_qed'=>'boolean',
        'is_quantum_capable'=>'boolean','is_racing'=>'boolean','is_refinery'=>'boolean','is_refuel'=>'boolean',
        'is_repair'=>'boolean','is_research'=>'boolean','is_salvage'=>'boolean','is_scanning'=>'boolean',
        'is_science'=>'boolean','is_showdown_winner'=>'boolean','is_spaceship'=>'boolean','is_starter'=>'boolean',
        'is_stealth'=>'boolean','is_tractor_beam'=>'boolean'
    ];
}
