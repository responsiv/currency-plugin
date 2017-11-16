<?php namespace Responsiv\Currency\Helpers;

use Responsiv\Currency\Models\Currency as CurrencyModel;
use Responsiv\Currency\Classes\Converter as CurrencyConverter;

/**
 * Currency helper
 *
 * Use the facade to access this class:
 *
 *   1. use Responsiv\Currency\Facades\Currency as CurrencyHelper;
 *   2. CurrencyHelper::method();
 */
class Currency
{
    /**
     * Formats a number to currency.
     * @param int $number
     * @param array $options
     * @return string
     */
    public function format($number, $options = [])
    {
        $result = $number;

        extract(array_merge([
            'to' => null,
            'from' => null,
            'format' => null, // long|short
            'decimals' => null
        ], $options));

        $toCurrency = strtoupper($to);
        $fromCurrency = strtoupper($from);

        if ($decimals === null) {
            $decimals = $format == 'short' ? 0 : 2;
        }

        if ($toCurrency) {
            $result = $this->convert($result, $toCurrency, $fromCurrency);
        }

        $currencyObj = $toCurrency
            ? CurrencyModel::findByCode($toCurrency)
            : CurrencyModel::getPrimary();

        $result = $currencyObj
            ? $currencyObj->formatCurrency($result, $decimals)
            : number_format($result, $decimals);

        if ($format == 'long') {
            $result .= ' ' . ($toCurrency ?: $this->primaryCode());
        }

        return $result;
    }

    public function convert($value, $toCurrency, $fromCurrency = null)
    {
        if (!$fromCurrency) {
            $fromCurrency = $this->primaryCode();
        }

        return CurrencyConverter::instance()->convert($value, $fromCurrency, $toCurrency, null);
    }

    public function primaryCode()
    {
        return CurrencyModel::getPrimary()->currency_code;
    }
}
