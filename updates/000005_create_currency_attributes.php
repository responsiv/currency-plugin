<?php namespace Responsiv\Currency\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_currency_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model_type', 512);
            $table->integer('model_id');
            $table->string('currency_code', 16);
            $table->string('attribute', 128);
            $table->mediumText('value')->nullable();
            $table->index(
                ['model_type', 'model_id', 'currency_code'],
                'currency_type_id_code_index'
            );
            $table->unique(
                ['model_type', 'model_id', 'currency_code', 'attribute'],
                'currency_unique_index'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_currency_attributes');
    }
};
