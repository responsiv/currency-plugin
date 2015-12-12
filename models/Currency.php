<?php namespace Responsiv\Currency\Models;

use Model;

/**
 * Currency Model
 */
class Currency extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_currency_currencies';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * Formats supplied currency to supplied settings.
     * @param  mixed  $number   Currency amount
     * @param  integer $decimals Decimal places to include
     * @return string
     */
    public static function formatCurrency($number, $decimals = 2)
    {
        if (!strlen($number)) {
            return null;
        }

        $settings = self::instance();

        $negative = $number < 0;
        $negativeSymbol = null;

        if ($negative) {
            $number *= -1;
            $negativeSymbol = '-';
        }

        $number = number_format($number, $decimals, $settings->decimal_point, $settings->thousand_separator);

        if ($settings->place_symbol_before) {
            return $negativeSymbol.$settings->currency_symbol.$number;
        }
        else {
            return $negativeSymbol.$number.$settings->currency_symbol;
        }
    }

    public static function clearCache()
    {

    }

}