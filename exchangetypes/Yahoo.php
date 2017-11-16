<?php namespace Responsiv\Currency\ExchangeTypes;

use Http;
use Responsiv\Currency\Classes\ExchangeBase;
use SystemException;
use Exception;

class Yahoo extends ExchangeBase
{
    const API_URL = 'http://finance.yahoo.com/d/quotes.csv?f=l1d1t1&s=%s%s=X';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'Yahoo',
            'description' => 'This service has been discontinued.'
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
            $response = Http::get(sprintf(self::API_URL, $fromCurrency, $toCurrency));
        }
        catch (Exception $ex) { }

        if (!strlen($response) || $response->code != 200) {
            throw new SystemException('Error loading the Yahoo currency exchange feed.');
        }

        $data = explode(',', $response);
        if (count($data) < 2) {
            throw new SystemException('The Yahoo currency exchange rate service returned invalid data.');
        }

        return $data[0];
    }

    /**
     * {@inheritDoc}
     */
    public function defineFormFields()
    {
        return [];
    }
}
