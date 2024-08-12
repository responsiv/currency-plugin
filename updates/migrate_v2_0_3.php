<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('system_site_definitions', 'currency_id')) {
            Schema::table('system_site_definitions', function(Blueprint $table) {
                $table->bigInteger('currency_id')->unsigned()->nullable()->index();
            });
        }

        if (!Schema::hasColumn('system_site_definitions', 'base_currency_id')) {
            Schema::table('system_site_definitions', function(Blueprint $table) {
                $table->bigInteger('base_currency_id')->unsigned()->nullable()->index();
            });
        }

        if (!Schema::hasColumn('responsiv_currency_currencies', 'is_default')) {
            Schema::table('responsiv_currency_currencies', function(Blueprint $table) {
                $table->integer('is_default')->default(false);
            });
        }
    }

    public function down()
    {
    }
};
