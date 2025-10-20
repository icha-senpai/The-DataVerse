<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Commodity extends Model
{
    use Validation;

    protected $table = 'uex_commodities';
    protected $guarded = ['*'];
    protected $fillable = [
        'code','date_added','date_modified','id_parent',
        'is_available','is_available_live','is_buggy','is_buyable','is_explosive','is_extractable',
        'is_fuel','is_harvestable','is_illegal','is_raw','is_refinable','is_refined','is_sellable',
        'is_temporary','is_visible','is_volatile_qt','is_volatile_time',
        'kind','name','price_buy','price_sell','weight_scu','wiki'
    ];
    public $rules = [];
    protected $casts = [
        'date_added'=>'integer','date_modified'=>'integer',
        'is_available'=>'boolean','is_available_live'=>'boolean','is_buggy'=>'boolean','is_buyable'=>'boolean',
        'is_explosive'=>'boolean','is_extractable'=>'boolean','is_fuel'=>'boolean','is_harvestable'=>'boolean',
        'is_illegal'=>'boolean','is_raw'=>'boolean','is_refinable'=>'boolean','is_refined'=>'boolean',
        'is_sellable'=>'boolean','is_temporary'=>'boolean','is_visible'=>'boolean',
        'is_volatile_qt'=>'boolean','is_volatile_time'=>'boolean',
        'price_buy'=>'float','price_sell'=>'float','weight_scu'=>'float'
    ];

    public $hasMany = [
        'prices' => [Price::class, 'key' => 'commodity_id']
    ];
}
