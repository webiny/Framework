<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Tests;

use MongoDB\Model\CollectionInfo;
use PHPUnit_Framework_TestCase;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityTrait;
use Webiny\Component\Entity\Tests\Lib\SimpleEntities\SimpleEntity1;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class EntityCollectionTest extends PHPUnit_Framework_TestCase
{
    use EntityTrait, MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';
    const MONGO_CONFIG = '/MongoConfig.yaml';
    private static $prices = [100, 200, 200, 400, 500, 600, 700];

    public static function setUpBeforeClass()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::MONGO_CONFIG));
        Entity::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        self::deleteAllTestCollections();

        // 200 is used twice so we can also test totalCount with filters
        foreach (self::$prices as $price) {
            $se = new SimpleEntity1();
            $se->price = $price;
            $se->save();
        }
    }

    public static function tearDownAfterClass()
    {
        self::deleteAllTestCollections();
    }

    public function testFilter()
    {
        $ec = SimpleEntity1::find([])->filter(function (SimpleEntity1 $se) {
            return $se->price > 400;
        });

        $this->assertGreaterThanOrEqual(3, count($ec));
    }

    public function testMap()
    {
        $ec = SimpleEntity1::find([])->map(function (SimpleEntity1 $se) {
            return $se->price;
        });

        $this->assertEquals(self::$prices, $ec);
    }

    public function testCountAndTotalCount()
    {
        $ec = SimpleEntity1::find(['price' => 200], [], 1);
        $this->assertSame(1, $ec->count());
        $this->assertSame(2, $ec->totalCount());
    }

    public function testContains()
    {
        $ec = SimpleEntity1::find([]);
        $ec400 = SimpleEntity1::findOne(['price' => 400]);

        $this->assertTrue($ec->contains($ec400));
        $this->assertTrue($ec->contains($ec400->id));
    }

    public function testRandomize()
    {
        $ec = SimpleEntity1::find([])->randomize()->map(function(SimpleEntity1 $se) {
            return $se->price;
        });

        $this->assertSameSize(self::$prices, $ec);
        $this->assertNotSame(self::$prices, $ec);
    }

    public function testFirstAndLast()
    {
        $first = SimpleEntity1::find([])->first();
        $last = SimpleEntity1::find([])->last();

        $this->assertEquals(self::$prices[0], $first->price);
        $this->assertEquals(self::$prices[count(self::$prices) - 1], $last->price);
    }

    private static function deleteAllTestCollections()
    {
        /* @var $collection CollectionInfo */
        foreach (self::mongo()->listCollections() as $collection) {
            if (strpos($collection->getName(), 'Entity_') === 0) {
                self::mongo()->dropCollection(substr($collection->getName(), 7));
            }
        }
    }
}