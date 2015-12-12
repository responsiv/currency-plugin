<?php namespace Responsiv\Currency\PaymentTypes;

use Responsiv\Currency\Classes\ConverterBase;

class XeServices extends ConverterBase
{
    const API_URL = '';

    /**
     * {@inheritDoc}
     */
    public function converterDetails()
    {
        return [
            'name'        => 'XE Services',
            'description' => 'Free conversion services via the XE Services gateway (xe.com).'
        ];
    }

}
