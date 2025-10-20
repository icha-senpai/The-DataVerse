<?php namespace Dataverse\Core\Models;

use Model;

class Price extends Model
{
    protected $table = 'uex_prices';
    protected $guarded = ['*'];
    protected $fillable = [
        'commodity_id', 'terminal_id',
        'price_buy', 'price_sell', 'fetched_at'
    ];
    public $timestamps = false;

    public $belongsTo = [
        'commodity' => [Commodity::class, 'key' => 'commodity_id'],
        'terminal'  => [Terminal::class, 'key' => 'terminal_id']
    ];
}
