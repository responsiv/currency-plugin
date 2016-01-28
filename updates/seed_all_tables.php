<?php namespace Responsiv\Currency;

use October\Rain\Database\Updates\Seeder;
use Responsiv\Currency\Models\Currency;

class SeedAllTables extends Seeder
{

    public function run()
    {
        Currency::create([
            'name' => 'U.S. Dollar',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'decimal_point' => '.',
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_primary' => true,
        ]);

        Currency::create([
            'name' => 'Euro',
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'decimal_point' => '.',
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_primary' => false
        ]);

        Currency::create([
            'name' => 'Pound Sterling',
            'currency_code' => 'GBP',
            'currency_symbol' => '£',
            'decimal_point' => '.',
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_primary' => false
        ]);

        Currency::create([
            'name' => 'Australian Dollar',
            'currency_code' => 'AUD',
            'currency_symbol' => '$',
            'decimal_point' => '.',
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_primary' => false
        ]);
    }

}