<?php namespace Responsiv\Currency\Controllers;

use Responsiv\Currency\Models\ExchangeConverter;
use Backend\Classes\SettingsController;
use Exception;

/**
 * Converters Backend Controller
 */
class Converters extends SettingsController
{
    /**
     * @var array implement behaviors in this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
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
     * @var array required permissions
     */
    public $requiredPermissions = ['responsiv.currency.rates'];

    /**
     * @var string settingsItemCode determines the settings code
     */
    public $settingsItemCode = 'converters';
}
