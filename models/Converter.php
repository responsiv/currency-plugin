<?php namespace Responsiv\Currency\Models;

use Model;

/**
 * Converter Model
 */
class Converter extends Model
{
    use \October\Rain\Database\Traits\Purgeable;
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_currency_converters';

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
        $this->applyConverterClass();

        $this->attributes = array_merge($this->config_data, $this->attributes);
    }

    public function beforeValidate()
    {
        if (!$this->applyConverterClass()) {
            return;
        }
    }

    public function beforeSave()
    {
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
    }

    /**
     * {@inheritDoc}
     */
    public function getConverterClass()
    {
        return $this->class_name;
    }
}