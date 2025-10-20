<?php namespace Dataverse\Core\Models;

use Model;

class Planet extends Model
{
    protected $table = 'uex_planets';
    protected $guarded = ['*'];
    protected $fillable = [
        'id', 'id_star_system', 'name', 'code', 'type', 'is_landable'
    ];

    public $timestamps = true;

    public $belongsTo = [
        'system' => [StarSystem::class, 'key' => 'id_star_system']
    ];

    public $hasMany = [
        'cities'  => [City::class, 'key' => 'id_planet'],
        'outposts'=> [Outpost::class, 'key' => 'id_planet'],
        'stations'=> [SpaceStation::class, 'key' => 'id_planet'],
        'poi'     => [Poi::class, 'key' => 'id_planet'],
    ];
}
