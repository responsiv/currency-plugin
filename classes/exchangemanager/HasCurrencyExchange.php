<?php namespace Responsiv\Currency\Classes\ExchangeManager;

use Carbon\Carbon;
use Responsiv\Currency\Models\ExchangeRate;
use Responsiv\Currency\Models\ExchangeConverter;
use Responsiv\Currency\Models\Currency as CurrencyModel;
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
     * loadRate loads the latest rate from a currency converter
     * @todo this should pull the rate in and save it to the pair
     */
    public function loadRate(string $fromCurrency, string $toCurrency): float
    {
        $fromCurrency = trim(strtoupper($fromCurrency));
        $toCurrency = trim(strtoupper($toCurrency));

        // Look up in the cache
        $key = $fromCurrency.'_'.$toCurrency;
        if (array_key_exists($key, $this->rateCache)) {
            return $this->rateCache[$key];
        }

        // Look up in the database cache
        // @todo this should use the ExchangeRate model
        $converter = ExchangeConverter::___getDefault();
        if (!$converter->class_name) {
            throw new ApplicationException('Currency rate converter is not configured.');
        }

        $interval = $converter->refresh_interval;
        $intervalDate = Carbon::now()->subHours($interval);

        $record = ExchangeRate::where('from_currency', $fromCurrency)
            ->where('to_currency',  $toCurrency)
            ->where('created_at', '>', $intervalDate)
        ;

        if ($record = $record->first()) {
            return $this->rateCache[$key] = $record->rate_value;
        }

        // Evaluate rate using a currency rate converter
        try {
            $rate = $converter->getExchangeRate($fromCurrency, $toCurrency);

            $record = new ExchangeRate;
            $record->from_currency = $fromCurrency;
            $record->to_currency = $toCurrency;
            $record->rate_value = $rate;
            $record->save();

            return $this->rateCache[$key] = $rate;
        }
        catch (Exception $ex) {
            // Load the most recent rate from the cache
            $record = ExchangeRate::where('from_currency', $fromCurrency)
                ->where('to_currency',  $toCurrency)
                ->orderBy('created_at', 'desc')
            ;

            if (!$record = $record->first()) {
                throw $ex;
            }

            return $this->rateCache[$key] = $record->rate_value;
        }
    }

    /**
     * convert a currency value from one currency to another. Round number of decimal digits
     * to round the result to. Specify NULL to disable.
     */
    public function convert(float $value, string $toCurrency, string $fromCurrency = null, int $round = null): string
    {
        if (!$fromCurrency) {
            $fromCurrency = CurrencyModel::getPrimaryCode();
        }

        $result = $value * $this->getRate($fromCurrency, $toCurrency);

        return $round !== null
            ? round($result, $round)
            : $result;
    }
}
