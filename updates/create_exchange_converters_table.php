<?php namespace Responsiv\Currency\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateConvertersTable extends Migration
{

    public function up()
    {
        Schema::create('responsiv_currency_exchange_converters', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('class_name', 100)->nullable();
            $table->integer('refresh_interval')->default(24);
            $table->text('config_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_currency_exchange_converters');
    }

}
