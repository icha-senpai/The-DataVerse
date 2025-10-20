<?php namespace Dataverse\Core\Models;

use Model;

class Outpost extends Model
{
    protected $table = 'uex_outposts';
    protected $guarded = ['*'];
    protected $fillable = [
        'id', 'id_star_system', 'id_planet', 'name', 'type',
        'is_mining', 'is_refinery', 'has_trade_terminal'
    ];

    public $timestamps = true;

    public $belongsTo = [
        'system' => [StarSystem::class, 'key' => 'id_star_system'],
        'planet' => [Planet::class, 'key' => 'id_planet']
    ];

    public $hasMany = [
        'terminals' => [Terminal::class, 'key' => 'id_outpost']
    ];
}
