<?php namespace Dataverse\Core\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Company extends Model
{
    use Validation;

    protected $table = 'uex_companies';
    protected $guarded = ['*'];
    protected $fillable = [
        'name', 'slug', 'description', 'headquarters', 'url'
    ];

    public $timestamps = true;
    public $rules = [];

    public $hasMany = [
        'vehicles' => [Vehicle::class, 'key' => 'company_id']
    ];
}
