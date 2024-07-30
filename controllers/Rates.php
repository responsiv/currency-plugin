<?php namespace Responsiv\Currency\Controllers;

use Backend\Classes\SettingsController;
use Responsiv\Currency\Models\ExchangeRate;

/**
 * Rates Backend Controller
 */
class Rates extends SettingsController
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array relationConfig for extensions.
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['responsiv.currency.rates'];

    /**
     * @var string settingsItemCode determines the settings code
     */
    public $settingsItemCode = 'rates';

    /**
     * onGeneratePairs
     */
    public function onGeneratePairs()
    {
        ExchangeRate::generatePairs();

        return $this->listRefresh();
    }

    /**
     * onRequestRates
     */
    public function onRequestRates()
    {
        return $this->listRefresh();
    }
}
