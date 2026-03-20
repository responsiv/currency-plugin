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
     * getDefault returns the global default currency, ignoring site context.
     */
    public function getDefault()
    {
        return CurrencyModel::getDefault();
    }

    /**
     * getDefaultCode returns the global default currency code.
     */
    public function getDefaultCode()
    {
        return $this->getDefault()->code;
    }

    /**
     * getPrimary returns the base currency for the current site group.
     * Falls back to the global default when no group override is set.
     * This is the currency that prices are stored in.
     */
    public function getPrimary()
    {
        $site = Site::getSiteFromContext();

        if ($site && $site->group && $site->group->base_currency_id) {
            return $site->group->base_currency;
        }

        return CurrencyModel::getDefault();
    }

    /**
     * getPrimaryCode returns the base currency code for the current site group.
     */
    public function getPrimaryCode()
    {
        return $this->getPrimary()->code;
    }

    /**
     * getActive returns the active currency for the current site context.
     * Falls back to the global default when no site currency is set.
     */
    public function getActive()
    {
        $site = Site::getSiteFromContext();

        if ($site && $site->currency_id) {
            return $site->currency;
        }

        return CurrencyModel::getDefault();
    }

    /**
     * getActiveCode returns the active currency code for the current site context.
     */
    public function getActiveCode()
    {
        return $this->getActive()->code;
    }

    /**
     * getForModel returns the currency to use for a specific model or model attribute.
     * Always returns the primary currency since prices are stored in primary currency
     * and the Currencyable trait handles conversion via promotion.
     */
    public function getForModel($model, $attr = null)
    {
        return $this->getPrimary();
    }

    /**
     * getForModelCode returns the code for a specific model or model attribute
     */
    public function getForModelCode($model, $attr = null)
    {
        return $this->getForModel($model, $attr)->code;
    }

    /**
     * @deprecated use Currency model fromBaseValue method
     */
    public function fromBaseValue($value)
    {
        return $this->getDefault()->fromBaseValue($value);
    }

    /**
     * @deprecated use Currency model toBaseValue method
     */
    public function toBaseValue($value)
    {
        return $this->getDefault()->toBaseValue($value);
    }
}
