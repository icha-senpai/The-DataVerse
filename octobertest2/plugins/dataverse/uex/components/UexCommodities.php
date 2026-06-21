<?php namespace Dataverse\Uex\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Facades\DB;
use Response;

class UexCommodities extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'UEX Commodities Table',
            'description' => 'Pulls live data from UEX tables and exposes it as JSON for Tabulator.'
        ];
    }

    public function onRun()
    {
        //
    }

    public function onGetData()
    {
        try {
            $data = DB::table('uex_commodities')
                ->select('id', 'code', 'name')
                ->orderBy('name')
                ->get();

            return Response::json($data);
        } catch (\Throwable $e) {
            return Response::make($e->getMessage(), 500);
        }
    }

    public function onUpdateCommodity()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['code'])) {
            return Response::json(['error' => 'Missing code'], 400);
        }

        DB::table('uex_commodities_prices_all')
            ->where('id_commodity', function ($query) use ($data) {
                $query->select('id')
                      ->from('uex_commodities')
                      ->where('code', $data['code'])
                      ->limit(1);
            })
            ->update([
                'price_buy'  => $data['buy_price'] ?? null,
                'price_sell' => $data['sell_price'] ?? null,
                'date_modified' => now()->timestamp,
            ]);

        return Response::json(['success' => true]);
    }
}
