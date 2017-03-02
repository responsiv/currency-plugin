<?php namespace Responsiv\Currency\FormWidgets;

use Html;
use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Responsiv\Currency\Models\Currency as CurrencyModel;
use RainLab\Location\Models\Setting;

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
        $currencyObj = CurrencyModel::getPrimary();
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['field'] = $this->formField;
        $this->vars['format'] = $this->format;
        $this->vars['symbol'] = $currencyObj ? $currencyObj->currency_symbol : '$';
        $this->vars['symbolBefore'] = $currencyObj ? $currencyObj->place_symbol_before : true;
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

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
    }
}
