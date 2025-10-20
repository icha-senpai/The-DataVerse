<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Terminal extends Model
{
    use Validation;

    protected $table = 'uex_terminals';
    protected $guarded = ['*'];
    protected $fillable = [
        'city_name','code','company_name','contact_url','date_added','date_modified','displayname',
        'faction_name','game_version','has_docking_port','has_freight_elevator','has_loading_dock',
        'id_city','id_moon','id_outpost','id_planet','id_space_station','id_star_system',
        'is_available','is_available_live','is_cargo_center','is_clinic','is_food','is_gravity','is_habitation',
        'is_nqa','is_player_owned','is_refinery','is_refuel','is_repair','max_container_size','mcs','moon_name',
        'name','nickname','orbit_name','outpost_name','planet_name','screenshot','screenshot_author','screenshot_full',
        'space_station_name','star_system_name','type'
    ];
    public $rules = [];
    protected $casts = [
        'date_added'=>'integer','date_modified'=>'integer',
        'has_docking_port'=>'boolean','has_freight_elevator'=>'boolean','has_loading_dock'=>'boolean',
        'is_available'=>'boolean','is_available_live'=>'boolean','is_cargo_center'=>'boolean','is_clinic'=>'boolean',
        'is_food'=>'boolean','is_gravity'=>'boolean','is_habitation'=>'boolean','is_nqa'=>'boolean',
        'is_player_owned'=>'boolean','is_refinery'=>'boolean','is_refuel'=>'boolean','is_repair'=>'boolean'
    ];
}
