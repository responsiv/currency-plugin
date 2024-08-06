<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('system_site_definitions', 'currency_id')) {
            Schema::table('system_site_definitions', function($table) {
                $table->bigInteger('currency_id')->unsigned()->nullable()->index();
            });
        }
    }

    public function down()
    {
    }
};
