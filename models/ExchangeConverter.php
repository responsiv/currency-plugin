<?php namespace Responsiv\Currency\Models;

use October\Rain\Database\ExpandoModel;
use Responsiv\Currency\Classes\ExchangeManager;

/**
 * ExchangeConverter Model
 *
 * @property int $id
 * @property string $name
 * @property string $class_name
 * @property int $refresh_interval
 * @property array $config_data
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
class ExchangeConverter extends ExpandoModel
{
    use \October\Rain\Database\Traits\Sortable;
    use \October\Rain\Database\Traits\Purgeable;
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_currency_exchange_converters';

    /**
     * @var string expandoColumn name to store the data
     */
    protected $expandoColumn = 'config_data';

    /**
     * @var array expandoPassthru attributes that should not be serialized
     */
    protected $expandoPassthru = [
        'name',
        'class_name',
        'refresh_interval',
    ];

    /**
     * @var array purgeable list of attribute names which should not be saved to the database
     */
    protected $purgeable = ['converter_name'];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'name' => 'required'
    ];

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

    /**
     * afterFetch
     */
    public function afterFetch()
    {
        $this->applyDriverClass();
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
}
