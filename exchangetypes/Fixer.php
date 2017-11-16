<?php namespace Responsiv\Currency\ExchangeTypes;

use Http;
use Responsiv\Currency\Classes\ExchangeBase;
use SystemException;
use Exception;

class Fixer extends ExchangeBase
{
    const API_URL = 'https://api.fixer.io/latest?symbols=%s&base=%s';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'Fixer',
            'description' => 'Free currency exchange rate service provided by Fixer.io'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        $fromCurrency = trim(strtoupper($fromCurrency));
        $toCurrency = trim(strtoupper($toCurrency));

        $response = null;
        try {
            $response = Http::get(sprintf(self::API_URL, $toCurrency, $fromCurrency));
            $body = (string) $response;
        }
        catch (Exception $ex) { }

        if (!strlen($body)) {
            throw new SystemException('Error loading the Fixer currency exchange feed.');
        }

        $result = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SystemException('The Fixer currency exchange rate service returned invalid data.');
        }

        $rates = array_get($result, 'rates', []);

        if (!$rate = array_get($rates, $toCurrency)) {
            throw new SystemException('The Fixer currency exchange rate service is missing the destination currency.');
        }

        return $rate;
    }

    /**
     * {@inheritDoc}
     */
    public function defineFormFields()
    {
        return [];
    }
}
