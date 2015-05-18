<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityException;
use Webiny\Component\Entity\EntityPool;
use Webiny\Component\Entity\EntityTrait;
use Webiny\Component\Entity\Tests\Classes\Author;
use Webiny\Component\Entity\Tests\Classes\Comment;
use Webiny\Component\Entity\Tests\Classes\Label;
use Webiny\Component\Entity\Tests\Classes\Page;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class EntityTest extends PHPUnit_Framework_TestCase
{
    use EntityTrait, MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';
    const MONGO_CONFIG = '/MongoExampleConfig.yaml';

    /**
     * @var Page
     */
    private static $page;

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
        $page->getAttribute('publishOn')->setValue('2014-11-01');
        $page->getAttribute('remindOn')->setValue(time());
        $page->getAttribute('author')->setValue($author);
        $page->getAttribute('settings')->setValue([
                                                      'key1' => 'value1',
                                                      'key2' => ['key3' => 'value3']
                                                  ]
        );
        $page->getAttribute('comments')->add($comment);
        $page->getAttribute('labels')->add([
                                               $label,
                                               $label2
                                           ]
        );
        self::$page = $page;
    }

    public static function tearDownAfterClass()
    {
        /*self::mongo()->dropCollection('Author');
        self::mongo()->dropCollection('Page');
        self::mongo()->dropCollection('Comment');
        self::mongo()->dropCollection('Label');
        self::mongo()->dropCollection('Label2Page');*/
    }

    public function testEntity()
    {
        $page = self::$page;
        $this->assertInstanceOf('Webiny\Component\Entity\EntityAbstract', $page);
        $this->assertTrue($page->save());
        $this->assertInstanceOf('Webiny\Component\Entity\Tests\Classes\Author', $page->author);
        $this->assertEquals('First blog post', $page->labels[1]->pages[0]->title);

        /**
         * Remove this instance from pool so we fetch fresh data from database
         */
        EntityPool::getInstance()->remove($page);

        /**
         * Get recently saved Page instance and verify values
         * Must set to self because EntityPool 'remove()' method unsets reference
         */
        self::$page = $page = Page::findById($page->id);
        $this->assertEquals('First blog post', $page->title);
        $this->assertEquals(2, $page->labels->count());
        $this->assertEquals('Pavel Denisjuk', $page->author->name);
        $this->assertEquals('Best blog post ever!', $page->comments[0]->text);
        $this->assertEquals('value1', $page->settings['key1']);
        $this->assertEquals('value3', $page->settings['key2']['key3']);
        $this->assertEquals('seo', $page->labels[1]->label);

        // Test date attributes
        $createdOn = $page->getAttribute('createdOn')->getValue(true)->format('Y-m-d');
        $this->assertEquals(date('Y-m-d'), $createdOn);
        $pubishOn = $page->publishOn;
        $this->assertEquals('2014-11-01', $pubishOn);
        $remindOn = $page->remindOn;
        $this->assertEquals(date('Y-m-d'), $remindOn);

        // Test nested modification
        $page->getAttribute('settings')->set('key2.key3', 'changedKey3');
        $this->assertEquals('changedKey3', $page->getAttribute('settings')->get('key2.key3'));

        $page->getAttribute('labels')->remove($page->labels[0]);
        $this->assertEquals(1, $page->labels->count());

        $page->comments->delete();
        $this->assertEquals(0, $page->comments->count());
    }

    public function testFindOne()
    {
        $page = Page::findOne(['id' => self::$page->id]);
        $this->assertInstanceOf('Webiny\Component\Entity\EntityAbstract', $page);

        $page = Page::findOne(['title' => 'First blog post']);
        $this->assertInstanceOf('Webiny\Component\Entity\EntityAbstract', $page);

        $page = Page::findOne(['title' => 'NO TITLE']);
        $this->assertNull($page);
    }

    /**
     * This should cause 2 validation errors
     */
    public function testPopulateValidation()
    {
        $page = self::$page;
        $data = [
            'title' => 12,
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
        EntityPool::getInstance()->remove($page);

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
    }
}