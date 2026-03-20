<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('system_site_groups', 'base_currency_id')) {
            Schema::table('system_site_groups', function(Blueprint $table) {
                $table->integer('base_currency_id')->unsigned()->nullable()->index();
            });
        }
    }

    public function down()
    {
    }
};
