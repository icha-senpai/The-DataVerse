<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Outpost extends Model
{
    use Validation;

    protected $table = 'uex_outposts';
    protected $guarded = ['*'];
    protected $fillable = ['id_planet', 'name', 'type'];
    public $timestamps = true;

    public $belongsTo = [
        'planet' => [Planet::class, 'key' => 'id_planet']
    ];

    public $hasMany = [
        'terminals' => [Terminal::class, 'key' => 'id_outpost']
    ];
}
