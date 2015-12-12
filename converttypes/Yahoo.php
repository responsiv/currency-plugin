<?php namespace Responsiv\Currency\PaymentTypes;

use Responsiv\Currency\Classes\ConverterBase;

class Yahoo extends ConverterBase
{
    const API_URL = '';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'Yahoo',
            'description' => 'Free currency exchange rate service provided by Yahoo (yahoo.com).'
        ];
    }

}
