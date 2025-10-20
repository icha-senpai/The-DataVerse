<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Commodity extends Model
{
    use Validation;

    protected $table = 'uex_commodities';
    protected $guarded = ['*'];
    protected $fillable = ['name', 'type', 'category', 'mass'];
    public $timestamps = true;

    protected $casts = [
        'mass' => 'float'
    ];

    public $rules = []; // <-- Add this line

    public $hasMany = [
        'prices' => [Price::class, 'key' => 'commodity_id']
    ];
}
