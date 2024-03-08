<?php namespace Responsiv\Currency\Classes;

use System\Classes\DriverBehavior;

/**
 * ExchangeBase represents a currency converter service.
 * All other converters must be derived from this class
 */
abstract class ExchangeBase extends DriverBehavior
{
    /**
     * @var string rateModel
     */
    protected $rateModel = \Responsiv\Currency\Models\ExchangeRate::class;

    /**
     * converterDetails returns information about the converter type
     * Must return array:
     *
     * [
     *      'name' => 'XE.com',
     *      'description' => 'Conversion services provided by XE.'
     * ]
     *
     * @return array
     */
    public function converterDetails()
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
     * createRateModel creates an instance of the exchange rate model
     */
    protected function createRateModel()
    {
        $class = '\\'.ltrim($this->rateModel, '\\');
        $model = new $class();
        return $model;
    }
}
