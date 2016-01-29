<?php namespace Responsiv\Currency\Controllers;

use Lang;
use Flash;
use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Responsiv\Currency\Models\Currency as CurrencyModel;
use Exception;

/**
 * Currencies Back-end Controller
 */
class Currencies extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Responsiv.Currency', 'currencies');

        $this->addJs('/plugins/responsiv/currency/assets/js/currency-list.js');
    }

    /**
     * {@inheritDoc}
     */
    public function listInjectRowClass($record, $definition = null)
    {
        if (!$record->is_enabled) {
            return 'safe disabled';
        }
    }

    public function onCreateForm()
    {
        $this->asExtension('FormController')->create();

        return $this->makePartial('create_form');
    }

    public function onCreate()
    {
        CurrencyModel::clearCache();
        $this->asExtension('FormController')->create_onSave();

        return $this->listRefresh();
    }

    public function onUpdateForm()
    {
        $this->asExtension('FormController')->update(post('record_id'));
        $this->vars['recordId'] = post('record_id');

        return $this->makePartial('update_form');
    }

    public function onUpdate()
    {
        CurrencyModel::clearCache();
        $this->asExtension('FormController')->update_onSave(post('record_id'));

        return $this->listRefresh();
    }

    public function onDelete()
    {
        CurrencyModel::clearCache();
        $this->asExtension('FormController')->update_onDelete(post('record_id'));

        return $this->listRefresh();
    }

    public function onLoadDisableForm()
    {
        try {
            $this->vars['checked'] = post('checked');
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        return $this->makePartial('disable_form');
    }

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