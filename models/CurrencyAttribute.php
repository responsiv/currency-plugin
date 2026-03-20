<?php namespace Responsiv\Currency\Models;

use October\Rain\Database\Model;

/**
 * CurrencyAttribute stores per-currency attribute overrides for currencyable models
 *
 * @package responsiv\currency
 * @author Responsiv Software
 */
class CurrencyAttribute extends Model
{
    /**
     * @var string table associated with the model
     */
    public $table = 'responsiv_currency_attributes';

    /**
     * @var bool timestamps
     */
    public $timestamps = false;

    /**
     * @var array fillable fields
     */
    protected $fillable = ['currency_code', 'attribute', 'value'];

    /**
     * @var array morphTo
     */
    public $morphTo = [
        'model' => []
    ];
}