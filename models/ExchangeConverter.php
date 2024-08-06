<?php namespace Responsiv\Currency\Models;

use October\Rain\Database\ExpandoModel;
use ValidationException;

/**
 * ExchangeConverter Model
 *
 * @property int $id
 * @property string $name
 * @property string $class_name
 * @property int $refresh_interval
 * @property array $config_data
 * @property bool $is_enabled
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
class ExchangeConverter extends ExpandoModel
{
    use \October\Rain\Database\Traits\Purgeable;
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Defaultable;

    /**
     * @var string NO_RATE_DATA specified
     */
    const NO_RATE_DATA = -1;

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
        'is_enabled',
        'is_default',
        'refresh_interval',
        'fallback_converter_id',
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
     * @var array belongsTo
     */
    public $belongsTo = [
        'fallback_converter' => ExchangeConverter::class
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
        $this->converter_name = array_get($this->driverDetails(), 'name', 'Unknown');
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

        if (!$this->is_enabled && $this->is_default) {
            throw new ValidationException(['is_enabled' => __("The default currency converter cannot be disabled.")]);
        }
    }

    /**
     * getRefreshIntervalOptions
     */
    public function getRefreshIntervalOptions()
    {
        return [
            '48' => '48 hours',
            '24' => '24 hours',
            '12' => '12 hours',
            '6'  => '6 hours',
            '3'  => '3 hours',
            '1'  => '1 hour',
        ];
    }
}
