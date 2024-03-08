<?php namespace Responsiv\Currency\Classes\CurrencyManager;

use Responsiv\Shop\Models\Currency as CurrencyModel;
use SystemException;

/**
 * HasCurrencyExchange
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
trait HasCurrencyFormat
{
    /**
     * format a number to currency.
     */
    public function format($number, $options = []): string
    {
        $result = (float) $number;

        extract(array_merge([
            'in' => null,        // Currency code to display in (default fallback)
            'to' => null,        // Convert to currency
            'from' => null,      // Convert from currency (default fallback)
            'format' => null,    // Display format (long|short)
            'decimals' => null,  // Decimal override
            'baseValue' => true, // Base conversion (eg: cents → dollars)
        ], (array) $options));

        if ($decimals === null) {
            $decimals = $format == 'short' ? 0 : null;
        }

        $toCurrency = strtoupper($to);
        $fromCurrency = strtoupper($from);

        if ($toCurrency) {
            $result = $this->convert($result, $toCurrency, $fromCurrency);
        }

        $currencyCode = $toCurrency ?: $in;

        $currencyObj = $currencyCode
            ? CurrencyModel::findByCode($currencyCode)
            : CurrencyModel::getPrimary();

        if (!$currencyObj) {
            throw new SystemException("Unable to load a currency definition.");
        }

        $result = $currencyObj->formatCurrency($result, $decimals, $baseValue);

        if ($format == 'long') {
            $result .= ' ' . $currencyObj->currency_code;
        }

        return $result;
    }
}
