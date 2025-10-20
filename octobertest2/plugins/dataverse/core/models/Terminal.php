<?php namespace Dataverse\Core\Models;

use Model;

class Terminal extends Model
{
    protected $table = 'uex_terminals';
    protected $guarded = ['*'];
    protected $fillable = [
        'id', 'id_city', 'id_outpost', 'id_space_station',
        'name', 'type', 'is_trade'
    ];

    public $timestamps = true;

    public $belongsTo = [
        'city' => [City::class, 'key' => 'id_city'],
        'outpost' => [Outpost::class, 'key' => 'id_outpost'],
        'station' => [SpaceStation::class, 'key' => 'id_space_station']
    ];

    public $hasMany = [
        'prices' => [Price::class, 'key' => 'terminal_id']
    ];
}
