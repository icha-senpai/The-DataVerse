<?php namespace Dataverse\Core\Models;

use Model;

class StarSystem extends Model
{
    protected $table = 'uex_star_systems';
    protected $guarded = ['*'];
    protected $fillable = ['id', 'name', 'code', 'description'];

    public $timestamps = true;

    public $hasMany = [
        'planets' => [Planet::class, 'key' => 'id_star_system'],
        'cities'  => [City::class, 'key' => 'id_star_system'],
        'stations'=> [SpaceStation::class, 'key' => 'id_star_system'],
    ];
}
