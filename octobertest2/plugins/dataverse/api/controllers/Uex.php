<?php namespace Dataverse\Api\Controllers;

use Illuminate\Support\Facades\DB;
use Response;

class Uex extends \Cms\Classes\Controller
{
    public function onCities()
    {
        return $this->respondWithTable('uex_cities');
    }

    public function onCommodities()
    {
        return $this->respondWithTable('uex_commodities');
    }

    public function onPlanets()
    {
        return $this->respondWithTable('uex_planets');
    }

    public function onOutposts()
    {
        return $this->respondWithTable('uex_outposts');
    }

    public function onPoi()
    {
        return $this->respondWithTable('uex_poi');
    }

    public function onPrices()
    {
        return $this->respondWithTable('uex_prices');
    }

    public function onSpaceStations()
    {
        return $this->respondWithTable('uex_space_stations');
    }

    public function onTerminals()
    {
        return $this->respondWithTable('uex_terminals');
    }

    public function onStarSystems()
    {
        return $this->respondWithTable('uex_star_systems');
    }

    public function onVehicles()
    {
        return $this->respondWithTable('uex_vehicles');
    }

    protected function respondWithTable(string $table)
    {
        return Response::json(DB::table($table)->get());
    }
}
