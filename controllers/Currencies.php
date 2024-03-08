<?php namespace Responsiv\Currency\Controllers;

use Lang;
use Flash;
use Backend;
use Backend\Classes\SettingsController;
use Responsiv\Currency\Models\Currency as CurrencyModel;
use Exception;

/**
 * Currencies Backend Controller
 */
class Currencies extends SettingsController
{
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
    public $requiredPermissions = [];

    /**
     * @var string settingsItemCode determines the settings code
     */
    public $settingsItemCode = 'currencies';

    /**
     * {@inheritDoc}
     */
    public function listInjectRowClass($record, $definition = null)
    {
        if (!$record->is_enabled) {
            return 'safe disabled';
        }
    }

    /**
     * index
     */
    public function index()
    {
        CurrencyModel::syncPrimaryCurrency();

        $this->asExtension('ListController')->index();
    }

    /**
     * formAfterSave is called after the creation or updating form is saved
     */
    public function formAfterSave($model)
    {
        CurrencyModel::clearCache();
    }

    /**
     * onLoadDisableForm
     */
    public function onLoadDisableForm()
    {
        try {
            $this->vars['checked'] = (array) post('checked');
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        return $this->makePartial('disable_form');
    }

    /**
     * onDisableCurrencies
     */
    public function onDisableCurrencies()
    {
        $enable = post('enable', false);
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $objectId) {
                if (!$object = CurrencyModel::find($objectId)) {
                    continue;
                }

                $object->is_enabled = $enable;
                $object->save();
            }

        }

        if ($enable) {
            Flash::success(Lang::get('responsiv.currency::lang.currency.enable_success'));
        }
        else {
            Flash::success(Lang::get('responsiv.currency::lang.currency.disable_success'));
        }

        return Backend::redirect('responsiv/currency/currencies');
    }
}
