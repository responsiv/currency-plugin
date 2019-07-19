<?php namespace Responsiv\Currency;

use Backend;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;
use Responsiv\Currency\Facades\Currency as CurrencyFacade;

/**
 * Currency Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Currency',
            'description' => 'Tools for currency display and conversion',
            'author'      => 'Responsiv Internet',
            'icon'        => 'icon-usd',
            'homepage'    => 'https://github.com/responsiv/currency-plugin'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('Currency', 'Responsiv\Currency\Facades\Currency');
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents() { }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'responsiv.currency.access_settings' => [
                'tab'   => 'Currency',
                'label' => 'Manage currency settings'
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'currencies' => [
                'label'       => 'responsiv.currency::lang.currency.currencies',
                'description' => 'responsiv.currency::lang.currency.description',
                'icon'        => 'icon-eur',
                'url'         => Backend::url('responsiv/currency/currencies'),
                'category'    => 'responsiv.currency::lang.plugin.tab',
                'order'       => 500,
                'permissions' => ['responsiv.currency.access_settings']
            ],
            'converters' => [
                'label'       => 'responsiv.currency::lang.converter.title',
                'description' => 'responsiv.currency::lang.converter.description',
                'icon'        => 'icon-calculator',
                'url'         => Backend::url('responsiv/currency/converters'),
                'category'    => 'responsiv.currency::lang.plugin.tab',
                'order'       => 510,
                'permissions' => ['responsiv.currency.access_settings']
            ]
        ];
    }

    /**
     * Register new Twig variables
     * @return array
     */
    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'currency' => [CurrencyFacade::class, 'format']
            ]
        ];
    }

    /**
     * Register new list column types
     * @return array
     */
    public function registerListColumnTypes()
    {
        return [
            'currency' => function($value, $column) {
                return CurrencyFacade::format($value, ['format' => $column->format]);
            }
        ];
    }

    /**
     * Registers any form widgets implemented in this plugin.
     */
    public function registerFormWidgets()
    {
        return [
            'Responsiv\Currency\FormWidgets\Currency' => [
                'label' => 'Currency',
                'code'  => 'currency'
            ]
        ];
    }

    /**
     * Registers any currency converters implemented in this plugin.
     * The converters must be returned in the following format:
     * ['className1' => 'alias'],
     * ['className2' => 'anotherAlias']
     */
    public function registerCurrencyConverters()
    {
        return [
            'Responsiv\Currency\ExchangeTypes\EuropeanCentralBank' => 'ecb',
            'Responsiv\Currency\ExchangeTypes\CoinMarketCap'       => 'coinmarketcap',
            'Responsiv\Currency\ExchangeTypes\Fixer'               => 'fixer',
            // 'Responsiv\Currency\ExchangeTypes\Yahoo'               => 'yahoo', // Discontinued
            // 'Responsiv\Currency\ExchangeTypes\XeServices'          => 'xe',
            // 'Responsiv\Currency\ExchangeTypes\Coinmill'            => 'coinmill',
        ];
    }
}
