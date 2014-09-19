<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Annotations\Bridge;

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
    private static $_library = '\Webiny\Component\Annotations\Bridge\Minime\Annotations';


    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    public static function _getLibrary()
    {
        return \Webiny\Component\Annotations\Annotations::getConfig()->get('Bridge', self::$_library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new bridge class.
     *                            The class must implement \Webiny\Component\Annotations\Bridge\AnnotationsInterface.
     */
    public static function setLibrary($pathToClass)
    {
        self::$_library = $pathToClass;
    }

    /**
     * Create an instance of a annotations driver.
     *
     * @throws AnnotationsException
     * @return AnnotationsInterface
     */
    public static function getInstance()
    {
        $driver = static::_getLibrary();

        try {
            $instance = self::factory($driver, '\Webiny\Component\Annotations\Bridge\AnnotationsInterface');
        } catch (\Exception $e) {
            throw new AnnotationsException($e->getMessage());
        }

        return $instance;
    }
}