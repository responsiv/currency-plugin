<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        $updater = App::make('db.updater');
        if (!Schema::hasTable('responsiv_currency_pairs')) {
            $updater->setUp(__DIR__.'/000004_create_exchange_rate_data.php');
        }

        if (!Schema::hasColumn('responsiv_currency_currencies', 'decimal_scale')) {
            Schema::table('responsiv_currency_currencies', function(Blueprint $table) {
                $table->integer('decimal_scale')->default(2);
            });
        }

        if (!Schema::hasColumn('responsiv_currency_exchange_converters', 'name')) {
            Schema::table('responsiv_currency_exchange_converters', function(Blueprint $table) {
                $table->string('name')->nullable();
                $table->boolean('is_enabled')->default(false);
                $table->boolean('is_default')->default(false);
                $table->integer('fallback_converter_id')->unsigned()->nullable();
            });
        }

        if (!Schema::hasColumn('responsiv_currency_exchange_rates', 'converter_id')) {
            Schema::table('responsiv_currency_exchange_rates', function(Blueprint $table) {
                $table->integer('converter_id')->unsigned()->nullable()->index();
            });
        }

        // Rename columns need their own query for SQLite

        if (!Schema::hasColumn('responsiv_currency_exchange_rates', 'from_currency_code')) {
            Schema::table('responsiv_currency_exchange_rates', function(Blueprint $table) {
                $table->renameColumn('from_currency', 'from_currency_code');
            });
        }

        if (!Schema::hasColumn('responsiv_currency_exchange_rates', 'to_currency_code')) {
            Schema::table('responsiv_currency_exchange_rates', function(Blueprint $table) {
                $table->renameColumn('to_currency', 'to_currency_code');
            });
        }

        if (!Schema::hasColumn('responsiv_currency_exchange_rates', 'rate_value')) {
            Schema::table('responsiv_currency_exchange_rates', function(Blueprint $table) {
                $table->renameColumn('rate', 'rate_value');
            });
        }
    }

    public function down()
    {
    }
};
