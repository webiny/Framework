<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Tests;

use Webiny\Component\TwitterOAuth\Tests\Mocks\TwitterOAuthMock;
use Webiny\Component\TwitterOAuth\TwitterOAuth;

class TwitterOAuthTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $config = TwitterOAuth::getConfig();

        $this->assertSame('cId', $config->get('MyTwitterApp.ClientId'));
        $this->assertSame('cSecret', $config->get('MyTwitterApp.ClientSecret'));
    }

    /**
     * @param TwitterOAuth $instance
     *
     * @dataProvider dataProvider
     */
    public function testConstructor(TwitterOAuth $instance)
    {
        $this->assertInstanceOf(TwitterOAuth::class, $instance);
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
        TwitterOAuth::setConfig(__DIR__ . '/ExampleConfig.yaml');
        $config = TwitterOAuth::getConfig();

        // create bridge
        $bridge = new \Webiny\Component\TwitterOAuth\Bridge\League\TwitterOAuth($config->get('MyTwitterApp.ClientId'),
                                                                                $config->get('MyTwitterApp.ClientSecret'),
                                                                                '/'
        );

        // replace the \TwitterOAuth instance with mock
        $bridge = new TwitterOAuthMock($config->get('MyTwitterApp.ClientId'),
                                     $config->get('MyTwitterApp.ClientSecret'),
                                     '/');
        //$bridge->setDriverInstance($mock);

        // create TwitterOAuth instance
        $instance = new TwitterOAuth($bridge);

        return [[$instance]];
    }
}