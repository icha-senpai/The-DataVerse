<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Poi extends Model
{
    use Validation;

    protected $table = 'uex_poi';
    protected $guarded = ['*'];
    protected $fillable = ['id_planet', 'name', 'type'];
    public $timestamps = true;
    public $rules = []; // <-- Add this line
    public $belongsTo = [
        'planet' => [Planet::class, 'key' => 'id_planet']
    ];
}
