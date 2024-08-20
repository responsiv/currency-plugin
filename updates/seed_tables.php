<?php namespace Responsiv\Currency;

use October\Rain\Database\Updates\Seeder;
use Responsiv\Currency\Models\Currency;

class SeedAllTables extends Seeder
{

    public function run()
    {
        Currency::create([
            'name' => 'U.S. Dollar',
            'code' => 'USD',
            'currency_symbol' => '$',
            'decimal_point' => '.',
            'decimal_scale' => 2,
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_default' => true,
        ]);

        Currency::create([
            'name' => 'Euro',
            'code' => 'EUR',
            'currency_symbol' => '€',
            'decimal_point' => ',',
            'decimal_scale' => 2,
            'thousand_separator' => '.',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_default' => false
        ]);

        Currency::create([
            'name' => 'Pound Sterling',
            'code' => 'GBP',
            'currency_symbol' => '£',
            'decimal_point' => '.',
            'decimal_scale' => 2,
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_default' => false
        ]);

        Currency::create([
            'name' => 'Australian Dollar',
            'code' => 'AUD',
            'currency_symbol' => '$',
            'decimal_point' => '.',
            'decimal_scale' => 2,
            'thousand_separator' => ',',
            'place_symbol_before' => true,
            'is_enabled' => true,
            'is_default' => false
        ]);
    }

}