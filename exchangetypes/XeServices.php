<?php namespace Responsiv\Currency\ExchangeTypes;

use Responsiv\Currency\Classes\ExchangeBase;

class XeServices extends ExchangeBase
{
    const API_URL = 'https://xecdapi.xe.com/v1/convert_from.json/';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'XE Services',
            'description' => 'Paid conversion services via the XE Services gateway (xe.com).'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        return 1;
    }
}
