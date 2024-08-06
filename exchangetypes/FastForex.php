<?php namespace Responsiv\Currency\ExchangeTypes;

use Date;
use Http;
use Cache;
use Responsiv\Currency\Classes\ExchangeBase;
use SystemException;
use Exception;

/**
 * FastForex exchange service
 */
class FastForex extends ExchangeBase
{
    const API_URL = 'https://api.fastforex.io/fetch-all?api_key=%s&from=%s';

    /**
     * {@inheritDoc}
     */
    public function driverDetails()
    {
        return [
            'name' => 'fastFOREX',
            'description' => 'Free currency exchange rate service provided by fastFOREX.io'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function initDriverHost($host)
    {
        $host->rules['api_key'] = 'required';

        if (!$host->exists) {
            $host->name = 'fastFOREX';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        $baseCode = trim(strtoupper($fromCurrency));
        $toRate = trim(strtoupper($toCurrency));

        $response = $this->requestRatesFromService($baseCode);
        if (!$response) {
            throw new SystemException('Error loading the FastForex currency exchange feed.');
        }

        if ($response['base'] !== $baseCode) {
            throw new SystemException('The FastForex currency exchange rate service returned the from base currency.');
        }

        $rates = $response['results'] ?? [];
        if (!$rates) {
            throw new SystemException('The FastForex currency exchange rate service returned invalid data.');
        }

        if (!$rate = array_get($rates, $toRate)) {
            throw new SystemException('The FastForex currency exchange rate service is missing the destination currency.');
        }

        return $rate;
    }

    /**
     * getServiceEndpointUrl
     */
    protected function getServiceEndpointUrl()
    {
        return self::API_URL;
    }

    /**
     * requestRatesFromService
     */
    protected function requestRatesFromService($baseCode)
    {
        $host = $this->getHostObject();
        $cacheKey = "responsiv.currency.exchange.{$host->id}-{$baseCode}";
        $expires = Date::now()->addHours($host->refresh_interval ?? 24);

        $response = null;
        try {
            $response = Cache::remember($cacheKey, $expires, function() use ($host, $baseCode) {
                return Http::get(sprintf(
                    $this->getServiceEndpointUrl(),
                    $host->api_key,
                    $baseCode
                ))->json();
            });
        }
        catch (Exception $ex) { }

        return $response;
    }
}
