<?php namespace Dataverse\Core\Models;

use Model;

class Commodity extends Model
{
    protected $table = 'uex_commodities';
    protected $guarded = ['*'];
    protected $fillable = [
        'id', 'name', 'code', 'kind',
        'is_buyable', 'is_sellable', 'is_illegal',
        'is_refinable', 'is_raw'
    ];

    public $timestamps = true;

    public $hasMany = [
        'prices' => [Price::class, 'key' => 'commodity_id']
    ];
}
