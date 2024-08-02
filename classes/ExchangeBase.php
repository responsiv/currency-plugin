<?php namespace Responsiv\Currency\Classes;

use System\Classes\DriverBehavior;

/**
 * ExchangeBase represents a currency converter service.
 * All other converters must be derived from this class
 */
abstract class ExchangeBase extends DriverBehavior
{
    /**
     * driverDetails returns information about the converter type
     * Must return array:
     *
     * [
     *      'name' => 'XE.com',
     *      'description' => 'Conversion services provided by XE.'
     * ]
     *
     * @return array
     */
    public function driverDetails()
    {
        return [
            'name' => 'Unknown',
            'description' => 'Unknown conversion service.'
        ];
    }

    /**
     * getExchangeRate returns an exchange rate for two currencies.
     * @param string $fromCurrency Currency code to convert from (eg: USD)
     * @param string $toCurrency Currency code to convert to (eg: AUD)
     * @return float
     */
    abstract public function getExchangeRate($fromCurrency, $toCurrency);

    /**
     * getPartialPath render setup help
     * @return string
     */
    public function getPartialPath()
    {
        return $this->configPath;
    }

    /**
     * createRateModel creates an instance of the exchange rate model
     */
    protected function createRateModel()
    {
        return new \Responsiv\Currency\Models\ExchangeRate;
    }
}
