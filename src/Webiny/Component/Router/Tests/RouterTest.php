<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Tests;


use Webiny\Component\Cache\Cache;
use Webiny\Component\Config\Config;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Request;
use Webiny\Component\Router\Router;

/**
 * Class RouterTest
 * @package Webiny\Component\Router\Tests
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{

    const CONFIG = '/ExampleConfig.yaml';

    public function setUp()
    {
        Router::getInstance()->prependRoutes(Config::getInstance()->yaml(__DIR__.'/ExampleConfig.yaml')->get('Router.Routes'));
        Request::getInstance()->setCurrentUrl('http://www.webiny.com/');
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchTrue($url, $expectedResult)
    {
        $result = Router::getInstance()->match($url);

        $this->assertSame($expectedResult['callback'], $result->getCallback());
        $this->assertSame($expectedResult['params'], $result->getParams());
    }

    public function testMatchFalse()
    {
        $router = Router::getInstance();
        $result = $router->match('doesnt-exist');
        $this->assertFalse($result);
    }

    /**
     * @dataProvider generatorProvider
     */
    public function testGenerator($route, $params, $expectedUrl)
    {
        $url = Router::getInstance()->generate($route, $params);
        $this->assertSame($expectedUrl, $url);
    }

    /**
     * @expectedException \Webiny\Component\Router\RouterException
     * @expectedExceptionMessage Unknown route
     */
    public function testGeneratorException()
    {
        Router::getInstance()->generate('doesnt exist');
    }

    /**
     * @expectedException \Webiny\Component\Router\RouterException
     * @expectedExceptionMessage Some parameters are missing
     */
    public function testGeneratorException2()
    {
        Router::getInstance()->generate('BlogTag');
    }

    public function testSetGetCacheAsCacheObject()
    {
        $nullCache = Cache::BlackHole();
        Router::getInstance()->setCache($nullCache);
        $this->assertInstanceOf('\Webiny\Component\Cache\CacheStorage', Router::getInstance()->getCache());
    }

    /**
     * @expectedException \Webiny\Component\ServiceManager\ServiceManagerException
     */
    public function testSetGetCacheAsCacheServiceException()
    {
        Router::getInstance()->setCache('TestCache2');
    }

    public function testSetGetCacheToFalse()
    {
        Router::getInstance()->setCache(false);
        $this->assertFalse(Router::getInstance()->getCache());
    }

    public function testAppendRoutes()
    {
        $routeToAppend = [
            'Test' => [
                'Path'     => 'some-dummy-path/{id}',
                'Callback' => 'TestCallback'
            ]
        ];
        Router::getInstance()->appendRoutes(new ConfigObject($routeToAppend));
        $this->assertSame('http://www.webiny.com/some-dummy-path/12',
                          Router::getInstance()->generate('Test', ['id' => 12])
        );
    }

    public function matchProvider()
    {
        return [
            [
                'http://www.webiny.com/blog/',
                [
                    'callback' => 'MyApp\Blog\index',
                    'params'   => []
                ]
            ],
            [
                'http://www.webiny.com/blog/tag-slash/php/',
                [
                    'callback' => 'MyApp\Blog\index',
                    'params'   => [
                        'tag' => 'php'
                    ]
                ]
            ],
            [
                'http://www.webiny.com/blog/tag/html5',
                [
                    'callback' => 'MyApp\Blog\showTag',
                    'params'   => [
                        'tag' => 'html5'
                    ]
                ]
            ],
            [
                'http://www.webiny.com/blog/post/marketing-intro/123',
                [
                    'callback' => 'MyApp\Blog\showPost',
                    'params'   => [
                        'slug' => 'marketing-intro',
                        'id'   => '123'
                    ]
                ]
            ],
            [
                'http://www.webiny.com/blog/authors',
                [
                    'callback' => 'MyApp\Blog\showAuthorPosts',
                    'params'   => [
                        'author' => 'webiny'
                    ]
                ]
            ]

        ];
    }

    public function generatorProvider()
    {
        return [
            [
                'BlogTag',
                [
                    'tag' => 'some-tag'
                ],
                'http://www.webiny.com/blog/tag/some-tag'
            ],
            [
                'BlogPost',
                [
                    'slug' => 'some-post',
                    'id'   => '32'
                ],
                'http://www.webiny.com/blog/post/some-post/32'
            ],
            [
                'BlogAuthor',
                [],
                'http://www.webiny.com/blog/authors/webiny'
            ],
            [
                'BlogAuthor',
                [
                    'author' => 'sven'
                ],
                'http://www.webiny.com/blog/authors/sven'
            ],
            [
                'Blog',
                [],
                'http://www.webiny.com/blog'
            ]
        ];
    }
}