<?php namespace Responsiv\Currency\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateRatesTable extends Migration
{

    public function up()
    {
        Schema::create('responsiv_currency_rates', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('from_currency', 3)->nullable();
            $table->string('to_currency', 3)->nullable();
            $table->decimal('rate', 15, 4)->nullable();
            $table->index(['from_currency', 'to_currency'], 'from_currency_to_currency');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_currency_rates');
    }

}
