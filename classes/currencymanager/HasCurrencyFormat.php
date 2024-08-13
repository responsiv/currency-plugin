<?php namespace Responsiv\Currency\Classes\CurrencyManager;

use Currency;
use Responsiv\Currency\Classes\ExchangeManager;
use Responsiv\Currency\Models\Currency as CurrencyModel;
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
            'site' => null,      // Set to true to apply site currency context (from, to)
            'in' => null,        // Currency code to display in (default fallback)
            'to' => null,        // Convert to currency
            'from' => null,      // Convert from currency (default fallback)
            'format' => null,    // Display format (long|short)
            'decimals' => null,  // Decimal override
            'baseValue' => true, // Base conversion (eg: cents → dollars)
        ], (array) $options));

        if ($decimals === null) {
            $decimals = $format === 'short' ? 0 : null;
        }

        // Sanitize input
        $inCurrency = $in ? strtoupper($in) : null;
        $toCurrency = $to ? strtoupper($to) : null;
        $fromCurrency = $from ? strtoupper($from) : null;

        // Apply site context
        if ($site === true) {
            $toCurrency = $this->getActiveCode();
            $fromCurrency = $this->getPrimaryCode();
        }

        // Convert currency
        if ($toCurrency !== $fromCurrency) {
            $result = $this->convert($result, $toCurrency, $fromCurrency);
        }

        // Lookup display currency object
        $currencyCode = $toCurrency ?: $inCurrency;
        $currencyObj = $currencyCode
            ? CurrencyModel::findByCode($currencyCode)
            : $this->getDefault();

        if (!$currencyObj) {
            throw new SystemException("Unable to load a currency definition.");
        }

        // Format currency from object
        $result = $currencyObj->formatCurrency($result, $decimals, $baseValue);
        if ($format === 'long') {
            $result .= ' ' . $currencyObj->currency_code;
        }

        return $result;
    }

    /**
     * convert a currency value from one currency to another
     */
    public function convert($value, $toCurrency, $fromCurrency = null)
    {
        if (!$fromCurrency) {
            $fromCurrency = $this->getDefaultCode();
        }

        $rate = ExchangeManager::instance()->getRate($fromCurrency, $toCurrency);

        return $value * $rate;
    }
}
