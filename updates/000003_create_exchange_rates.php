<?php namespace Responsiv\Currency\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_currency_exchange_rates', function($table) {
            $table->increments('id');
            $table->string('from_currency')->nullable();
            $table->string('to_currency')->nullable();
            $table->decimal('rate_value', 15, 8)->nullable();
            $table->index(['from_currency', 'to_currency'], 'from_currency_to_currency');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_currency_exchange_rates');
    }
};
