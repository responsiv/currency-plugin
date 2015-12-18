<?php namespace Responsiv\Currency\Facades;

use October\Rain\Support\Facade;

class Currency extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'responsiv.currency.helper';
    }

    protected static function getFacadeInstance()
    {
        return new \Responsiv\Currency\Helpers\Currency;
    }
}
