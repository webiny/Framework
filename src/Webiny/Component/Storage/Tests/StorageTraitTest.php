<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Tests;

use Webiny\Component\Storage\Storage;
use Webiny\Component\Storage\StorageTrait;

class StorageTraitTest extends \PHPUnit_Framework_TestCase
{
    use StorageTrait;

    const CONFIG = 'ExampleConfig.yaml';

    public function setUp()
    {
        Storage::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    public function testStorage()
    {
        $this->assertInstanceOf(Storage::class, $this->storage('LocalStorage'));
        $this->assertInstanceOf(Storage::class, $this->storage('CloudStorage'));
    }
}