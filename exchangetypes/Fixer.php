<?php namespace Responsiv\Currency\ExchangeTypes;

use Date;
use Http;
use Cache;
use Responsiv\Currency\Classes\ExchangeBase;
use SystemException;
use Exception;

/**
 * Fixer exchange service
 */
class Fixer extends ExchangeBase
{
    const API_URL = 'https://data.fixer.io/api/latest?access_key=%s&base=%s';

    /**
     * {@inheritDoc}
     */
    public function driverDetails()
    {
        return [
            'name' => 'Fixer',
            'description' => 'Free currency exchange rate service provided by Fixer.io'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function initDriverHost($host)
    {
        $host->rules['access_key'] = 'required';

        if (!$host->exists) {
            $host->name = 'Fixer';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        $baseCode = trim(strtoupper($fromCurrency));
        $toRate = trim(strtoupper($toCurrency));

        $response = $this->requestRatesFromFixer($baseCode);
        if (!$response) {
            throw new SystemException('Error loading the Fixer currency exchange feed.');
        }

        $rates = $response->json('rates');
        if (!strlen($rates)) {
            throw new SystemException('The Fixer currency exchange rate service returned invalid data.');
        }

        if (!$rate = array_get($rates, $toRate)) {
            throw new SystemException('The Fixer currency exchange rate service is missing the destination currency.');
        }

        return $rate;
    }

    /**
     * requestRatesFromFixer
     */
    protected function requestRatesFromFixer($baseCode)
    {
        $host = $this->getHostObject();

        $cacheKey = "responsiv.currency.exchange.{$host->id}-{$baseCode}";
        $expires = Date::now()->addHours($host->refresh_interval ?? 24);

        $response = null;
        try {
            $response = Cache::remember($cacheKey, $expires, function() use ($host, $baseCode) {
                return Http::get(sprintf(
                    self::API_URL,
                    $host->access_key,
                    $baseCode
                ));
            });
        }
        catch (Exception $ex) { }

        return $response;
    }
}
