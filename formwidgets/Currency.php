<?php namespace Responsiv\Currency\FormWidgets;

use Site;
use Currency as CurrencyService;
use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Responsiv\Currency\Classes\CurrencyManager;

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
     * Prepares the list data
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
    }

    /**
     * getLoadValue
     */
    public function getLoadValue()
    {
        $value = parent::getLoadValue();
        if ($value === null) {
            return null;
        }

        return $this->getLoadCurrency()->fromBaseValue($value);
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

        return $this->getLoadCurrency()->toBaseValue($value);
    }

    /**
     * getLoadCurrency returns the currency object to used. If the model uses multisite,
     * then extract the primary currency from the site definition, otherwise use the
     * primary currency definition.
     */
    public function getLoadCurrency()
    {
        return CurrencyService::getForModel($this->model, $this->valueFrom);
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
    }
}
