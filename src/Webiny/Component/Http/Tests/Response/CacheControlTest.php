<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Response;

use Webiny\Component\Http\Http;
use Webiny\Component\Http\Response\CacheControl;
use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject;

class CacheControlTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $cc = new CacheControl();
        $this->assertInstanceOf(CacheControl::class, $cc);
    }

    public function testSetAsDontCache()
    {
        $cc = new CacheControl();
        $cc->setAsDontCache();

        $dontCacheCacheControl = [
            'Expires'       => -1,
            'Pragma'        => 'no-cache',
            'Cache-Control' => 'no-cache, must-revalidate'
        ];

        $this->assertSame($dontCacheCacheControl, $cc->getCacheControl());
    }

    public function testSetAsCache()
    {
        $cc = new CacheControl();
        $dt = new DateTimeObject();

        $cc->setAsCache($dt->add('3 hours'));
        $ccHeaders = $cc->getCacheControl();

        $maxAge = $dt->getTimestamp() - time();
        $this->assertSame('private, max-age=' . $maxAge, $ccHeaders['Cache-Control']);

        $dtFormatted = date('D, d M Y H:i:s', $dt->getTimestamp());
        $this->assertSame($dtFormatted . ' GMT', $ccHeaders['Expires']);
    }

    public function testGetCacheControl()
    {
        $cc = new CacheControl();
        $cc->setAsDontCache();

        $dontCacheCacheControl = [
            'Expires'       => -1,
            'Pragma'        => 'no-cache',
            'Cache-Control' => 'no-cache, must-revalidate'
        ];

        $this->assertSame($dontCacheCacheControl, $cc->getCacheControl());
    }

    public function testSetCacheControl()
    {
        $cc = new CacheControl();
        $ccValue = ['Cache-Control' => 'cache, public'];
        $cc->setCacheControl($ccValue);

        $this->assertSame($ccValue, $cc->getCacheControl());
    }

    /**
     * @expectedException \Webiny\Component\Http\Response\ResponseException
     */
    public function testSetCacheControlException()
    {
        $cc = new CacheControl();
        $ccValue = [
            'Cache-Control' => 'cache, public',
            'foo'           => 'value'
        ];
        $cc->setCacheControl($ccValue);
    }

    public function testSetCacheControlEntry()
    {
        $cc = new CacheControl();
        $cc->setCacheControlEntry('Cache-Control', 'cache, public');

        $this->assertSame(['Cache-Control' => 'cache, public'], $cc->getCacheControl());
    }

    /**
     * @expectedException \Webiny\Component\Http\Response\ResponseException
     */
    public function testSetCacheControlEntryException()
    {
        $cc = new CacheControl();
        $cc->setCacheControlEntry('foo', 'value');
    }
}