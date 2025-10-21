<?php namespace Dataverse\Uex\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class SpaceStation extends Model
{
    use Validation;

    protected $table = 'uex_space_stations';
    protected $guarded = ['*'];
    protected $fillable = [
        'city_name','date_added','date_modified','faction_name',
        'has_cargo_center','has_clinic','has_docking_port','has_food','has_freight_elevator','has_gravity',
        'has_habitation','has_loading_dock','has_quantum_marker','has_refinery',
        'is_available','is_available_live','is_default','is_jump_point','is_lagrange',
        'is_landable','is_monitored','is_visible',
        'jurisdiction_name','moon_name','name','nickname','orbit_name','pad_types','planet_name','star_system_name'
    ];
    public $rules = [];
    protected $casts = [
        'date_added'=>'integer','date_modified'=>'integer',
        'has_cargo_center'=>'boolean','has_clinic'=>'boolean','has_docking_port'=>'boolean','has_food'=>'boolean',
        'has_freight_elevator'=>'boolean','has_gravity'=>'boolean','has_habitation'=>'boolean','has_loading_dock'=>'boolean',
        'has_quantum_marker'=>'boolean','has_refinery'=>'boolean',
        'is_available'=>'boolean','is_available_live'=>'boolean','is_default'=>'boolean','is_jump_point'=>'boolean',
        'is_lagrange'=>'boolean','is_landable'=>'boolean','is_monitored'=>'boolean','is_visible'=>'boolean'
    ];
}
