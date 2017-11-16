<?php namespace Responsiv\Currency\Classes;

use Carbon\Carbon;
use Responsiv\Currency\Models\ExchangeRate;
use Responsiv\Currency\Models\ExchangeConverter;
use October\Rain\Support\Singleton;
use ApplicationException;
use Exception;

/**
 * Currency converter
 *
 * To create an instance of this singleton:
 *
 *   Converter::instance();
 *
 */
class Converter extends Singleton
{
    public static $rateCache = [];

    /**
     * {@inheritDoc}
     */
    protected static function getSingletonAccessor()
    {
        return 'responsiv.currency.converter';
    }

    /**
     * Returns the exchange rate for two currencies.
     * @param string $fromCurrency Currency code to convert from (eg: USD)
     * @param string $toCurrency Currency code to convert to (eg: AUD)
     * @return int
     */
    public function getRate($fromCurrency, $toCurrency)
    {
        $fromCurrency = trim(strtoupper($fromCurrency));
        $toCurrency = trim(strtoupper($toCurrency));

        /*
         * Look up in the cache
         */
        $key = $fromCurrency.'_'.$toCurrency;
        if (array_key_exists($key, self::$rateCache)) {
            return self::$rateCache[$key];
        }

        /*
         * Look up in the database cache
         */
        $converter = ExchangeConverter::getDefault();
        if (!$converter->class_name) {
            throw new ApplicationException('Currency rate converter is not configured.');
        }

        $interval = $converter->refresh_interval;
        $intervalDate = Carbon::now()->subHours($interval);

        $record = ExchangeRate::make()
            ->where('from_currency', $fromCurrency)
            ->where('to_currency',  $toCurrency)
            ->where('created_at', '>', $intervalDate)
        ;

        if ($record = $record->first()) {
            return self::$rateCache[$key] = $record->rate;
        }

        /*
         * Evaluate rate using a currency rate converter
         */
        try {
            $rate = $converter->getExchangeRate($fromCurrency, $toCurrency);

            $record = ExchangeRate::make();
            $record->from_currency = $fromCurrency;
            $record->to_currency = $toCurrency;
            $record->rate = $rate;
            $record->save();

            return self::$rateCache[$key] = $rate;
        }
        catch (Exception $ex) {
            /*
             * Load the most recent rate from the cache
             */
            $record = ExchangeRate::make()
                ->where('from_currency', $fromCurrency)
                ->where('to_currency',  $toCurrency)
                ->orderBy('created_at', 'desc')
            ;

            if (!$record = $record->first()) {
                throw $ex;
            }

            return self::$rateCache[$key] = $record->rate;
        }
    }

    /**
     * Convert a currency value from one currency to another.
     * @param number $value Specifies a value to convert
     * @param string $fromCurrency Currency code to convert from (eg: USD)
     * @param string $toCurrency Currency code to convert to (eg: AUD)
     * @param int $round Number of decimal digits to round the result to. Specify NULL to disable.
     * @return int
     */
    public function convert($value, $fromCurrency, $toCurrency, $round = 2)
    {
        $result = $value * $this->getRate($fromCurrency, $toCurrency);

        return $round !== null
            ? round($result, $round)
            : $result;
    }
}
