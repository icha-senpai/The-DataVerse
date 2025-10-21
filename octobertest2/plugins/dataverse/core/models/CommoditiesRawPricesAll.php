<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class CommoditiesRawPricesAll extends Model
{
    use Validation;

    protected $table = 'uex_commodities_raw_prices_all';
    protected $guarded = ['*'];
    protected $fillable = ['id', 'id_commodity', 'id_terminal', 'price_buy', 'price_buy_avg', 'price_sell', 'price_sell_avg', 'date_added', 'date_modified', 'commodity_name', 'terminal_name'];
    public $rules = [];
}