<?php namespace Responsiv\Currency\Models;

use Model;
use Responsiv\Currency\Classes\CurrencyManager;

/**
 * ExchangeConverter Model
 *
 * @property int $id
 * @property string $name
 * @property string $class_name
 * @property int $refresh_interval
 * @property array $config_data
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
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
     * @var array Fillable fields
     */
    protected $fillable = [
        'name'
    ];

    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected $jsonable = ['config_data'];

    /**
     * @var array purgeable list of attribute names which should not be saved to the database
     */
    protected $purgeable = ['converter_name'];

    /**
     * @var array rules to be applied to the data.
     */
    public $rules = [];

    /**
     * @var bool autoExtend is set to false to disable automatic implementation of the converter type behavior.
     */
    public $autoExtend = true;

    /**
     * @var array splicedAttributes that have been spliced in from config data and should be purged.
     */
    protected $splicedAttributes = [];

    /**
     * getDefault returns the first exchange converter. There can be only one.
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

    /**
     * getClassNameOptions
     */
    public function getClassNameOptions()
    {
        $converters = CurrencyManager::instance()->listConverters();

        $converters->sortBy('name');

        return $converters->lists('name', 'class');
    }

    /**
     * getRefreshIntervalOptions
     */
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
     * applyDriverClass extends this class with the converter class
     * @param  string $class
     * @return bool
     */
    public function applyDriverClass($class = null)
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

    /**
     * afterFetch
     */
    public function afterFetch()
    {
        if ($this->autoExtend) {
            $this->applyDriverClass();
        }

        $this->splicedAttributes = (array) $this->config_data;
        $this->attributes = array_merge($this->splicedAttributes, $this->attributes);
    }

    /**
     * beforeValidate
     */
    public function beforeValidate()
    {
        if ($this->applyDriverClass()) {
            $this->getDriverObject()->validateDriverHost($this);
        }
    }

    /**
     * beforeSave
     */
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
     * getDriverObject returns the gateway class extension object.
     * @param  string $class Class name
     * @return \Responsiv\Currency\Classes\ExchangeBase
     */
    public function getDriverObject($class = null)
    {
        if (!$class) {
            $class = $this->class_name;
        }

        return $this->asExtension($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getDriverClass()
    {
        return $this->class_name;
    }
}
