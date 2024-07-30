<?php namespace Responsiv\Currency\Models;

use Model;

/**
 * ExchangeRateData Model
 */
class ExchangeRateData extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'responsiv_currency_exchange_rate_data';

    /**
     * @var array rules for validation
     */
    public $rules = [];
}
