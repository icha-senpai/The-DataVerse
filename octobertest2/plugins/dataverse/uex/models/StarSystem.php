<?php namespace Dataverse\Uex\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class StarSystem extends Model
{
    use Validation;

    protected $table = 'uex_star_systems';
    protected $guarded = ['*'];
    protected $fillable = [
        'code','date_added','date_modified','faction_name','id_faction','id_jurisdiction',
        'is_available','is_available_live','is_default','is_visible','jurisdiction_name','name','wiki'
    ];
    public $rules = [];
    protected $casts = [
        'date_added'=>'integer','date_modified'=>'integer',
        'is_available'=>'boolean','is_available_live'=>'boolean',
        'is_default'=>'boolean','is_visible'=>'boolean'
    ];
}
