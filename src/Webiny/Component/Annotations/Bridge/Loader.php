<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Annotations\Bridge;

use Webiny\Component\Annotations\Bridge\Minime\Annotations;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Image bridge loader.
 *
 * @package         Webiny\Component\Annotations\Bridge
 */
class Loader
{
    use FactoryLoaderTrait, StdLibTrait;

    /**
     * @var string Default Annotations bridge.
     */
    private static $library = Annotations::class;


    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    public static function getLibrary()
    {
        return \Webiny\Component\Annotations\Annotations::getConfig()->get('Bridge', self::$library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new bridge class.
     *                            The class must implement \Webiny\Component\Annotations\Bridge\AnnotationsInterface.
     */
    public static function setLibrary($pathToClass)
    {
        self::$library = $pathToClass;
    }

    /**
     * Create an instance of a annotations driver.
     *
     * @throws AnnotationsException
     * @return AnnotationsInterface
     */
    public static function getInstance()
    {
        $driver = static::getLibrary();

        try {
            $instance = self::factory($driver, AnnotationsInterface::class);
        } catch (\Exception $e) {
            throw new AnnotationsException($e->getMessage());
        }

        return $instance;
    }
}