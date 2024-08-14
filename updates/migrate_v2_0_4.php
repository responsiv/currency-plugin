<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('system_site_definitions', 'currency_id')) {
            Schema::table('system_site_definitions', function(Blueprint $table) {
                $table->integer('currency_id')->unsigned()->nullable()->index();
            });
        }

        if (!Schema::hasColumn('system_site_definitions', 'base_currency_id')) {
            Schema::table('system_site_definitions', function(Blueprint $table) {
                $table->integer('base_currency_id')->unsigned()->nullable()->index();
            });
        }

        if (!Schema::hasColumn('responsiv_currency_currencies', 'is_default')) {
            Schema::table('responsiv_currency_currencies', function(Blueprint $table) {
                $table->integer('is_default')->default(false);
            });
        }

        if (!Schema::hasColumn('responsiv_currency_currencies', 'code')) {
            Schema::table('responsiv_currency_currencies', function(Blueprint $table) {
                $table->renameColumn('currency_code', 'code');
            });
        }
    }

    public function down()
    {
    }
};
