<?php namespace Responsiv\Currency\FormWidgets;

use Site;
use Currency as CurrencyService;
use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Responsiv\Currency\Classes\CurrencyManager;
use Responsiv\Currency\Models\Currency as CurrencyModel;

/**
 * Currency input
 */
class Currency extends FormWidgetBase
{
    //
    // Configurable properties
    //

    /**
     * @var string Currency format to display (long|short)
     */
    public $format = null;

    /**
     * @var string currencyFrom reads the currency code from this model attribute
     */
    public $currencyFrom = null;

    //
    // Object properties
    //

    /**
     * {@inheritDoc}
     */
    public $defaultAlias = 'currency';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->fillFromConfig([
            'format',
            'currencyFrom',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('currency');
    }

    /**
     * prepareVars for the widget partial
     */
    public function prepareVars()
    {
        $currencyObj = $this->getLoadCurrency();
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['field'] = $this->formField;
        $this->vars['format'] = $this->format;
        $this->vars['currencyCode'] = $currencyObj ? $currencyObj->code : '';
        $this->vars['symbol'] = $currencyObj ? $currencyObj->currency_symbol : '$';
        $this->vars['symbolBefore'] = $currencyObj ? $currencyObj->place_symbol_before : true;

        // Override state for non-default currency sites
        $this->vars['isCurrencyOverridable'] = $this->isCurrencyOverridable();
        $this->vars['hasCurrencyOverride'] = $this->hasCurrencyOverride();
        $this->vars['isCurrencyReadOnly'] = $this->isCurrencyReadOnly();
        $this->vars['convertedValue'] = $this->getConvertedValue();
    }

    /**
     * getLoadValue returns the value for display. Shows the active site's
     * currency value — base price on the default site, or the override /
     * auto-converted value on non-default sites.
     */
    public function getLoadValue()
    {
        // Non-currencyable model: raw value in primary currency
        if (!$this->model->methodExists('getCurrencyableBaseValue')) {
            $value = parent::getLoadValue();
            return $value === null ? null : $this->getLoadCurrency()->toFloatValue($value);
        }

        // Currencyable model on default site: show base value
        if (!$this->model->shouldConvertCurrency()) {
            $value = $this->model->getCurrencyableBaseValue($this->valueFrom);
            return $value === null ? null : $this->getLoadCurrency()->toFloatValue($value);
        }

        // Currencyable model on non-default site: show override or converted value
        $value = $this->model->getCurrencyOverride(
            $this->valueFrom,
            $this->model->getCurrencyableContext()
        );

        return $value === null ? null : $this->getLoadCurrency()->toFloatValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        if ($this->formField->disabled || $this->formField->hidden) {
            return FormField::NO_SAVE_DATA;
        }

        if (!strlen($value)) {
            return null;
        }

        return $this->getLoadCurrency()->fromFloatValue($value);
    }

    /**
     * getLoadCurrency returns the currency object to use. For currencyable
     * attributes, always returns the active site currency. For non-currencyable
     * models, returns the primary currency.
     */
    public function getLoadCurrency()
    {
        // Per-record currency override
        if ($this->currencyFrom) {
            $code = data_get($this->model, $this->currencyFrom);
            if ($code) {
                return CurrencyModel::findByCode($code) ?: CurrencyService::getPrimary();
            }
        }

        if (
            $this->model->methodExists('isCurrencyableAttribute') &&
            $this->model->isCurrencyableAttribute($this->valueFrom)
        ) {
            return CurrencyService::getActive();
        }

        return CurrencyService::getForModel($this->model, $this->valueFrom);
    }

    //
    // Currency override
    //

    /**
     * isCurrencyOverridable returns true when the field supports override
     * (currencyable model on a non-default currency site)
     */
    protected function isCurrencyOverridable(): bool
    {
        return $this->model->methodExists('shouldConvertCurrency')
            && $this->model->shouldConvertCurrency()
            && $this->model->isCurrencyableAttribute($this->valueFrom);
    }

    /**
     * hasCurrencyOverride returns true when an explicit override exists
     */
    protected function hasCurrencyOverride(): bool
    {
        if (!$this->isCurrencyOverridable()) {
            return false;
        }

        return $this->model->hasCurrencyOverride($this->valueFrom);
    }

    /**
     * isCurrencyReadOnly returns true when the field should be read-only
     * (non-default site, no override set — showing auto-converted value)
     */
    protected function isCurrencyReadOnly(): bool
    {
        return $this->isCurrencyOverridable() && !$this->hasCurrencyOverride();
    }

    /**
     * getConvertedValue returns the exchange-rate converted value (ignoring
     * any override) so the JS control can restore it when clearing
     */
    protected function getConvertedValue()
    {
        if (!$this->isCurrencyOverridable()) {
            return null;
        }

        $baseValue = $this->model->getCurrencyableBaseValue($this->valueFrom);
        if ($baseValue === null) {
            return null;
        }

        $converted = CurrencyService::convert(
            $baseValue,
            $this->model->getCurrencyableContext(),
            $this->model->getCurrencyableDefault()
        );

        return $this->getLoadCurrency()->toFloatValue((int) $converted);
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addJs('js/currencyfield.js');
    }
}
