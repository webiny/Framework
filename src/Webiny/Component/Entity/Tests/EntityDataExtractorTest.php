<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityDataExtractor;
use Webiny\Component\Entity\EntityPool;
use Webiny\Component\Entity\EntityTrait;
use Webiny\Component\Entity\Tests\Classes\Author;
use Webiny\Component\Entity\Tests\Classes\Comment;
use Webiny\Component\Entity\Tests\Classes\Label;
use Webiny\Component\Entity\Tests\Classes\Page;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class EntityDataExtractorTest extends PHPUnit_Framework_TestCase
{
    use EntityTrait, MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';
    const MONGO_CONFIG = '/MongoExampleConfig.yaml';

    /**
     * @var Page
     */
    private static $_page;

    public static function setUpBeforeClass()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::MONGO_CONFIG));
        Entity::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        /**
         * Make sure no collections exist
         */
        self::mongo()->dropCollection('Author');
        self::mongo()->dropCollection('Page');
        self::mongo()->dropCollection('Comment');
        self::mongo()->dropCollection('Label');
        self::mongo()->dropCollection('Label2Page');

        /**
         * Create some test entity instances
         */
        $page = new Page();
        $author = new Author();
        $comment = new Comment();
        $label = new Label();
        $label2 = new Label();

        /**
         * Try simple assignment (should trigger assign via magic method)
         */
        $author->name = 'Pavel Denisjuk';

        /**
         * Assign using regular way
         */
        $comment->getAttribute('text')->setValue('Best blog post ever!');
        $label->getAttribute('label')->setValue('marketing');
        $label2->getAttribute('label')->setValue('seo');
        $page->getAttribute('title')->setValue('First blog post');
        $page->getAttribute('author')->setValue($author);
        $page->getAttribute('comments')->add($comment);
        $page->getAttribute('settings')->setValue([
                                                      'key1' => 'value1',
                                                      'key2' => ['key3' => 'value3']
                                                  ]
        );
        $page->getAttribute('labels')->add([
                                               $label,
                                               $label2
                                           ]
        );
        self::$_page = $page;
    }

    public static function tearDownAfterClass()
    {
        self::mongo()->dropCollection('Author');
        self::mongo()->dropCollection('Page');
        self::mongo()->dropCollection('Comment');
        self::mongo()->dropCollection('Label');
        self::mongo()->dropCollection('Label2Page');
    }

    function testEntity()
    {
        $page = self::$_page;
        $page->save();

        $data = $page->toArray();
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertArrayHasKey('name', $data['author']);
        $this->assertArrayNotHasKey('labels', $data);
        $this->assertArrayNotHasKey('comments', $data);

        $data = $page->toArray('title,comments.text,comments.id,labels');
        $this->assertArrayHasKey('comments', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('label', $data['labels'][0]);
        $this->assertArrayNotHasKey('author', $data);
        $this->assertEquals('marketing', $data['labels'][0]['label']);
    }

    function testParentEntity()
    {
        // Create parent page
        $parentPage = new Page();
        $parentPage->title = 'Parent page';
        $parentPage->save();

        $page = self::$_page;
        $page->parent = $parentPage;
        $page->save();

        $parentPage->parent = $page;
        $parentPage->save();

        $data = $page->toArray('*,labels');
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertArrayHasKey('name', $data['author']);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayNotHasKey('comments', $data);

        $data = $page->toArray('title,comments.text,comments.id,labels');
        $this->assertArrayHasKey('comments', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('label', $data['labels'][0]);
        $this->assertArrayNotHasKey('author', $data);
    }
}