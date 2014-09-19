<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ClassLoader\Loaders;

/**
 * PEAR autoloader.
 *
 * @package         Webiny\Component\ClassLoader\Loaders
 */
class Pear extends LoaderAbstract
{
    /**
     * @var LoaderAbstract Holds the loader instance.
     */
    protected static $_instance = null;

    /**
     * Register a map.
     *
     * @param string       $prefix  Map prefix or namespace.
     * @param array|string $library Absolute path to the library or an array with path and additional options.
     *
     * @return void
     */
    public function registerMap($prefix, $library)
    {
        // check the structure of location if it contains metadata
        if (is_array($library)) {
            $path = $library['Path'];
            $this->_rules[$prefix] = $library;
        } else {
            $path = $library;
        }

        $this->_maps[$prefix] = $path;
    }

    /**
     * Parses that class name and returns the absolute path to the class.
     * NOTE: no file existence checks should be performed, nor should the method require or include the class, is
     * should just return the path to the file.
     *
     * @param string $class Class name that should be loaded.
     *
     * @return string|bool Path to the class or false.
     */
    public function findClass($class)
    {
        if (!$this->_maps) {
            return false;
        }

        // PEAR-like class name
        $normalizedClass = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

        foreach ($this->_maps as $prefix => $dir) {
            if (0 !== strpos($class, $prefix)) {
                continue;
            }

            if (isset($this->_rules[$prefix])) {
                if (isset($this->_rules[$prefix]['Normalize'])) {
                    $normalizedClass = $class . '.php';
                }

                if (isset($this->_rules[$prefix]['Case'])) {
                    if ($this->_rules[$prefix]['Case'] == 'lower') {
                        $normalizedClass = strtolower($normalizedClass);
                    }
                }
            }

            $file = rtrim($dir, '/') . DIRECTORY_SEPARATOR . $normalizedClass;

            // no check if a file exists or not
            return $file;
        }

        return false;
    }
}