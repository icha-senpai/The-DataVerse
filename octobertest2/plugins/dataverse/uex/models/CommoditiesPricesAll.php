<?php namespace Dataverse\Uex\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class CommoditiesPricesAll extends Model
{
    use Validation;

    protected $table = 'uex_commodities_prices_all';
    protected $guarded = ['*'];
    protected $fillable = ['id', 'id_commodity', 'id_terminal', 'price_buy', 'price_buy_avg', 'price_sell', 'price_sell_avg', 'scu_buy', 'scu_buy_avg', 'scu_sell_stock', 'scu_sell_stock_avg', 'scu_sell', 'scu_sell_avg', 'status_buy', 'status_sell', 'container_sizes', 'date_added', 'date_modified', 'commodity_name', 'terminal_name'];
    public $rules = [];
}