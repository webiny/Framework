<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Bridge;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * This class creates instances of bridge drivers.
 *
 * @package         Webiny\Component\TemplateEngine\Bridge
 */
class TemplateEngine
{
    use StdLibTrait;

    /**
     * @var string Default TemplateEngine bridge library.
     */
    private static $_library = ['Smarty' => '\Webiny\Component\TemplateEngine\Bridge\Smarty\Smarty'];

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @param string $engineName Name of the template engine for which you wish to get the
     *
     * @return string
     */
    private static function _getLibrary($engineName)
    {
        $bridges = \Webiny\Component\TemplateEngine\TemplateEngine::getConfig()->get('Bridges', false);
        if (!$bridges) {
            if (!isset(self::$_library[$engineName])) {
                return false;
            }

            return self::$_library[$engineName];
        }

        return $bridges->get($engineName, false);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $engineName  Name of the template engine for which to set the bridge library.
     * @param string $pathToClass Path to the new driver class. Must be an instance of \Webiny\Bridge\Cache\CacheInterface
     */
    public static function setLibrary($engineName, $pathToClass)
    {
        self::$_library[$engineName] = $pathToClass;
    }

    /**
     * Create an instance of an TemplateEngine driver.
     *
     * @param string                                $engineName Name of the template engine for which to load the instance.
     * @param \Webiny\Component\Config\ConfigObject $config     Template engine config.
     *
     * @throws TemplateEngineException
     * @throws \Exception
     * @return TemplateEngineInterface
     */
    public static function getInstance($engineName, ConfigObject $config)
    {
        $driver = static::_getLibrary($engineName);

        if (!self::isString($driver)) {
            throw new TemplateEngineException('Invalid driver returned for ' . $engineName . ' engine');
        }

        try {
            $instance = new $driver($config);
        } catch (\Exception $e) {
            throw $e;
        }

        if (!self::isInstanceOf($instance, '\Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface')) {
            throw new TemplateEngineException(TemplateEngineException::MSG_INVALID_ARG, [
                    'driver',
                    '\Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface'
                ]
            );
        }

        return $instance;
    }
}