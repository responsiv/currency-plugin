<?php namespace Responsiv\Currency\ContentFields;

use Site;
use Currency as CurrencyService;
use Tailor\Classes\ContentFieldBase;
use October\Contracts\Element\FormElement;
use October\Contracts\Element\ListElement;

/**
 * Currency Content Field
 */
class Currency extends ContentFieldBase
{
    /**
     * defineFormField will define how a field is displayed in a form.
     */
    public function defineFormField(FormElement $form, $context = null)
    {
        $form->addFormField($this->fieldName, $this->label)->useConfig($this->config)->displayAs('currency');
    }

    /**
     * defineListColumn will define how a field is displayed in a list.
     */
    public function defineListColumn(ListElement $list, $context = null)
    {
        $useSite = $this->getDefaultColumnSite($list);

        $list->defineColumn($this->fieldName, $this->label)->displayAs('currency')->site($useSite);
    }

    /**
     * extendModel
     */
    public function extendModel($model)
    {
        // @todo unfinished
        return;

        if ($model instanceof \Tailor\Models\RecordExport) {
            $model->bindEvent('model.beforeExportAttribute', function ($attr, &$value) {
                if ($attr === $this->fieldName) {
                    $value = CurrencyService::getForModel($this->model, $attr)->toFloatValue($value);
                }
            });
        }

        if ($model instanceof \Tailor\Models\RecordImport) {
        }
    }

    /**
     * extendDatabaseTable adds any required columns to the database.
     */
    public function extendDatabaseTable($table)
    {
        $table->bigInteger($this->fieldName)->nullable();
    }

    /**
     * getDefaultColumnSite returns true if the model and field name uses multisite
     */
    protected function getDefaultColumnSite($list)
    {
        if ($this->site !== null) {
            return $this->site;
        }

        if (!$model = $list->{'getModel'}()) {
            return null;
        }

        if (Site::isModelMultisite($model, $this->fieldName)) {
            return true;
        }

        return false;
    }
}
