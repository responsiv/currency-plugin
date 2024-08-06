<?php namespace Responsiv\Currency\Classes\ExchangeManager;

use Carbon\Carbon;
use Responsiv\Currency\Models\ExchangeRate;
use Responsiv\Currency\Models\ExchangeRateData;
use Responsiv\Currency\Models\ExchangeConverter;
use ApplicationException;
use Exception;

/**
 * HasCurrencyExchange
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
trait HasCurrencyExchange
{
    /**
     * @var array rateCache
     */
    public $rateCache = [];

    /**
     * getRate returns the exchange rate for two currencies.
     * From currency (USD) to currency (AUD).
     */
    public function getRate(string $fromCurrency, string $toCurrency): float
    {
        $fromCurrency = trim(strtoupper($fromCurrency));
        $toCurrency = trim(strtoupper($toCurrency));

        if ($fromCurrency === $toCurrency) {
            return 1;
        }

        // Look up in the cache
        $key = $fromCurrency.'_'.$toCurrency;
        if (array_key_exists($key, $this->rateCache)) {
            return $this->rateCache[$key];
        }

        $record = ExchangeRate::where('from_currency_code', $fromCurrency)
            ->where('to_currency_code',  $toCurrency)
            ->first()
        ;

        if ($record) {
            return $this->rateCache[$key] = $record->rate_value;
        }

        // Fallback to the inverse currency pair
        $record = ExchangeRate::where('to_currency_code', $fromCurrency)
            ->where('from_currency_code',  $toCurrency)
            ->first()
        ;

        if ($record) {
            return $this->rateCache[$key] = (1 / $record->rate_value);
        }

        throw new ApplicationException(__("There is no currency pair configured for :from/:to", [
            'from' => $fromCurrency,
            'to' => $toCurrency
        ]));
    }

    /**
     * requestAllRates
     */
    public function requestAllRates()
    {
        $rates = ExchangeRate::with('converter')->get();
        $defaultConverter = ExchangeConverter::getDefault();

        foreach ($rates as $rate) {
            $converter = $rate->converter ?: $defaultConverter;
            if (!$converter || !$converter->is_enabled) {
                continue;
            }

            $rateValue = $this->requestRate($rate, $converter);
            if ($rateValue === ExchangeConverter::NO_RATE_DATA) {
                continue;
            }

            $data = new ExchangeRateData;
            $data->rate_value = $rateValue;
            $data->valid_at = $data->freshTimestamp();
            $data->rate = $rate;
            $data->save();

            $rate->updateRateValue();
        }
    }

    /**
     * requestRate loads the latest rate from a currency converter
     */
    public function requestRate($rate, $converter)
    {
        $fromCurrency = trim(strtoupper($rate->from_currency_code));
        $toCurrency = trim(strtoupper($rate->to_currency_code));

        $rate = ExchangeConverter::NO_RATE_DATA;

        try {
            $rate = $converter->getExchangeRate($fromCurrency, $toCurrency);
        }
        catch (Exception $ex) {
            if ($fallback = $converter->fallback_converter) {
                try {
                    $rate = $fallback->getExchangeRate($fromCurrency, $toCurrency);
                }
                catch (Exception $ex) {
                }
            }
        }

        return $rate;
    }
}
