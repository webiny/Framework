<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class MongoTest extends PHPUnit_Framework_TestCase
{
    use MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';

    /**
     * @dataProvider driverSet
     */
    function testConstructor($mongo)
    {
        $this->assertInstanceOf('Webiny\Component\Mongo\Mongo', $mongo);
    }

    /**
     * @dataProvider driverSet
     */
    function testMongo(Mongo $mongo)
    {
        $collection = 'TestCollection';
        $mongo->dropCollection($collection);

        // Test create collection
        $mongo->createCollection($collection);
        $collectionNames = $mongo->getCollectionNames();
        $this->assertInternalType('array', $collectionNames);
        $this->assertContains($collection, $collectionNames);

        // Test insert data
        $data = ['name' => 'Webiny'];
        $res = $mongo->insert($collection, $data);
        $this->assertEquals(1, $mongo->count($collection));

        // Get new record ID
        $id = $res['_id'];

        // Test update and findOne
        $mongo->update($collection, ['_id' => $id], ['name' => 'Updated Webiny']);
        $data = $mongo->findOne($collection, ['_id' => $id]);
        $this->assertEquals('Updated Webiny', $data['name']);

        // Test find
        $data = $mongo->find($collection, ['name' => 'Updated Webiny']);
        $this->assertCount(1, $data);

        // Test ensureIndex
        $res = $mongo->ensureIndex($collection, 'name');
        $this->assertEquals(1, $res['ok']);

        // Test remove data
        $mongo->remove($collection, ['_id' => $id]);
        $this->assertEquals(0, $mongo->count($collection));

        // Test save
        $data = ['name' => 'Webiny Save'];
        $res = $mongo->save($collection, $data);
        $this->assertEquals(1, $res['ok']);

        // Test drop collection
        $mongo->dropCollection($collection);
        $this->assertFalse(in_array($collection, $mongo->getCollectionNames()));
    }

    function driverSet()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [$this->mongo()]
        ];
    }

}