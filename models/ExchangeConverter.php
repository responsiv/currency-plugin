<?php namespace Responsiv\Currency\Models;

use Model;
use Responsiv\Currency\Classes\ExchangeManager;

/**
 * Converter Model
 */
class ExchangeConverter extends Model
{
    use \October\Rain\Database\Traits\Purgeable;
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_currency_exchange_converters';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['config_data'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array List of attribute names which are json encoded and decoded from the database.
     */
    protected $jsonable = ['config_data'];

    /**
     * @var array List of attribute names which should not be saved to the database.
     */
    protected $purgeable = ['converter_name'];

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];

    /**
     * @var bool Set to false to disable automatic implementation of the converter type behavior.
     */
    public $autoExtend = true;

    /**
     * @var array Attributes that have been spliced in from config data and should be purged.
     */
    protected $splicedAttributes = [];

    /**
     * Returns the first exchange converter. There can be only one.
     * @return self
     */
    public static function getDefault()
    {
        if ($obj = self::first()) {
            return $obj;
        }

        $obj = new self;
        $obj->class_name = 'Responsiv\Currency\ExchangeTypes\EuropeanCentralBank';
        $obj->refresh_interval = 24;
        $obj->save();
        return $obj;
    }

    public function getClassNameOptions()
    {
        $converters = ExchangeManager::instance()->listConverters();
        $converters->sortBy('name');
        return $converters->lists('name', 'class');
    }

    public function getRefreshIntervalOptions()
    {
        return [
            '1'  => '1 hour',
            '3'  => '3 hours',
            '6'  => '6 hours',
            '12' => '12 hours',
            '24' => '24 hours'
        ];
    }

    /**
     * Extends this class with the converter class
     * @param  string $class Class name
     * @return boolean
     */
    public function applyConverterClass($class = null)
    {
        if (!$class) {
            $class = $this->class_name;
        }

        if (!$class) {
            return false;
        }

        if (!$this->isClassExtendedWith($class)) {
            $this->extendClassWith($class);
        }

        $this->class_name = $class;
        $this->converter_name = array_get($this->converterDetails(), 'name', 'Unknown');
        return true;
    }

    public function afterFetch()
    {
        if ($this->autoExtend) {
            $this->applyConverterClass();
        }

        $this->splicedAttributes = (array) $this->config_data;
        $this->attributes = array_merge($this->splicedAttributes, $this->attributes);
    }

    public function beforeValidate()
    {
        if (!$this->applyConverterClass()) {
            return;
        }
    }

    public function beforeSave()
    {
        if (!$this->class_name) {
            return;
        }

        $configData = [];
        $fieldConfig = $this->getFieldConfig();
        $fields = isset($fieldConfig->fields) ? $fieldConfig->fields : [];

        foreach ($fields as $name => $config) {
            if (!array_key_exists($name, $this->attributes)) {
                continue;
            }

            $configData[$name] = $this->attributes[$name];
            unset($this->attributes[$name]);
        }

        $this->config_data = $configData;
        $this->attributes = array_except($this->attributes, array_keys($this->splicedAttributes));
    }

    /**
     * {@inheritDoc}
     */
    public function getConverterClass()
    {
        return $this->class_name;
    }
}
