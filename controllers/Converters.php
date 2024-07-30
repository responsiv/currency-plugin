<?php namespace Responsiv\Currency\Controllers;

use File;
use Responsiv\Currency\Classes\ExchangeManager;
use Backend\Classes\SettingsController;
use ApplicationException;
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

    /**
     * @var string driverAlias
     */
    public $driverAlias;

    /**
     * index_onLoadAddPopup
     */
    protected function index_onLoadAddPopup()
    {
        try {
            $converters = ExchangeManager::instance()->listConverters();
            $converters->sortBy('name');
            $this->vars['converters'] = $converters;
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        return $this->makePartial('add_converter_form');
    }

    /**
     * create
     */
    public function create($driverAlias = null)
    {
        try {
            if (!$driverAlias) {
                throw new ApplicationException('Missing a gateway code');
            }

            $this->driverAlias = $driverAlias;
            $this->asExtension('FormController')->create();
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }
    }

    /**
     * formExtendModel
     */
    public function formExtendModel($model)
    {
        if (!$model->exists) {
            $model->applyDriverClass($this->getDriverClass());
        }

        return $model;
    }

    /**
     * formExtendFields
     */
    public function formExtendFields($widget)
    {
        $model = $widget->getModel();

        $widget->inActiveTabSection('primary', function() use ($widget, $model) {
            $model->defineDriverFormFields($widget);
        });

        // Add the set up help partial
        $setupPartial = $model->getPartialPath().'/_setup_help.php';
        if (File::exists($setupPartial)) {
            $widget->addTabField('setup_help', [
                'type' => 'partial',
                'tab' => "Help",
                'path' => $setupPartial
            ]);
        }
    }

    /**
     * getDriverClass
     */
    protected function getDriverClass()
    {
        $alias = post('driver_alias', $this->driverAlias);

        if ($this->gatewayClass !== null) {
            return $this->gatewayClass;
        }

        if (!$gateway = ExchangeManager::instance()->findConverterByAlias($alias)) {
            throw new ApplicationException("Unable to find driver: {$alias}");
        }

        return $this->gatewayClass = $gateway->class;
    }
}
