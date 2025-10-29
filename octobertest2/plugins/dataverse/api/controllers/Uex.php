<?php namespace Dataverse\Api\Controllers;

use Illuminate\Support\Facades\DB;
use Response;

class Uex extends \Cms\Classes\Controller
{
    public function onCities()
    {
        $data = DB::table('uex_cities')->get();
        return Response::json($data);
    }

    public function onCommodities()
    {
        $data = DB::table('uex_commodities')->get();
        return Response::json($data);
    }

    public function onPlanets()
    {
        $data = DB::table('uex_planets')->get();
        return Response::json($data);
    }

    public function onOutposts()
    {
        $data = DB::table('uex_outposts')->get();
        return Response::json($data);
    }

    public function onPoi()
    {
        $data = DB::table('uex_poi')->get();
        return Response::json($data);
    }

    public function onPrices()
    {
        $data = DB::table('uex_prices')->get();
        return Response::json($data);
    }

    public function onSpace_stations()
    {
        $data = DB::table('uex_space_stations')->get();
        return Response::json($data);
    }

    public function onTerminals()
    {
        $data = DB::table('uex_terminals')->get();
        return Response::json($data);
    }

    public function onStar_systems()
    {
        $data = DB::table('uex_star_systems')->get();
        return Response::json($data);
    }

    public function onVehicles()
    {
        $data = DB::table('uex_vehicles')->get();
        return Response::json($data);
    }
 // Add more for each table you need
}
