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
        $this->extendSiteDefinitionModel();

        // Site Definition

        $events->listen('backend.form.extendFields', [static::class, 'extendUserFormFields']);

        $events->listen('backend.list.extendColumns', [static::class, 'extendUserListColumns']);
    }

    /**
     * extendSiteDefinitionModel
     */
    public function extendSiteDefinitionModel()
    {
        ExtensionContainer::extendClass(\System\Models\SiteDefinition::class, static function($model) {
            $model->implementClassWith(\Responsiv\Currency\Behaviors\CurrencyModel::class);
            $model->implementClassWith(\Responsiv\Currency\Behaviors\BaseCurrencyModel::class);
        });

        ExtensionContainer::extendClass(\System\Classes\SiteManager::class, static function($model) {
            // @todo
        });
    }

    /**
     * extendUserFormFields
     */
    public function extendUserFormFields(\Backend\Widgets\Form $widget)
    {
        if ($widget->isNested || !$this->checkControllerMatchesSiteDefinition($widget)) {
            return;
        }

        $widget->addTabField('base_currency', 'Base Currency')
            ->tab("Site Definition")
            ->displayAs('dropdown')
            ->span('auto')
            ->comment(sprintf(__('Current default value: :value', ['value' => '<strong>%s</strong>']), \Responsiv\Currency\Models\Currency::getDefaultCode()))
            ->commentHtml()
            ->emptyOption('- '.__("Use Default").' -');

        $widget->addTabField('currency', 'Display Currency')
            ->tab("Site Definition")
            ->displayAs('dropdown')
            ->span('auto')
            ->comment("Currency used for display purposes.")
            ->emptyOption('- '.__("Use Default").' -');
    }

    /**
     * extendUserListColumns
     */
    public function extendUserListColumns(\Backend\Widgets\Lists $widget)
    {
        if (!$this->checkControllerMatchesSiteDefinition($widget)) {
            return;
        }

        $widget->defineColumn('base_currency', "Base Currency")->invisible()->relation('base_currency')->sqlSelect('name')->defaults('- '.__("Default").' -');
        $widget->defineColumn('currency', "Currency")->after('timezone')->relation('currency')->sqlSelect('name')->defaults('- '.__("Default").' -');
    }

    /**
     * checkControllerMatchesSiteDefinition
     */
    protected function checkControllerMatchesSiteDefinition($widget): bool
    {
        return $widget->getController() instanceof \System\Controllers\Sites &&
            $widget->getModel() instanceof \System\Models\SiteDefinition;
    }
}
