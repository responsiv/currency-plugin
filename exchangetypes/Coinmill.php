<?php namespace Responsiv\Currency\ExchangeTypes;

use Responsiv\Currency\Classes\ExchangeBase;

class Coinmill extends ExchangeBase
{
    const API_URL = 'http://coinmill.com/rss/AUD_USD.xml';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'Coinmill',
            'description' => 'Free conversion services via the Coinmill gateway (coinmill.com).'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    public function defineFormFields()
    {
        return [];
    }
}
