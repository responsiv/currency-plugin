<?php namespace Responsiv\Currency\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_currency_currencies', function($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('code')->nullable()->index();
            $table->string('currency_symbol')->nullable();
            $table->string('decimal_point')->nullable();
            $table->integer('decimal_scale')->default(2);
            $table->string('thousand_separator')->nullable();
            $table->boolean('place_symbol_before')->default(true);
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_currency_currencies');
    }
};
