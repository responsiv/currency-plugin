<?php namespace Responsiv\Currency\Behaviors;

use Responsiv\Currency\Models\Currency;
use System\Classes\ModelBehavior;

/**
 * CurrencyModel extension adds Currency to a model
 *
 * Usage in the model class definition:
 *
 *     public $implement = [\Responsiv\Currency\Behaviors\CurrencyModel::class];
 *
 */
class CurrencyModel extends ModelBehavior
{
    /**
     * __construct
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $model->addFillable([
            'currency',
            'currency_id',
            'currency_code',
        ]);

        $model->belongsTo['currency'] = [
            Currency::class,
            'replicate' => false
        ];
    }

    /**
     * getCurrencyOptions
     */
    public function getCurrencyOptions()
    {
        return Currency::getNameList();
    }

    /**
     * setCurrencyCodeAttribute sets the "currency" relation with the code specified, model lookup used.
     * @param string $code
     */
    public function setCurrencyCodeAttribute($code)
    {
        if (!$currency = Currency::whereCode($code)->first()) {
            return;
        }

        $this->model->currency = $currency;
    }

    /**
     * getCurrencyCodeAttribute mutator for "currency_code" attribute.
     * @return string
     */
    public function getCurrencyCodeAttribute()
    {
        return $this->model->currency ? $this->model->currency->code : null;
    }

    /**
     * getBaseCurrencyAttribute resolves the currency prices are stored in, from the site group or the global default.
     */
    public function getBaseCurrencyAttribute()
    {
        $group = $this->model->group;

        if ($group && $group->base_currency_id) {
            return $group->base_currency;
        }

        return Currency::getDefault();
    }

    /**
     * getBaseCurrencyCodeAttribute returns the code of the currency prices are stored in.
     */
    public function getBaseCurrencyCodeAttribute()
    {
        return $this->getBaseCurrencyAttribute()?->code;
    }

    /**
     * getHardCurrencyAttribute always returns a currency, falling back to the base currency like the hard_locale pattern.
     */
    public function getHardCurrencyAttribute()
    {
        return $this->model->currency ?: $this->getBaseCurrencyAttribute();
    }

    /**
     * getHardCurrencyCodeAttribute always returns a currency code, falling back to the base currency.
     */
    public function getHardCurrencyCodeAttribute()
    {
        return $this->getHardCurrencyAttribute()?->code;
    }

    /**
     * setCurrencyIdAttribute ensures an integer value is set, otherwise nullable.
     */
    public function setCurrencyIdAttribute($value)
    {
        $this->model->attributes['currency_id'] = $value ?: null;
    }
}
