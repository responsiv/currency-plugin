<?php namespace Responsiv\Currency\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Responsiv\Currency\Models\ExchangeConverter;
use Exception;

/**
 * Converters Back-end Controller
 */
class Converters extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
    ];

    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Responsiv.Currency', 'converters');
    }

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

    protected function index_onSave()
    {
        $record = ExchangeConverter::getDefault();
        return $this->update_onSave($record->id);
    }

    public function formExtendModel($model)
    {
        $model->autoExtend = false;
        return $model;
    }

    public function formExtendFields($widget)
    {
        $model = $widget->model;
        $className = post('ExchangeConverter[class_name]', $model->class_name);
        $model->applyConverterClass($className);

        $config = $model->getFieldConfig();
        if (isset($config->fields)) {
            $widget->addFields($config->fields, 'primary');
        }
    }

}