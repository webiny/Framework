<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Tests;

use Webiny\Component\OAuth2\OAuth2;


class OAuth2Test extends \PHPUnit_Framework_TestCase
{

    const CONFIG = '/ExampleConfig.yaml';

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testConstructor($oauth2)
    {
        $this->assertInstanceOf('\Webiny\Component\OAuth2\OAuth2', $oauth2);
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testRequest($oauth2)
    {
        $this->assertInstanceOf('\Webiny\Component\OAuth2\ServerAbstract', $oauth2->request());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testGetClientId($oauth2)
    {
        $this->assertSame('1', $oauth2->getClientId());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testGetClientSecret($oauth2)
    {
        $this->assertSame('secret', $oauth2->getClientSecret());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testRequestAccessToken($oauth2)
    {
        $this->assertSame('access_token', $oauth2->requestAccessToken());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testGetAccessToken($oauth2)
    {
        $this->assertSame('access_token', $oauth2->getAccessToken());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testGetRedirectUri($oauth2)
    {
        $this->assertSame('redirect_uri', $oauth2->getRedirectURI());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testSetAccessToken($oauth2)
    {
        $oauth2->setAccessToken('new_token');
        $this->assertSame('new_token', $oauth2->getAccessToken());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testSetGetCertificate($oauth2)
    {
        $oauth2->setCertificate('dummy_path');
        $this->assertSame('dummy_path', $oauth2->getCertificate());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testSetGetScope($oauth2)
    {
        $oauth2->setScope('test_scope');
        $this->assertSame('test_scope', $oauth2->getScope());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testSetGetState($oauth2)
    {
        $oauth2->setState('test_state');
        $this->assertSame('test_state', $oauth2->getState());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testGetAccessTokenName($oauth2)
    {
        $this->assertSame('test_token', $oauth2->getAccessTokenName());
    }

    /**
     * @param $oauth2 OAuth2
     *
     * @dataProvider dataProvider
     */
    public function testGetAuthenticationUrl($oauth2)
    {
        $oauth2->setScope('test_scope');
        $oauth2->setState('test_state');

        $expected = 'http://www.webiny.com/oa2/?client_id=1&redirect_uri=redirect_uri&scope=test_scope&state=test_state';

        $this->assertSame($expected, $oauth2->getAuthenticationUrl());
    }

    public function testGetConfig()
    {
        OAuth2::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
        $this->assertSame('\Webiny\Component\OAuth2\Bridge\League\OAuth2', OAuth2::getConfig()->Bridge);
        $this->assertSame(123, OAuth2::getConfig()->Facebook->ClientId);
    }

    public function dataProvider()
    {
        OAuth2::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        $mock = new Mocks\OAuth2BridgeMock('1', 'secret', 'redirect_uri');
        $oauth2 = new OAuth2($mock);

        return [
            [$oauth2]
        ];
    }

}