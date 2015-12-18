<?php namespace Responsiv\Currency;

use October\Rain\Database\Updates\Seeder;
use Responsiv\Currency\Models\Currency;

class SeedAllTables extends Seeder
{

    public function run()
    {
        Currency::create([
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'decimal_point' => '.',
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_primary' => true,
        ]);
    }

}
