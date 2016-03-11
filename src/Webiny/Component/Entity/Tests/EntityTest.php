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
use Webiny\Component\Entity\EntityException;
use Webiny\Component\Entity\EntityTrait;
use Webiny\Component\Entity\Tests\Classes as Classes;
use Webiny\Component\Entity\Tests\Classes\NoValidation\Many2Many;
use Webiny\Component\Entity\Tests\Classes\NoValidation\Many2One;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class EntityTest extends PHPUnit_Framework_TestCase
{
    use EntityTrait, MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';
    const MONGO_CONFIG = '/MongoExampleConfig.yaml';

    public static function setUpBeforeClass()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::MONGO_CONFIG));
        Entity::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        self::deleteAllTestCollections();
    }

    public static function tearDownAfterClass()
    {
        // self::deleteAllTestCollections();
    }

    /**
     * This test runs a simple populate on entity with no validators.
     *
     * It tests:
     * - all attribute types
     * - all reference attributes: many2one, one2many and many2many
     * - it tests both on-fly creation and linking with existing entities
     */
    public function testPopulateNoValidationAllNew()
    {
        $many2one = new Many2One();
        $many2one->char = 'many2oneExisting';
        $many2one->save();

        $many2many = new Many2Many();
        $many2many->char = 'many2many1';
        $many2many->save();

        $many2many2 = new Many2Many();
        $many2many2->char = 'many2many2';
        $many2many2->save();

        $data = [
            'boolean'          => true,
            'char'             => 'char',
            'integer'          => 12,
            'float'            => 20.35,
            'date'             => '2016-03-14',
            'datetime'         => '2016-03-14 13:45:20',
            'arr'              => [1, 2, 3],
            'object'           => [
                'key1' => 'value',
                'key2' => 12
            ],
            'many2oneNew'      => [
                'char' => 'many2oneNew'
            ],
            'many2oneExisting' => $many2one,
            'one2many'         => [
                [
                    'char' => 'one2many1'
                ],
                [
                    'char' => 'one2many2'
                ]
            ],
            'many2many'        => [
                $many2many->id, // Store using simple ID
                $many2many2 // Store using entity instance
            ]
        ];

        $class = Classes\Classes::ENTITY_NO_VALIDATION;
        $entity = new $class;
        $entity->populate($data)->save();

        // Test current $entity state
        $this->assertEntityStateNoValidation($entity);

        // Load entity from DB and test state again
        $id = $entity->id;
        Entity::getInstance()->remove($entity);
        $entity = $class::findById($id);
        $this->assertEntityStateNoValidation($entity);
    }

    /**
     * @expectedException \Webiny\Component\Entity\EntityException
     * @dataProvider requiredData
     */
    public function testRequiredValidation($data)
    {
        try {
            $entity = new Classes\Validation\EntityRequired();
            $entity->populate($data);
        } catch (EntityException $e) {
            //print_r($e->getInvalidAttributes());
            throw $e;
        }
    }

    public function requiredData()
    {
        return [
            [[]],
            [
                [
                    'boolean' => true,
                ]
            ],
            [
                [
                    'boolean' => true,
                    'char' => 'abc',
                ]
            ],
            [
                [
                    'boolean' => true,
                    'char' => 'abc',
                    'integer' => 12
                ]
            ],
            [
                [
                    'boolean' => true,
                    'char' => 'uye',
                    'integer' => 12
                ]
            ],
            [
                [
                    'boolean' => true,
                    'char'    => 'def',
                    'integer' => 12,
                    'float' => 56.24
                ]
            ],
            [
                [
                    'boolean' => true,
                    'char'    => 'def',
                    'integer' => 2,
                    'float' => 56.24,
                    'object' => [
                        'key1' => 'value'
                    ]
                ]
            ],
            [
                [
                    'boolean' => true,
                    'char'    => 'def',
                    'integer' => 2,
                    'float' => 56.24,
                    'object' => [
                        'key1' => 'value'
                    ],
                    'many2one' => []
                ]
            ],
        ];
    }

    /**
     * @expectedException \Webiny\Component\Entity\EntityException
     * @dataProvider validationData
     */
    public function testPopulateValidation($data)
    {
        try {
            $entity = new Classes\Validation\Entity();
            $entity->populate($data);
        } catch (EntityException $e) {
            //print_r($e->getInvalidAttributes());
            throw $e;
        }
    }

    public function validationData()
    {
        return [
            [
                [
                    'char' => 'ab',
                ]
            ],
            [
                [
                    'char' => 'abcdefg',
                ]
            ],
            [
                [
                    'char' => 'uye',
                ]
            ],
            [
                [
                    'integer' => '',
                ]
            ],
            [
                [
                    'integer' => 2,
                ]
            ],
            [
                [
                    'integer' => 6,
                ]
            ],
            [
                [
                    'float'   => 1,
                ]
            ],
            [
                [
                    'float'   => 6,
                ]
            ],
            [
                [
                    'object'  => [
                        'key2' => 'something'
                    ]
                ]
            ],
            [
                [
                    'object'  => [
                        'key1' => '',
                        'key2' => 'something'
                    ]
                ]
            ],
            [
                [
                    'many2one' => ''
                ]
            ],
            [
                [
                    'many2one' => 123
                ]
            ]

        ];
    }

    private function assertEntityStateNoValidation($entity)
    {
        $this->assertTrue($entity->boolean);
        $this->assertEquals('char', $entity->char);
        $this->assertEquals(12, $entity->integer);
        $this->assertEquals(20.35, $entity->float);
        $this->assertEquals('2016-03-14', $entity->date);
        $this->assertEquals('2016-03-14 13:45:20', $entity->datetime);
        $this->assertEquals('dynamic-value', $entity->dynamic);
        $this->assertEquals('dynamic-value-db', $entity->dynamicDb);
        $this->assertEquals([1, 2, 3], $entity->arr->val());
        $this->assertEquals([
            'key1' => 'value',
            'key2' => 12
        ], $entity->object->val());
        $this->assertEquals('many2oneNew', $entity->many2oneNew->char);
        $this->assertEquals('many2oneExisting', $entity->many2oneExisting->char);
        $this->assertEquals(2, $entity->one2many->count());
        $this->assertEquals('one2many1', $entity->one2many[0]->char);
        $this->assertEquals('one2many2', $entity->one2many[1]->char);
        $this->assertEquals(2, $entity->many2many->count());
        $this->assertEquals('many2many1', $entity->many2many[0]->char);
        $this->assertEquals('many2many2', $entity->many2many[1]->char);
    }

    /*public function testFindOne()
    {
        $page = Page::findOne(['id' => self::$page->id]);
        $this->assertInstanceOf('Webiny\Component\Entity\EntityAbstract', $page);

        $page = Page::findOne(['title' => 'First blog post']);
        $this->assertInstanceOf('Webiny\Component\Entity\EntityAbstract', $page);

        $page = Page::findOne(['title' => 'NO TITLE']);
        $this->assertNull($page);
    }

    public function testPopulateValidation()
    {
        $page = self::$page;
        $data = [
            'title'  => 12,
            'author' => false
        ];
        $this->setExpectedException('\Webiny\Component\Entity\EntityException');
        $page->populate($data);
    }

    public function testRestrictException()
    {
        $page = self::$page;
        $this->setExpectedException('\Webiny\Component\Entity\EntityException');
        $page->author->delete();
    }

    public function testCascade()
    {
        $page = self::$page;
        $authorId = $page->author->id;

        $page->author->getAttribute('pages')->setOnDelete('cascade');
        $page->author->delete();

        $author = Author::findById($authorId);
        $this->assertNull($author);
    }

    public function testSetOnce()
    {
        $page = new Page();
        $page->title = 'Initial title';
        $page->save();

        $id = $page->id;

        // Completely remove current instance
        Entity::getInstance()->remove($page);

        // Load fresh instance from database
        $page = Page::findById($id);

        // Disable update of 'title' attribute
        $page->getAttribute('title')->setOnce()->setValue('New title');
        $this->assertEquals('Initial title', $page->title);

        $page->populate(['title' => 'Some title']);
        $this->assertEquals('Initial title', $page->title);

        // Try populating a null value attribute
        $page->getAttribute('settings')->setOnce()->setValue([]);
        $this->assertEquals([], $page->settings->val());

        // Try updating an attribute that has a value already assigned to it
        $page->settings = [
            1,
            2,
            3
        ];
        $this->assertEquals([], $page->settings->val());

        // Enable update of 'title' attribute
        $page->getAttribute('title')->setOnce(false)->setValue('New title');
        $this->assertEquals('New title', $page->title);
    }*/

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