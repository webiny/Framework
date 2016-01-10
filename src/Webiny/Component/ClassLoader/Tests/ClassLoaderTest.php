<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ClassLoader\Tests;

use Webiny\Component\Cache\Cache;
use Webiny\Component\ClassLoader\ClassLoader;

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testGetInstance()
    {
        $this->assertInstanceOf('\Webiny\Component\ClassLoader\ClassLoader', ClassLoader::getInstance());
    }

    public function testRegisterSpl()
    {
        $autoloaders = spl_autoload_functions();
        $this->assertSame('Webiny\Component\ClassLoader\ClassLoader', get_class($autoloaders[0][0]));
        $this->assertSame('getClass', $autoloaders[0][1]);
    }

    public function testRegisterCacheDriver()
    {
        ClassLoader::getInstance()->registerCacheDriver(Cache::BlackHole());
        $autoloaders = spl_autoload_functions();
        $this->assertSame('getClassFromCache', $autoloaders[0][1]);
    }

    public function testFindClass()
    {
        ClassLoader::getInstance()->registerMap([
                                                    'Webiny\Component\ClassLoader' => realpath(__DIR__ . '/../')
                                                ]
        );
        $class = ClassLoader::getInstance()->findClass('Webiny\Component\ClassLoader\ClassLoader');
        $this->assertSame(realpath(__DIR__ . '/../ClassLoader.php'), $class);
    }

}