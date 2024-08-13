<?php namespace Responsiv\Currency\Classes;

use App;
use Site;
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
            $fromCurrency = $this->getDefaultCode();
        }

        return ExchangeManager::instance()->convert($value, $toCurrency, $fromCurrency, null);
    }

    /**
     * getPrimary returns the default currency for source values, regardless of the site context.
     */
    public function getDefault()
    {
        return CurrencyModel::getDefault();
    }

    /**
     * getDefaultCode returns the primary currency code for source values.
     */
    public function getDefaultCode()
    {
        return $this->getDefault()->currency_code;
    }

    /**
     * getPrimary returns the primary currency for source values.
     */
    public function getPrimary()
    {
        $site = Site::getSiteFromContext();

        if ($site->base_currency_id) {
            return $site->base_currency;
        }

        return CurrencyModel::getDefault();
    }

    /**
     * getPrimaryCode returns the primary currency code for source values.
     */
    public function getPrimaryCode()
    {
        return $this->getPrimary()->currency_code;
    }

    /**
     * getActive returns the active currency for display purposes.
     */
    public function getActive()
    {
        $site = Site::getSiteFromContext();

        if ($site->currency_id) {
            return $site->currency;
        }

        return CurrencyModel::getDefault();
    }

    /**
     * getActiveCode returns the active currency code for display purposes.
     */
    public function getActiveCode()
    {
        return $this->getActive()->currency_code;
    }

    /**
     * fromBaseValue converts 100 to 1.00
     */
    public function fromBaseValue($value)
    {
        $currencyObj = $this->getPrimary();

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
        $currencyObj = $this->getPrimary();

        $value = floatval(str_replace($currencyObj->decimal_point, '.', $value));

        return $currencyObj->toBaseValue($value);
    }
}
