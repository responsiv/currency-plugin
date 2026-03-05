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
     * getBaseCurrencyCodeAttribute resolves the base currency code by walking
     * up to the site group, falling back to the global default. This allows
     * Twig templates to use `this.site.base_currency_code` transparently.
     * @return string
     */
    public function getBaseCurrencyCodeAttribute()
    {
        $model = $this->model;

        if ($model->group && $model->group->base_currency_id) {
            return $model->group->base_currency->code;
        }

        return Currency::getDefaultCode();
    }

    /**
     * getHardCurrencyCodeAttribute will always return a currency code no matter
     * what, falling back to the base currency. Mirrors the hard_locale pattern.
     */
    public function getHardCurrencyCodeAttribute()
    {
        return $this->model->currency
            ? $this->model->currency->code
            : $this->getBaseCurrencyCodeAttribute();
    }

    /**
     * setCurrencyIdAttribute ensures an integer value is set, otherwise nullable.
     */
    public function setCurrencyIdAttribute($value)
    {
        $this->model->attributes['currency_id'] = $value ?: null;
    }
}
