<?php namespace Responsiv\Currency\Classes\ExchangeManager;

use October\Rain\Support\Collection;

/**
 * HasCurrencyConverters
 *
 * @package responsiv\currency
 * @author Alexey Bobkov, Samuel Georges
 */
trait HasCurrencyConverters
{
    /**
     * @var array converters registered
     */
    private $converters;

    /**
     * loadConverters registered in the system
     */
    protected function loadConverters()
    {
        $methodValues = $this->pluginManager->getRegistrationMethodValues('registerCurrencyConverters');

        foreach ($methodValues as $id => $converters) {
            $this->registerConverters($id, $converters);
        }
    }

    /**
     * registerConverters for currencies
     * The argument is an array of the converter classes.
     * @param string $owner Specifies the menu items owner plugin or module in the format Author.Plugin.
     * @param array $classes An array of the converter type classes.
     */
    public function registerConverters($owner, array $classes)
    {
        if (!$this->converters) {
            $this->converters = [];
        }

        foreach ($classes as $class => $alias) {
            $converter = (object)[
                'owner' => $owner,
                'class' => $class,
                'alias' => $alias,
            ];

            $this->converters[$alias] = $converter;
        }
    }

    /**
     * listConverters returns a list of the converter type classes. As object of a
     * collection with extended information found in the class object.
     */
    public function listConverters(bool $asObject = true)
    {
        if ($this->converters === null) {
            $this->loadConverters();
        }

        if (!$asObject) {
            return $this->converters;
        }

        // Bless the collection with converter objects
        $collection = [];

        foreach ($this->converters as $converter) {
            if (!class_exists($converter->class)) {
                continue;
            }

            $converterObj = new $converter->class;
            $converterDetails = $converterObj->driverDetails();
            $collection[$converter->alias] = (object)[
                'owner' => $converter->owner,
                'class' => $converter->class,
                'alias' => $converter->alias,
                'object' => $converterObj,
                'name' => array_get($converterDetails, 'name', 'Undefined'),
                'description' => array_get($converterDetails, 'description', 'Undefined'),
            ];
        }

        return new Collection($collection);
    }

    /**
     * listConverterObjects returns a list of the converter type objects
     */
    public function listConverterObjects(): array
    {
        $collection = [];
        $converters = $this->listConverters(true);
        foreach ($converters as $converter) {
            $collection[$converter->alias] = $converter->object;
        }

        return $collection;
    }

    /**
     * findConverterByAlias returns a converter based on its unique alias.
     */
    public function findConverterByAlias($alias)
    {
        $converters = $this->listConverters();
        if (!isset($converters[$alias])) {
            return false;
        }

        return $converters[$alias];
    }
}
