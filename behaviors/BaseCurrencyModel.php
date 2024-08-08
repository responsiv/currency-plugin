<?php namespace Responsiv\Currency\Behaviors;

use Responsiv\Currency\Models\Currency;
use System\Classes\ModelBehavior;

/**
 * BaseCurrencyModel extension adds Currency to a model
 *
 * Usage in the model class definition:
 *
 *     public $implement = [\Responsiv\Currency\Behaviors\BaseCurrencyModel::class];
 *
 */
class BaseCurrencyModel extends ModelBehavior
{
    /**
     * __construct
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $model->addFillable([
            'base_currency',
            'base_currency_id',
            'base_currency_code',
        ]);

        $model->belongsTo['base_currency'] = [
            Currency::class,
            'replicate' => false
        ];
    }

    /**
     * getBaseCurrencyOptions
     */
    public function getBaseCurrencyOptions()
    {
        return Currency::getNameList();
    }

    /**
     * setBaseCurrencyCodeAttribute sets the "base_currency" relation with the code specified, model lookup used.
     * @param string $code
     */
    public function setBaseCurrencyCodeAttribute($code)
    {
        if (!$currency = Currency::whereCode($code)->first()) {
            return;
        }

        $this->model->base_currency = $currency;
    }

    /**
     * getCurrencyCodeAttribute mutator for "base_currency_code" attribute.
     * @return string
     */
    public function getBaseCurrencyCodeAttribute()
    {
        return $this->model->base_currency ? $this->model->base_currency->code : null;
    }

    /**
     * setCurrencyIdAttribute ensures an integer value is set, otherwise nullable.
     */
    public function setBaseCurrencyIdAttribute($value)
    {
        $this->model->attributes['base_currency_id'] = $value ?: null;
    }
}
