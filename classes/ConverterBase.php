<?php namespace Responsiv\Currency\Classes;

use Str;
use URL;
use File;
use System\Classes\ModelBehavior;

/**
 * Represents a currency converter service.
 * All other converters must be derived from this class
 */
class ConverterBase extends ModelBehavior
{
    use \System\Traits\ConfigMaker;

    protected $rateModel = 'Responsiv\Currency\Models\Rate';

    /**
     * Returns information about the converter type
     * Must return array:
     * 
     * [
     *      'name'        => 'XE.com',
     *      'description' => 'Conversion services provided by XE.'
     * ]
     *
     * @return array
     */
    public function converterDetails()
    {
        return [
            'name'        => 'Unknown',
            'description' => 'Unknown conversion service.'
        ];
    }

    /**
     * @var mixed Extra field configuration for the converter type.
     */
    protected $fieldConfig;

    /**
     * Constructor
     */
    public function __construct($model = null)
    {
        parent::__construct($model);

        /*
         * Parse the config
         */
        $this->configPath = $this->guessConfigPathFrom($this);
        $this->fieldConfig = $this->makeConfig($this->defineFormFields());

        if (!$model)
            return;

        $this->boot($model);
    }

    /**
     * Boot method called when the converter is first loaded
     * with an existing model.
     * @return array
     */
    public function boot($host)
    {
        // Set default data
        if (!$host->exists)
            $this->initConfigData($host);

        // Apply validation rules
        $host->rules = array_merge($host->rules, $this->defineValidationRules());
    }

    /**
     * Extra field configuration for the converter type.
     */
    public function defineFormFields()
    {
        return 'fields.yaml';
    }

    /**
     * Initializes configuration data when the converter is first created.
     * @param  Model $host
     */
    public function initConfigData($host){}

    /**
     * Defines validation rules for the custom fields.
     * @return array
     */
    public function defineValidationRules()
    {
        return [];
    }

    /**
     * Returns the field configuration used by this model.
     */
    public function getFieldConfig()
    {
        return $this->fieldConfig;
    }

    /**
     * Returns the exchange rate for two currencies.
     * @param string $fromCurrency Currency code to convert from (eg: USD)
     * @param string $toCurrency Currency code to convert to (eg: AUD)
     * @return int
     */
    public function getRate($fromCurrency, $toCurrency)
    {

    }

    /**
     * Convert a currency value from one currency to another.
     * @param number $value Specifies a value to convert
     * @param string $fromCurrency Currency code to convert from (eg: USD)
     * @param string $toCurrency Currency code to convert to (eg: AUD)
     * @param int $round Number of decimal digits to round the result to. Specify NULL to disable.
     * @return int
     */
    public function convert($value, $fromCurrency, $toCurrency, $round = 2)
    {

    }

    /**
     * Creates an instance of the exchange rate model
     */
    protected function createRateModel()
    {
        $class = '\\'.ltrim($this->rateModel, '\\');
        $model = new $class();
        return $model;
    }

}

