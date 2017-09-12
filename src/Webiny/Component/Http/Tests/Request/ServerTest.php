<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Request;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Request\Server;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/../ExampleConfig.yaml');

        $_SERVER = [
            'USER'                 => 'webiny',
            'HOME'                 => '/home/webiny',
            'FCGI_ROLE'            => 'RESPONDER',
            'QUERY_STRING'         => 'batman=one&superman=two',
            'REQUEST_METHOD'       => 'GET',
            'CONTENT_TYPE'         => '',
            'CONTENT_LENGTH'       => '',
            'SCRIPT_FILENAME'      => '/var/www/projects/webiny/Public/index.php',
            'SCRIPT_NAME'          => '/index.php',
            'REQUEST_URI'          => '/batman-is-better-than-superman/?batman=one&superman=two',
            'DOCUMENT_URI'         => '/index.php',
            'DOCUMENT_ROOT'        => '/var/www/projects/webiny/Public',
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'GATEWAY_INTERFACE'    => 'CGI/1.1',
            'SERVER_SOFTWARE'      => 'nginx/1.1.19',
            'REMOTE_ADDR'          => '192.168.58.1',
            'REMOTE_PORT'          => '63468',
            'SERVER_ADDR'          => '192.168.58.20',
            'SERVER_PORT'          => '443',
            'SERVER_NAME'          => 'admin.w3.com',
            'HTTPS'                => 'on',
            'REDIRECT_STATUS'      => '200',
            'HTTP_HOST'            => 'admin.w3.com',
            'HTTP_CONNECTION'      => 'keep-alive',
            'HTTP_CACHE_CONTROL'   => 'max-age=0',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_ACCEPT_CHARSET'  => 'utf-8',
            'HTTP_USER_AGENT'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.49 Safari/537.36',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,hr;q=0.6,sr;q=0.4,de;q=0.2,bs;q=0.2',
            'PHP_SELF'             => '/index.php',
            'REQUEST_TIME_FLOAT'   => 1401828237.8094151,
            'REQUEST_TIME'         => 1401828237,
        ];
    }

    public function testConstructor()
    {
        $server = new Server();
        $this->assertInstanceOf(Server::class, $server);
    }

    public function testGet()
    {
        $server = new Server();
        $this->assertSame("webiny", $server->get("USER"));
        $this->assertSame("/index.php", $server->get("PHP_SELF"));
        $this->assertFalse($server->get("doesnt_exist"));
        $this->assertFalse($server->get("user"));
    }

    public function testGatewayInterface()
    {
        $server = new Server();
        $this->assertSame("CGI/1.1", $server->gatewayInterface());
    }

    public function testServerIpAddress()
    {
        $server = new Server();
        $this->assertSame("192.168.58.20", $server->serverIpAddress());
    }

    public function testServerName()
    {
        $server = new Server();
        $this->assertSame("admin.w3.com", $server->serverName());
    }

    public function testServerSoftware()
    {
        $server = new Server();
        $this->assertSame("nginx/1.1.19", $server->serverSoftware());
    }

    public function testServerProtocol()
    {
        $server = new Server();
        $this->assertSame("HTTP/1.1", $server->serverProtocol());
    }

    public function testRequestMethod()
    {
        $server = new Server();
        $this->assertSame("GET", $server->requestMethod());
    }

    public function testRequestTime()
    {
        $server = new Server();
        $this->assertSame(1401828237, $server->requestTime());
        $this->assertNotSame(1401828237.8094151, $server->requestTime());
        $this->assertSame(1401828237.8094151, $server->requestTime(true));
    }

    public function testQueryString()
    {
        $server = new Server();
        $this->assertSame("batman=one&superman=two", $server->queryString());
    }

    public function testDocumentRoot()
    {
        $server = new Server();
        $this->assertSame("/var/www/projects/webiny/Public", $server->documentRoot());
    }

    public function testHttpAccept()
    {
        $server = new Server();
        $this->assertSame("text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8", $server->httpAccept());
    }

    public function testHttpAcceptCharset()
    {
        $server = new Server();
        $this->assertSame("utf-8", $server->httpAcceptCharset());
    }

    public function testHttpAcceptEncoding()
    {
        $server = new Server();
        $this->assertSame("gzip,deflate,sdch", $server->httpAcceptEncoding());
    }

    public function testHttpAcceptLanguage()
    {
        $server = new Server();
        $this->assertSame("en-US,en;q=0.8,hr;q=0.6,sr;q=0.4,de;q=0.2,bs;q=0.2", $server->httpAcceptLanguage());
    }

    public function testHttpConnection()
    {
        $server = new Server();
        $this->assertSame("keep-alive", $server->httpConnection());
    }

    public function testHttpHost()
    {
        $server = new Server();
        $this->assertSame("admin.w3.com", $server->httpHost());
    }

    public function testHttpUserAgent()
    {
        $server = new Server();
        $this->assertSame("Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.49 Safari/537.36",
            $server->httpUserAgent());
    }

    public function testHttps()
    {
        $server = new Server();
        $this->assertSame("on", $server->https());
    }

    public function testRemoteAddress()
    {
        $server = new Server();
        $this->assertSame("192.168.58.1", $server->remoteAddress());
    }

    public function testRemotePort()
    {
        $server = new Server();
        $this->assertSame("63468", $server->remotePort());
    }

    public function testScriptFilename()
    {
        $server = new Server();
        $this->assertSame("/var/www/projects/webiny/Public/index.php", $server->scriptFilename());
    }

    public function testServerPort()
    {
        $server = new Server();
        $this->assertSame("443", $server->serverPort());
    }

    public function testScriptName()
    {
        $server = new Server();
        $this->assertSame("/index.php", $server->scriptName());
    }

    public function testRequestUri()
    {
        $server = new Server();
        $this->assertSame("/batman-is-better-than-superman/?batman=one&superman=two", $server->requestUri());
    }


}