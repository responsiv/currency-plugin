<?php namespace Responsiv\Currency\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_currency_exchange_rates', function($table) {
            $table->increments('id');
            $table->integer('from_currency_id')->unsigned()->nullable()->index();
            $table->string('from_currency_code')->nullable();
            $table->integer('to_currency_id')->unsigned()->nullable()->index();
            $table->string('to_currency_code')->nullable();
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
