<?php namespace Responsiv\Currency\Classes;

use Config;
use October\Rain\Extension\Container as ExtensionContainer;

/**
 * ExtendSystemModule
 */
class ExtendSystemModule
{
    /**
     * subscribe
     */
    public function subscribe($events)
    {
        $this->extendModels();

        $events->listen('backend.form.extendFields', [static::class, 'extendFormFields']);
        $events->listen('backend.list.extendColumns', [static::class, 'extendListColumns']);
    }

    /**
     * extendModels
     */
    public function extendModels()
    {
        // Site Definition: display currency
        ExtensionContainer::extendClass(\System\Models\SiteDefinition::class, static function($model) {
            $model->implementClassWith(\Responsiv\Currency\Behaviors\CurrencyModel::class);
        });

        // Site Group: base/stored currency
        ExtensionContainer::extendClass(\System\Models\SiteGroup::class, static function($model) {
            $model->implementClassWith(\Responsiv\Currency\Behaviors\BaseCurrencyModel::class);
        });
    }

    /**
     * extendFormFields
     */
    public function extendFormFields(\Backend\Widgets\Form $widget)
    {
        if ($widget->isNested) {
            return;
        }

        // Site Definition: currency field
        if ($this->checkControllerModel($widget, \System\Controllers\Sites::class, \System\Models\SiteDefinition::class)) {
            $widget->addTabField('currency', 'Currency')
                ->tab("Site Definition")
                ->displayAs('dropdown')
                ->span('auto')
                ->comment(sprintf(__('Current default value: :value', ['value' => '<strong>%s</strong>']), \Responsiv\Currency\Models\Currency::getDefaultCode()))
                ->commentHtml()
                ->emptyOption('- '.__("Use Default").' -');
        }

        // Site Group: base currency field
        if ($this->checkControllerModel($widget, \System\Controllers\SiteGroups::class, \System\Models\SiteGroup::class)) {
            $widget->addTabField('base_currency', 'Base Currency')
                ->displayAs('dropdown')
                ->span('auto')
                ->comment(sprintf(__('Currency used to store prices. Default: :value', ['value' => '<strong>%s</strong>']), \Responsiv\Currency\Models\Currency::getDefaultCode()))
                ->commentHtml()
                ->emptyOption('- '.__("Use Default").' -');
        }
    }

    /**
     * extendListColumns
     */
    public function extendListColumns(\Backend\Widgets\Lists $widget)
    {
        // Site Definition list
        if ($this->checkControllerModel($widget, \System\Controllers\Sites::class, \System\Models\SiteDefinition::class)) {
            $widget->defineColumn('currency', "Currency")
                ->after('timezone')
                ->relation('currency')
                ->sqlSelect('name')
                ->defaults('- '.__("Default").' -');
        }
    }

    /**
     * checkControllerModel
     */
    protected function checkControllerModel($widget, string $controller, string $model): bool
    {
        return $widget->getController() instanceof $controller &&
            $widget->getModel() instanceof $model;
    }
}
