<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('uex_commodities_status_buy') && !Schema::hasColumn('uex_commodities_status_buy', 'last_hash')) {
            Schema::table('uex_commodities_status_buy', function (Blueprint $t) {
                $t->string('last_hash', 64)->nullable()->after('colors');
            });
        }

        if (Schema::hasTable('uex_commodities_status_sell') && !Schema::hasColumn('uex_commodities_status_sell', 'last_hash')) {
            Schema::table('uex_commodities_status_sell', function (Blueprint $t) {
                $t->string('last_hash', 64)->nullable()->after('colors');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('uex_commodities_status_buy') && Schema::hasColumn('uex_commodities_status_buy', 'last_hash')) {
            Schema::table('uex_commodities_status_buy', function (Blueprint $t) {
                $t->dropColumn('last_hash');
            });
        }

        if (Schema::hasTable('uex_commodities_status_sell') && Schema::hasColumn('uex_commodities_status_sell', 'last_hash')) {
            Schema::table('uex_commodities_status_sell', function (Blueprint $t) {
                $t->dropColumn('last_hash');
            });
        }
    }
};
