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
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var array required permissions
     */
    public $requiredPermissions = [];

    /**
     * @var string settingsItemCode determines the settings code
     */
    public $settingsItemCode = 'currency_converters';

    /**
     * index
     */
    public function index()
    {
        try {
            $record = ExchangeConverter::getDefault();
            $this->update($record->id);
        }
        catch (Exception $ex) {
            $this->controller->handleError($ex);
        }
    }

    /**
     * index_onSave
     */
    protected function index_onSave()
    {
        $record = ExchangeConverter::getDefault();
        return $this->update_onSave($record->id);
    }

    /**
     * formExtendModel
     */
    public function formExtendModel($model)
    {
        $model->autoExtend = false;
        return $model;
    }

    /**
     * formExtendFields
     */
    public function formExtendFields($widget)
    {
        $model = $widget->model;
        $className = post('ExchangeConverter[class_name]', $model->class_name);
        $model->applyDriverClass($className);

        $config = $model->getFieldConfig();
        if (isset($config->fields)) {
            $widget->addFields($config->fields, 'primary');
        }
    }

}