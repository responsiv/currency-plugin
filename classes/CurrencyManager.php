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
        return $this->getDefault()->code;
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
        return $this->getPrimary()->code;
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

        if ($site->base_currency_id) {
            return $site->base_currency;
        }

        return CurrencyModel::getDefault();
    }

    /**
     * getActiveCode returns the active currency code for display purposes.
     */
    public function getActiveCode()
    {
        return $this->getActive()->code;
    }

    /**
     * getForModel returns the current to use for a specific model or model attribute
     */
    public function getForModel($model, $attr = null)
    {
        if (Site::isModelMultisite($model, $attr)) {
            return $this->getPrimary();
        }

        return $this->getDefault();
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
