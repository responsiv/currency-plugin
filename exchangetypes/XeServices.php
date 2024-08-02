<?php namespace Responsiv\Currency\ExchangeTypes;

use Responsiv\Currency\Classes\ExchangeBase;

/**
 * XeServices
 */
class XeServices extends ExchangeBase
{
    const API_URL = 'https://xecdapi.xe.com/v1/convert_from.json/';

    /**
     * {@inheritDoc}
     */
    public function driverDetails()
    {
        return [
            'name' => 'XE Services',
            'description' => 'Paid conversion services via the XE Services gateway (xe.com).'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function initDriverHost($host)
    {
        $host->rules['api_username'] = 'required';
        $host->rules['api_password'] = 'required';

        if (!$host->exists) {
            $host->name = 'XE Services';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        return 1;
    }
}
