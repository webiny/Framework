<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Tests\Bridge\TwitterOAuth;

use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth\TwitterOAuth;
use Webiny\Component\TwitterOAuth\Tests\Mocks\TwitterOAuthMock;

class TwitterOAuthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testConstructor(TwitterOAuth $instance)
    {
        $this->assertInstanceOf('\Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth\TwitterOAuth', $instance);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetClientId(TwitterOAuth $instance)
    {
        $this->assertSame('cID1231', $instance->getClientId());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetClientSecret(TwitterOAuth $instance)
    {
        $this->assertSame('cSEC123', $instance->getClientSecret());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetRedirectUri(TwitterOAuth $instance)
    {
        $this->assertSame('/', $instance->getRedirectUri());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetDriverInstance(TwitterOAuth $instance)
    {
        $this->assertInstanceOf('\TwitterOAuth', $instance->getDriverInstance());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetRequestToken(TwitterOAuth $instance)
    {
        $this->assertSame('rToken', $instance->getRequestToken());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetResponseCode(TwitterOAuth $instance)
    {
        $this->assertSame(200, $instance->getResponseCode());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetAuthorizeUrl(TwitterOAuth $instance)
    {
        $this->assertSame('http://www.twitter.com/authMe', $instance->getAuthorizeUrl('token'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetAccessToken(TwitterOAuth $instance)
    {
        $result = [
            "oauth_token"        => "the-access-token",
            "oauth_token_secret" => "the-access-secret",
            "user_id"            => "5555",
            "screen_name"        => "WebinyPlatform"
        ];
        $this->assertSame($result, $instance->getAccessToken('v'));
    }

    public function dataProvider()
    {
        $clientId = 'cID1231';
        $clientSecret = 'cSEC123';
        $instance = new TwitterOAuth($clientId, $clientSecret, '/');

        // replace the \TwitterOAuth instance with mock
        $mock = new TwitterOAuthMock($clientId, $clientSecret);
        $instance->setDriverInstance($mock);


        return [[$instance]];
    }

}