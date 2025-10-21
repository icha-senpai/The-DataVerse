<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('uex_vehicles') && !Schema::hasColumn('uex_vehicles', 'last_hash')) {
            Schema::table('uex_vehicles', function ($table) {
                $table->string('last_hash', 64)->nullable()->index();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('uex_vehicles') && Schema::hasColumn('uex_vehicles', 'last_hash')) {
            Schema::table('uex_vehicles', function ($table) {
                $table->dropColumn('last_hash');
            });
        }
    }
};
