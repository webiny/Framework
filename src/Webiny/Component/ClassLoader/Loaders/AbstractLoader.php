<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ClassLoader\Loaders;

/**
 * LoaderInterface defines two methods that every loader must implement.
 *
 * @package         Webiny\Component\ClassLoader\Loaders
 */

abstract class AbstractLoader
{
    /**
     * @var bool|array A list of registered maps.
     */
    protected $maps = false;

    /**
     * @var array Optional rules that are attached to the map.
     */
    protected $rules;

    /**
     * Get an instance of Loader.
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (static::$instance !== null) {
            return static::$instance;
        }

        static::$instance = new static;

        return static::$instance;
    }

    /**
     * Removes the given map prefix from class loader.
     *
     * @param string $mapPrefix Map prefix that should be removed.
     *
     * @return bool Returns true if the map prefix was found and removed, otherwise false.
     */
    public function unregisterMap($mapPrefix)
    {
        if(isset($this->maps[$mapPrefix])){
            unset($this->maps[$mapPrefix]);
        }

        return false;
    }

    /**
     * Register a map.
     *
     * @param string       $prefix  Map prefix or namespace.
     * @param array|string $library Absolute path to the library or an array with path and additional options.
     *
     * @return void
     */
    abstract public function registerMap($prefix, $library);

    /**
     * Parses that class name and returns the absolute path to the class.
     * NOTE: no file existence checks should be performed, nor should the method require or include the class, is
     * should just return the path to the file.
     *
     * @param string $class Class name that should be loaded.
     *
     * @return string|bool Path to the class or false.
     */
    abstract public function findClass($class);
}