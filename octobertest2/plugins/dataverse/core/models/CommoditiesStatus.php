<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class CommoditiesStatus extends Model
{
    use Validation;

    protected $table = 'uex_commodities_status';
    protected $guarded = ['*'];
    protected $fillable = [];
    public $rules = [];
}