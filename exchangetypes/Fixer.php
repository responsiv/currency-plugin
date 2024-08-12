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
    const API_URL = '//data.fixer.io/api/latest?access_key=%s&base=%s';

    /**
     * {@inheritDoc}
     */
    public function driverDetails()
    {
        return [
            'name' => 'Fixer',
            'description' => 'Currency exchange rate service provided by Fixer.io'
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
            $host->use_secure_endpoint = false;
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
            throw new SystemException('Error loading the Fixer currency exchange feed.');
        }

        $rates = $response['rates'] ?? [];
        if (!$rates) {
            throw new SystemException('The Fixer currency exchange rate service returned invalid data.');
        }

        if (!$rate = array_get($rates, $toRate)) {
            throw new SystemException('The Fixer currency exchange rate service is missing the destination currency.');
        }

        return $rate;
    }

    /**
     * getServiceEndpointUrl
     */
    protected function getServiceEndpointUrl()
    {
        $host = $this->getHostObject();
        $url = self::API_URL;

        if ($host->use_secure_endpoint) {
            return "https:{$url}";
        }

        return "http:{$url}";
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
                    $host->access_key,
                    $baseCode
                ))->json();
            });
        }
        catch (Exception $ex) { }

        return $response;
    }
}
