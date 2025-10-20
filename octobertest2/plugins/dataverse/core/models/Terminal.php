<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Terminal extends Model
{
    use Validation;

    protected $table = 'uex_terminals';
    protected $guarded = ['*'];
    protected $fillable = [
        'id_city',
        'id_outpost',
        'id_space_station',
        'name',
        'type',
        'is_trade'
    ];
    public $timestamps = true;

    protected $casts = [
        'is_trade' => 'boolean'
    ];

    public $belongsTo = [
        'city'          => [City::class, 'key' => 'id_city'],
        'outpost'       => [Outpost::class, 'key' => 'id_outpost'],
        'space_station' => [SpaceStation::class, 'key' => 'id_space_station']
    ];

    public $hasMany = [
        'prices' => [Price::class, 'key' => 'terminal_id']
    ];
}
