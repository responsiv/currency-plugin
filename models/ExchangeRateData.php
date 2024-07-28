<?php namespace Responsiv\Currency\Models;

use Model;

/**
 * ExchangePair Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class ExchangePair extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'responsiv_currency_exchange_pairs';

    /**
     * @var array rules for validation
     */
    public $rules = [];
}
