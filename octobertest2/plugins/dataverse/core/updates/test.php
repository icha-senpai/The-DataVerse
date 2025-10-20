<?php namespace Dataverse\Core\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Test Migration
 *
 * @link https://docs.octobercms.com/4.x/extend/database/structure.html
 */
return new class extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        Schema::table('dataverse_core_test', function(Blueprint $table) {
            // ...
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::table('dataverse_core_test', function(Blueprint $table) {
            // ...
        });
    }
};
