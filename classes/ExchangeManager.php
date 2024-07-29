<?php namespace Responsiv\Currency\Classes;

use App;
use System\Classes\PluginManager;

/**
 * ExchangeManager manages currency conversion
 *
 * To create an instance of this singleton:
 *
 *     ExchangeManager::instance();
 *
 * @package responsiv/currency
 * @author Responsiv Software
 */
class ExchangeManager
{
    use \Responsiv\Currency\Classes\ExchangeManager\HasCurrencyExchange;
    use \Responsiv\Currency\Classes\ExchangeManager\HasCurrencyConverters;

    /**
     * @var \System\Classes\PluginManager
     */
    protected $pluginManager;

    /**
     * __construct this class
     */
    public function __construct()
    {
        $this->pluginManager = PluginManager::instance();
    }

    /**
     * instance creates a new instance of this singleton
     */
    public static function instance(): static
    {
        return App::make('responsiv.currency.exchanges');
    }
}
