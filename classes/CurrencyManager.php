<?php namespace Responsiv\Currency\Classes;

use App;
use Responsiv\Currency\Models\Currency as CurrencyModel;
use System\Classes\PluginManager;

/**
 * CurrencyManager class manages currencies
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
class CurrencyManager
{
    use \Responsiv\Currency\Classes\CurrencyManager\HasCurrencyFormat;

    /**
     * @var PluginManager pluginManager
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
        return App::make('currencies');
    }

    /**
     * convert
     */
    public function convert($value, $toCurrency, $fromCurrency = null)
    {
        if (!$fromCurrency) {
            $fromCurrency = $this->primaryCode();
        }

        return ExchangeManager::instance()->convert($value, $toCurrency, $fromCurrency, null);
    }

    /**
     * primaryCode
     */
    public function primaryCode()
    {
        return CurrencyModel::getPrimary()->currency_code;
    }

    /**
     * fromBaseValue converts 100 to 1.00
     */
    public function fromBaseValue($value)
    {
        $currencyObj = CurrencyModel::getPrimary();

        $value = $currencyObj->fromBaseValue($value);

        return number_format(
            $value,
            $currencyObj->decimal_scale,
            $currencyObj->decimal_point,
            ""
        );
    }

    /**
     * toBaseValue converts 1.00 to 100
     */
    public function toBaseValue($value)
    {
        $currencyObj = CurrencyModel::getPrimary();

        $value = floatval(str_replace($currencyObj->decimal_point, '.', $value));

        return $currencyObj->toBaseValue($value);
    }
}
