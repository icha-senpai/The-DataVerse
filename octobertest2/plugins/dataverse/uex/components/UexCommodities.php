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
        // optional preloads
    }

    public function onGetData()
    {
        $data = DB::table('uex_commodities as c')
            ->leftJoin('uex_commodities_prices_all as p', 'c.code', '=', 'p.code')
            ->leftJoin('uex_commodities_status_buy as sb', 'c.code', '=', 'sb.code')
            ->leftJoin('uex_commodities_status_sell as ss', 'c.code', '=', 'ss.code')
            ->select(
                'c.id',
                'c.code',
                'c.name',
                'p.buy_price',
                'p.sell_price',
                'sb.percentage as buy_status',
                'ss.percentage as sell_status'
            )
            ->orderBy('c.name')
            ->get();

        return Response::json($data);
    }

    public function onUpdateCommodity()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        DB::table('uex_commodities_prices_all')
            ->where('code', $data['code'])
            ->update([
                'buy_price'  => $data['buy_price'],
                'sell_price' => $data['sell_price']
            ]);

        return Response::json(['success' => true]);
    }
}
