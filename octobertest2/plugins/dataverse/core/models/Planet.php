<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Planet extends Model
{
    use Validation;

    protected $table = 'uex_planets';
    protected $guarded = ['*'];
    protected $fillable = ['id_star_system', 'name', 'code', 'is_landable'];
    public $timestamps = true;

    protected $casts = [
        'is_landable' => 'boolean',
    ];

    public $belongsTo = [
        'star_system' => [StarSystem::class, 'key' => 'id_star_system']
    ];

    public $hasMany = [
        'cities' => [City::class, 'key' => 'id_planet'],
        'outposts' => [Outpost::class, 'key' => 'id_planet'],
        'poi' => [Poi::class, 'key' => 'id_planet']
    ];

    public $rules = [
        'name' => 'required|string|max:255'
    ];
}
