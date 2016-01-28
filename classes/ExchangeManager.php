<?php namespace Responsiv\Currency\Classes;

use System\Classes\PluginManager;
use October\Rain\Support\Collection;
use October\Rain\Support\Singleton;

/**
 * Manages converter exchanges
 *
 * To create an instance of this singleton:
 *
 *   ExchangeManager::instance();
 *
 * @package Responsiv.Currency
 * @author Responsiv Internet
 */
class ExchangeManager extends Singleton
{
    /**
     * @var array Cache of registration callbacks.
     */
    private $callbacks = [];

    /**
     * @var array List of registered converters.
     */
    private $converters;

    /**
     * @var \System\Classes\PluginManager
     */
    protected $pluginManager;

    /**
     * {@inheritDoc}
     */
    protected static function getSingletonAccessor()
    {
        return 'responsiv.currency.exchangemanager';
    }

    /**
     * Initialize this singleton.
     */
    protected function init()
    {
        $this->pluginManager = PluginManager::instance();
    }

    /**
     * Loads the menu items from modules and plugins
     * @return void
     */
    protected function loadConverters()
    {
        /*
         * Load module items
         */
        foreach ($this->callbacks as $callback) {
            $callback($this);
        }

        /*
         * Load plugin items
         */
        $plugins = $this->pluginManager->getPlugins();

        foreach ($plugins as $id => $plugin) {
            if (!method_exists($plugin, 'registerCurrencyConverters')) {
                continue;
            }

            $converters = $plugin->registerCurrencyConverters();
            if (!is_array($converters)) {
                continue;
            }

            $this->registerConverters($id, $converters);
        }
    }

    /**
     * Registers a callback function that defines a converter type.
     * The callback function should register converters by calling the manager's
     * registerConverters() function. The manager instance is passed to the
     * callback function as an argument. Usage:
     * <pre>
     *   ExchangeManager::registerCallback(function($manager){
     *       $manager->registerConverters([...]);
     *   });
     * </pre>
     * @param callable $callback A callable function.
     */
    public function registerCallback(callable $callback)
    {
        $this->callbacks[] = $callback;
    }

    /**
     * Registers the payment converters.
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
     * Returns a list of the converter type classes.
     * @param boolean $asObject As a collection with extended information found in the class object.
     * @return array
     */
    public function listConverters($asObject = true)
    {
        if ($this->converters === null) {
            $this->loadConverters();
        }

        if (!$asObject) {
            return $this->converters;
        }

        /*
         * Enrich the collection with converter objects
         */
        $collection = [];
        foreach ($this->converters as $converter) {
            if (!class_exists($converter->class)) {
                continue;
            }

            $converterObj = new $converter->class;
            $converterDetails = $converterObj->converterDetails();
            $collection[$converter->alias] = (object)[
                'owner'       => $converter->owner,
                'class'       => $converter->class,
                'alias'       => $converter->alias,
                'object'      => $converterObj,
                'name'        => array_get($converterDetails, 'name', 'Undefined'),
                'description' => array_get($converterDetails, 'description', 'Undefined'),
            ];
        }

        return new Collection($collection);
    }

    /**
     * Returns a list of the converter type objects
     * @return array
     */
    public function listConverterObjects()
    {
        $collection = [];
        $converters = $this->listConverters(true);
        foreach ($converters as $converter) {
            $collection[$converter->alias] = $converter->object;
        }

        return $collection;
    }

    /**
     * Returns a converter based on its unique alias.
     */
    public function findByAlias($alias)
    {
        $converters = $this->listConverters();
        if (!isset($converters[$alias])) {
            return false;
        }

        return $converters[$alias];
    }

}