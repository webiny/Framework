<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Tests;

use Webiny\Component\Storage\Storage;
use Webiny\Component\Storage\StorageTrait;

class CloudStorageTest extends \PHPUnit_Framework_TestCase
{
    use StorageTrait;

    const CONFIG = '/ExampleConfig.yaml';

    private $_key = 'testFile.txt';

    /**
     * @dataProvider driverSet
     */
    public function testConstructor($storage)
    {
        $this->assertInstanceOf('Webiny\Component\Storage\Storage', $storage);
    }

    /**
     * @dataProvider driverSet
     */
    public function testStorage(Storage $storage)
    {
        $storage->setContents($this->_key, 'Test contents');
        $this->assertTrue($storage->keyExists($this->_key));

        $this->assertSame('Test contents', $storage->getContents($this->_key));

        $storage->deleteKey($this->_key);
        $this->assertFalse($storage->keyExists($this->_key));
    }

    public function driverSet()
    {
        Storage::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [$this->storage('CloudStorage')]
        ];
    }

}