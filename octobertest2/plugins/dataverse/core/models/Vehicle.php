<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Vehicle extends Model
{
    use Validation;

    protected $table = 'uex_vehicles';
    protected $guarded = ['*'];
    protected $fillable = [
        'id_company', 'id_parent', 'company_name', 'name', 'name_full', 'uuid', 'slug', 'scu',
        'crew', 'mass', 'width', 'height', 'length', 'fuel_hydrogen', 'fuel_quantum', 'pad_type',
        'game_version', 'container_sizes', 'url_photo', 'url_photos', 'url_store', 'url_brochure',
        'url_hotsite', 'url_video', 'date_added', 'date_modified'
    ];

    public $rules = [];

    protected $casts = [
        'scu' => 'float',
        'crew' => 'float',
        'mass' => 'float',
        'width' => 'float',
        'height' => 'float',
        'length' => 'float',
        'fuel_hydrogen' => 'float',
        'fuel_quantum' => 'float',
        'date_added' => 'datetime',
        'date_modified' => 'datetime',
        // dynamic cast of flags
        'is_addon' => 'boolean', 'is_boarding' => 'boolean', 'is_bomber' => 'boolean', 'is_cargo' => 'boolean',
        'is_carrier' => 'boolean', 'is_civilian' => 'boolean', 'is_concept' => 'boolean', 'is_construction' => 'boolean',
        'is_datarunner' => 'boolean', 'is_docking' => 'boolean', 'is_emp' => 'boolean', 'is_exploration' => 'boolean',
        'is_ground_vehicle' => 'boolean', 'is_hangar' => 'boolean', 'is_industrial' => 'boolean', 'is_interdiction' => 'boolean',
        'is_loading_dock' => 'boolean', 'is_medical' => 'boolean', 'is_military' => 'boolean', 'is_mining' => 'boolean',
        'is_passenger' => 'boolean', 'is_qed' => 'boolean', 'is_quantum_capable' => 'boolean', 'is_racing' => 'boolean',
        'is_refinery' => 'boolean', 'is_refuel' => 'boolean', 'is_repair' => 'boolean', 'is_research' => 'boolean',
        'is_salvage' => 'boolean', 'is_scanning' => 'boolean', 'is_science' => 'boolean', 'is_showdown_winner' => 'boolean',
        'is_spaceship' => 'boolean', 'is_starter' => 'boolean', 'is_stealth' => 'boolean', 'is_tractor_beam' => 'boolean'
    ];

    public $timestamps = true;

    public $belongsTo = [
        'company' => [\Dataverse\Core\Models\Company::class, 'key' => 'id_company']
    ];
}
