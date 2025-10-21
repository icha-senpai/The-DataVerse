<?php namespace Dataverse\Uex\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class CommodityStatusSell extends Model
{
    use Validation;

    protected $table = 'uex_commodities_status_sell';
    protected $guarded = ['*'];
    protected $fillable = [
        'code','name','name_short','name_abbr',
        'percentage','percentage_start','percentage_end','colors'
    ];
    public $rules = [];
}
