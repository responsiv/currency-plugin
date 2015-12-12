<?php namespace Responsiv\Currency\PaymentTypes;

use Responsiv\Currency\Classes\ConverterBase;

class EuropeanCentralBank extends ConverterBase
{
    const API_URL = '';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'European Central Bank',
            'description' => 'Free currency exchange rate feed provided by European Central Bank (www.ecb.int).'
        ];
    }

}
