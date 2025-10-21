<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        $tables = [
            'uex_star_systems',
            'uex_planets',
            'uex_cities',
            'uex_outposts',
            'uex_space_stations',
            'uex_poi',
            'uex_terminals',
            'uex_commodities',
            'uex_prices',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'last_hash')) {
                Schema::table($table, function ($t) {
                    $t->string('last_hash', 64)->nullable()->index();
                });
            }
        }
    }

    public function down()
    {
        $tables = [
            'uex_star_systems',
            'uex_planets',
            'uex_cities',
            'uex_outposts',
            'uex_space_stations',
            'uex_poi',
            'uex_terminals',
            'uex_commodities',
            'uex_prices',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'last_hash')) {
                Schema::table($table, function ($t) {
                    $t->dropColumn('last_hash');
                });
            }
        }
    }
};
