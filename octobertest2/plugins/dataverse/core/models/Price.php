<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Price extends Model
{
    use Validation;

    protected $table = 'uex_prices';
    protected $guarded = ['*'];
    protected $fillable = [
        'commodity_id',
        'terminal_id',
        'price_buy',
        'price_sell',
        'fetched_at'
    ];
    public $timestamps = false;

    protected $casts = [
        'price_buy'  => 'float',
        'price_sell' => 'float',
        'fetched_at' => 'datetime'
    ];

    public $belongsTo = [
        'commodity' => [Commodity::class, 'key' => 'commodity_id'],
        'terminal'  => [Terminal::class, 'key' => 'terminal_id']
    ];

    public function scopeLatest($query)
    {
        return $query->orderBy('fetched_at', 'desc');
    }

    public function scopeForCommodity($query, string $name)
    {
        return $query->whereHas('commodity', function ($q) use ($name) {
            $q->where('name', $name);
        });
    }

    public function getProfitAttribute(): ?float
    {
        if (is_null($this->price_sell) || is_null($this->price_buy)) {
            return null;
        }
        return round($this->price_sell - $this->price_buy, 2);
    }
}
