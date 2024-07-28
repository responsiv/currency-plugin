<?php namespace Responsiv\Currency;

use Backend;
use Currency;
use System\Classes\PluginBase;

/**
 * Plugin Information File
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
            'name' => 'Currency',
            'description' => 'Tools for currency display and conversion',
            'author' => 'Responsiv Software',
            'icon' => 'icon-usd',
            'homepage' => 'https://github.com/responsiv/currency-plugin'
        ];
    }

    /**
     * register the service provider.
     */
    public function register()
    {
        $this->registerSingletons();
    }

    /**
     * boot the module events.
     */
    public function boot()
    {
    }

    /**
     * registerSingletons
     */
    protected function registerSingletons()
    {
        $this->app->singleton('currencies', \Responsiv\Currency\Classes\CurrencyManager::class);
        $this->app->singleton('responsiv.currency.exchanges', \Responsiv\Currency\Classes\ExchangeManager::class);
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
                'label' => "Currencies",
                'description' => "Create and configure available currencies.",
                'icon' => 'icon-eur',
                'url' => Backend::url('responsiv/currency/currencies'),
                'category' => "Currency",
                'order' => 500,
                'permissions' => ['responsiv.currency.access_settings']
            ],
            'rates' => [
                'label' => "Currency Rates",
                'description' => "Select and manage the currency converter to use.",
                'icon' => 'icon-calculator',
                'url' => Backend::url('responsiv/currency/rates'),
                'category' => "Currency",
                'order' => 510,
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
                'currency' => [Currency::class, 'format']
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
                return Currency::format($value, ['format' => $column->format]);
            }
        ];
    }

    /**
     * Registers any form widgets implemented in this plugin.
     */
    public function registerFormWidgets()
    {
        return [
            \Responsiv\Currency\FormWidgets\Currency::class => 'currency',
        ];
    }

    /**
     * registerCurrencyConverters registers any currency converters implemented in this plugin.
     *
     * The converters must be returned in the following format:
     *
     * [DriverName1::class => 'alias'],
     * [DriverName2::class => 'anotherAlias']
     */
    public function registerCurrencyConverters()
    {
        return [
            \Responsiv\Currency\ExchangeTypes\XeServices::class => 'xe',
            // \Responsiv\Currency\ExchangeTypes\EuropeanCentralBank::class => 'ecb',
            // \Responsiv\Currency\ExchangeTypes\CoinMarketCap::class => 'coinmarketcap',
            // \Responsiv\Currency\ExchangeTypes\Fixer::class => 'fixer',
            // \Responsiv\Currency\ExchangeTypes\Yahoo::class => 'yahoo', // Discontinued
            // \Responsiv\Currency\ExchangeTypes\XeServices::class => 'xe',
            // \Responsiv\Currency\ExchangeTypes\Coinmill::class => 'coinmill',
        ];
    }
}
