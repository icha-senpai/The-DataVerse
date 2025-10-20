<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class City extends Model
{
    use Validation;

    protected $table = 'uex_cities';
    protected $guarded = ['*'];
    protected $fillable = ['id_planet', 'name', 'type', 'has_spaceport'];
    public $timestamps = true;

    protected $casts = [
        'has_spaceport' => 'boolean'
    ];
    public $rules = []; // <-- Add this line
    public $belongsTo = [
        'planet' => [Planet::class, 'key' => 'id_planet']
    ];

    public $hasMany = [
        'terminals' => [Terminal::class, 'key' => 'id_city']
    ];
}
