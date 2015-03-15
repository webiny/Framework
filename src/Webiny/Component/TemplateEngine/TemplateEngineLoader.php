<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine;

use Webiny\Component\TemplateEngine\Bridge\TemplateEngine as TemplateEngineBridge;

/**
 * Creates instances of template engine drivers.
 *
 * @package         Webiny\Component\TemplateEngine
 */
class TemplateEngineLoader
{

    /**
     * @var array
     */
    private static $instances = [];

    /**
     * Returns an instance of template engine driver.
     * If the requested driver is already created, the same instance is returned.
     *
     * @param string $driver Name of the template engine driver. Must correspond to components.template_engine.engines.{$driver}.
     *
     * @return \Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface
     * @throws TemplateEngineException
     * @throws \Exception
     */
    static function getInstance($driver)
    {

        if (isset(self::$instances[$driver])) {
            return self::$instances[$driver];
        }

        $driverConfig = TemplateEngine::getConfig()->get('Engines.' . $driver, false);
        if (!$driverConfig) {
            throw new TemplateEngineException('Unable to read driver configuration: TemplateEngine.Engines.' . $driver);
        }

        try {
            self::$instances[$driver] = TemplateEngineBridge::getInstance($driver, $driverConfig);

            return self::$instances[$driver];
        } catch (\Exception $e) {
            throw $e;
        }
    }

}