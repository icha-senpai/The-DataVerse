<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class StarSystem extends Model
{
    use Validation;

    protected $table = 'uex_star_systems';
    protected $guarded = ['*'];
    protected $fillable = ['name', 'code', 'type'];

    public $timestamps = true;

    public $hasMany = [
        'planets' => [Planet::class, 'key' => 'id_star_system'],
        'stations' => [SpaceStation::class, 'key' => 'id_star_system']
    ];

    public $rules = [
        'name' => 'required|string|max:255'
    ];
}
