<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib;

use Webiny\Component\ClassLoader\ClassLoader;
use Webiny\Component\Config\Config;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\ServiceManager\ServiceManager;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Component trait is a helper that automatically parses the component configuration, registers defined class loader
 * maps and services.
 *
 * @package         Webiny\Component\StdLib
 */
trait ComponentTrait
{
    private static $componentConfig;

    /**
     * Set the configuration file.
     * The root of your yaml config file must match the component class name, or an exception will be thrown.
     * If you wish to define a default config, just create a static array called $defaultConfig.
     * When setting/updating a config, it is always merged with the default config and current loaded config.
     *
     * @param string|array|ConfigObject $componentConfig Path to the configuration YAML file or ConfigObject instance
     *
     * @throws Exception\Exception
     */
    public static function setConfig($componentConfig)
    {
        // get component name
        $component = new StringObject(__CLASS__);
        $component = $component->explode('\\')->last();

        if (!$componentConfig) {
            throw new Exception\Exception('Invalid config passed to ' . $component . ' component. Config must be a string, array or ConfigObject.');
        }

        // check if we have default config
        if (isset(self::$defaultConfig)) {
            self::$componentConfig = new ConfigObject(self::$defaultConfig);
        } else {
            self::$componentConfig = new ConfigObject([]);
        }

        // validate config
        if ($componentConfig instanceof ConfigObject) {
            $config = $componentConfig;
        } elseif (is_array($componentConfig) || $componentConfig instanceof ArrayObject) {
            $config = new ConfigObject($componentConfig);
        } else {
            $config = Config::getInstance()->yaml($componentConfig)->get($component, false);
        }

        if (!$config) {
            throw new Exception\Exception('Invalid configuration file for ' . $component . ' component.');
        }

        // merge current config with new config
        self::$componentConfig->mergeWith($config);

        // automatically assign parameters to ServiceManager
        if (self::$componentConfig->get('Parameters', false)) {
            ServiceManager::getInstance()->registerParameters(self::$componentConfig->get('Parameters'));
        }

        // automatically register services
        if (self::$componentConfig->get('Services', false)) {
            ServiceManager::getInstance()->registerServices($component, self::$componentConfig->get('Services'), true);
        }

        // automatically register class loader libraries
        if (self::$componentConfig->get('ClassLoader', false)) {
            ClassLoader::getInstance()->registerMap(self::$componentConfig->get('ClassLoader')->toArray());
        }

        // trigger callback
        self::postSetConfig();
    }

    /**
     * Returns the current component configuration.
     *
     * @return ConfigObject
     */
    public static function getConfig()
    {
        if (!is_object(self::$componentConfig)) {
            $config = [];

            // check if we have default config
            if (isset(self::$defaultConfig)) {
                $config = self::$defaultConfig;
            }
            self::$componentConfig = new ConfigObject($config);
        }

        return self::$componentConfig;
    }

    /**
     * Append new config to the existing component config
     *
     * @param array|ConfigObject $config
     */
    public static function appendConfig($config)
    {
        self::setConfig(self::getConfig()->mergeWith($config));
    }

    /**
     * Callback that is called once the config is set.
     */
    protected static function postSetConfig()
    {
        // Override to implement
    }
}