<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ClassLoader;

use Webiny\Component\Cache\CacheStorage;

require_once __DIR__ . '/Loaders/AbstractLoader.php';
require_once __DIR__ . '/Loaders/Pear.php';
require_once __DIR__ . '/Loaders/Psr0.php';
require_once __DIR__ . '/Loaders/Psr4.php';

/**
 * Class loader implements a more standardized way of autoloading files.
 *
 * @package         Webiny\Component\ClassLoader
 */
class ClassLoader
{
    /**
     * @var null Holds the ClassLoader instance.
     */
    private static $instance = null;

    /**
     * @var bool|CacheInterface
     */
    private $cache = false;

    /**
     * @var array List of built-in loaders
     */
    private $loaders = [
        'Psr4',
        'Psr0',
        'Pear'
    ];


    /**
     * Base constructor.
     */
    private function __construct()
    {
        // omit the constructor
    }

    /**
     * Get an instance of ClassLoader.
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new static;
        self::registerAutoloader();

        return self::$instance;
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
        foreach ($this->loaders as $l) {
            $loader = "\\Webiny\\Component\\ClassLoader\\Loaders\\" . $l;
            $result = $loader::getInstance()->unregisterMap($mapPrefix);

            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets a cache layer in front of the autoloader.
     * Unregister the old ClassLoader::getClass autoload method.
     *
     * @param CacheStorage $cache Instance of the \Webiny\Component\Cache\Cache class.
     *
     * @throws \Exception
     */
    public function registerCacheDriver(CacheStorage $cache)
    {
        // set cache
        $this->cache = $cache;

        // unregister the old autoloader
        spl_autoload_unregister([
                self::$instance,
                'getClass'
            ]);

        // prepend the new cache autoloader
        spl_autoload_register([
            self::$instance,
            'getClassFromCache'
        ], true, true);
    }

    /**
     * Register a namespace or PEAR map rule.
     * NOTE: PEAR rules must end with an underline '_'.
     *
     * @param array $maps - Array of maps rules. An example rule is ['Webiny' => '/var/WebinyFramework/library']
     */
    public function registerMap(array $maps)
    {
        foreach ($maps as $prefix => $library) {
            $endChar = substr($prefix, -1);
            if ($endChar == '_') {
                Loaders\Pear::getInstance()->registerMap($prefix, $library);
            } else {
                if (is_array($library)) {
                    if (isset($library['Psr']) && $library['Psr'] == '0') {
                        Loaders\Psr0::getInstance()->registerMap($prefix, $library);
                        continue;
                    }
                }

                Loaders\Psr4::getInstance()->registerMap($prefix, $library);
            }
        }
    }

    /**
     * Tries to find the class file based on currently registered rules.
     *
     * @param string $class Name of the class you are trying to find.
     *
     * @return bool True is returned if the class if found and loaded into memory.
     */
    public function getClass($class)
    {
        if ($file = $this->findClass($class)) {
            require $file;

            return true;
        }

        return false;
    }

    /**
     * First tries to find the class in the cache. If the class is not found in the cache, then it tries to find it
     * by using the registered maps.
     *
     * @param string $class Name of the class you are trying to find.
     *
     * @return bool True is retuned if the class if found and loaded into memory.
     */
    public function getClassFromCache($class)
    {
        // from cache
        if (($file = $this->cache->read($class))) {
            require $file;
        }

        // from disk
        if ($file = $this->findClass($class)) {
            $this->cache->save('wf.component.class_loader.' . $class, $file, 600, [
                    '_wf',
                    '_component',
                    '_class_loader'
                ]);
            require $file;
        }
    }

    /**
     * Tries to get the path to the class based on registered maps.
     *
     * @param string $class The name of the class
     *
     * @return string|bool The path, if found, or false.
     */
    public function findClass($class)
    {
        if (strrpos($class, '\\') !== false) {
            $file = Loaders\Psr4::getInstance()->findClass($class);
            if (!$file) {
                $file = Loaders\Psr0::getInstance()->findClass($class);
            }
        } else {
            $file = Loaders\Pear::getInstance()->findClass($class);
        }

        return $file;
    }

    /**
     * Registers SPL autoload function.
     */
    private static function registerAutoloader()
    {
        spl_autoload_register([
            self::$instance,
            'getClass'
        ], true, true);
    }

}