<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\Tests\StdObject;

use Webiny\Component\StdLib\StdObject\UrlObject\UrlObject;

class UrlObjectTest extends \PHPUnit_Framework_TestCase
{

    private $url = 'http://www.webiny.com:666/some-page/some-subpage/?param1&param2=null
					&paramArray[]=somevalue&paramArray[]=somevalue2';

    private $url2 = 'http://www.webiny.com/path/?query=something';


    public function testConstructor()
    {
        $u = new UrlObject($this->url);
    }

    /**
     * @expectedException \Webiny\Component\StdLib\StdObject\StdObjectException
     * @expectedExceptionMessage Unable to parse
     */
    public function testConstuctor2()
    {
        $u = new UrlObject(false);
    }

    public function testGetHost()
    {
        $u = new UrlObject($this->url);

        $this->assertSame('www.webiny.com', $u->getHost());
    }

    public function testGetScheme()
    {
        $u = new UrlObject($this->url);

        $this->assertSame('http', $u->getScheme());
    }

    public function testGetPort()
    {
        $u = new UrlObject($this->url);

        $this->assertSame(666, $u->getPort());
    }

    public function testGetDomain()
    {
        $u = new UrlObject($this->url);

        $this->assertSame('http://www.webiny.com', $u->getDomain());
    }

    public function testGetPath()
    {
        $u = new UrlObject($this->url);

        $this->assertSame('/some-page/some-subpage/', $u->getPath());
    }

    public function testSetters()
    {
        $u = new UrlObject($this->url2);

        $u->setScheme('ftp')
          ->setHost('google.com////')
          ->setPath('new-path/over-me')
          ->setPort(45)
          ->setQuery(['name' => 'John']);

        $this->assertSame('ftp://google.com:45/new-path/over-me?name=John', $u->val());
    }

    public function testSetters2()
    {
        $u = new UrlObject($this->url2);

        $u->setQuery(['name' => 'John'], true);

        $this->assertSame('http://www.webiny.com/path/?query=something&name=John', $u->val());
    }

    public function testSetters3()
    {
        $u = new UrlObject($this->url2);

        $u->setQuery([
                         'name'  => 'John',
                         'query' => 'nothing'
                     ], true
        );

        $this->assertSame('http://www.webiny.com/path/?query=nothing&name=John', $u->val());
    }
}