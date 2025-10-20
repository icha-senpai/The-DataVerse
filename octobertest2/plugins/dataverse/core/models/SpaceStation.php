<?php namespace Dataverse\Core\Models;

use Model;

class SpaceStation extends Model
{
    protected $table = 'uex_space_stations';
    protected $guarded = ['*'];
    protected $fillable = [
        'id', 'id_star_system', 'id_planet', 'name', 'nickname',
        'has_trade_terminal', 'has_refinery', 'is_landable'
    ];

    public $timestamps = true;

    public $belongsTo = [
        'system' => [StarSystem::class, 'key' => 'id_star_system'],
        'planet' => [Planet::class, 'key' => 'id_planet']
    ];

    public $hasMany = [
        'terminals' => [Terminal::class, 'key' => 'id_space_station']
    ];
}
