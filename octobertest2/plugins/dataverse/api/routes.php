<?php

use Dataverse\Api\Controllers\Uex;

Route::group(['prefix' => 'api/uex'], function () {
    Route::get('cities', [Uex::class, 'onCities']);
    Route::get('commodities', [Uex::class, 'onCommodities']);
    Route::get('planets', [Uex::class, 'onPlanets']);
    Route::get('outposts', [Uex::class, 'onOutposts']);
    Route::get('poi', [Uex::class, 'onPoi']);
    Route::get('prices', [Uex::class, 'onPrices']);
    Route::get('space_stations', [Uex::class, 'onSpace_stations']);
    Route::get('terminals', [Uex::class, 'onTerminals']);
    Route::get('star_systems', [Uex::class, 'onStar_systems']);
    Route::get('vehicles', [Uex::class, 'onVehicles']);

});