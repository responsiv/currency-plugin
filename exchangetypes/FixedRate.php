<?php namespace Responsiv\Currency\ExchangeTypes;

use Responsiv\Currency\Classes\ExchangeBase;
use Responsiv\Currency\Models\ExchangeConverter;
use October\Contracts\Element\FormElement;

/**
 * FixedRate
 */
class FixedRate extends ExchangeBase
{
    /**
     * {@inheritDoc}
     */
    public function driverDetails()
    {
        return [
            'name' => 'Fixed Rate',
            'description' => 'This converter will not import any rates and should be used when manually specifying exchange rates.',
            'isFixed' => true
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function initDriverHost($host)
    {
        if (!$host->exists) {
            $host->name = 'Fixed Rate';
        }
    }

    /**
     * defineFormFields is an method for internal use to define fields used by this driver.
     * Override this method to define form fields.
     */
    public function defineFormFields(FormElement $form, $context = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        return ExchangeConverter::NO_RATE_DATA;
    }
}
