<?php namespace Dataverse\Core\Models;

use Model;

class Poi extends Model
{
    protected $table = 'uex_poi';
    protected $guarded = ['*'];
    protected $fillable = ['id', 'id_planet', 'name', 'type', 'description'];
    public $timestamps = true;

    public $belongsTo = [
        'planet' => [Planet::class, 'key' => 'id_planet']
    ];
}
