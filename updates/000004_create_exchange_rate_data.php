<?php namespace Responsiv\Currency\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        Schema::create('responsiv_currency_exchange_rate_data', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('rate_id')->unsigned()->nullable()->index();
            $table->decimal('rate_value', 40, 20)->nullable();
            $table->timestamp('valid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('responsiv_currency_exchange_rate_data');
    }
};
