<?php namespace Responsiv\Currency\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_currency_exchange_rates', function($table) {
            $table->increments('id');
            $table->string('from_currency_code')->nullable();
            $table->string('to_currency_code')->nullable();
            $table->decimal('rate_value', 40, 20)->nullable();
            $table->index(['from_currency_code', 'to_currency_code'], 'from_currency_to_currency');
            $table->integer('converter_id')->unsigned()->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_currency_exchange_rates');
    }
};
