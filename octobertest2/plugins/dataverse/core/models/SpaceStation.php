<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class SpaceStation extends Model
{
    use Validation;

    protected $table = 'uex_space_stations';
    protected $guarded = ['*'];
    protected $fillable = ['id_star_system', 'name', 'type'];
    public $timestamps = true;
    public $rules = []; // <-- Add this line
    public $belongsTo = [
        'star_system' => [StarSystem::class, 'key' => 'id_star_system']
    ];

    public $hasMany = [
        'terminals' => [Terminal::class, 'key' => 'id_space_station']
    ];
}
