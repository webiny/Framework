<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authentication\Providers\OAuth2;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Request;
use Webiny\Component\Http\Session;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2;


class OAuth2Test extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        \Webiny\Component\OAuth2\OAuth2::setConfig(__DIR__ . '/OAuth2ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $instance = new OAuth2('Facebook', ['ROLE_ADMIN']);
        $this->assertInstanceOf(OAuth2::class, $instance);
    }

    /**
     * @expectedException \Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2Exception
     */
    public function testConstructorException()
    {
        new OAuth2('Doesnt exist', ['ROLE_ADMIN']);
    }

    /**
     * @expectedException \Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2Exception
     * @expectedExceptionMessage Redirecting
     */
    public function testGetLoginObjectStep1()
    {
        $oauth2 = new OAuth2('Facebook', ['ROLE_ADMIN']);
        $oauth2->setExitTrigger(OAuth2::EXIT_TRIGGER_EXCEPTION);
        $oauth2->getLoginObject(new ConfigObject([]));
    }

    /**
     * @expectedException \Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2Exception
     * @expectedExceptionMessage The state parameter
     */
    public function testGetLoginObjectStep1Dot1()
    {
        Request::deleteInstance();
        Session::getInstance()->save('oauth_token', '123');
        $_GET['code'] = 'some code';

        $oauth2 = new OAuth2('Facebook', ['ROLE_ADMIN']);
        $oauth2->setExitTrigger(OAuth2::EXIT_TRIGGER_EXCEPTION);
        $oauth2->getLoginObject(new ConfigObject([]));
    }

    public function testGetLoginObjectValidState()
    {
        Request::deleteInstance();
        Session::getInstance()->save('oauth_token', '123');
        Session::getInstance()->save('oauth_state', 'state-id');
        $_GET['code'] = 'some code';
        $_GET['state'] = 'state-id';

        $oauth2 = new OAuth2('Facebook', ['ROLE_ADMIN']);
        $oauth2->setExitTrigger(OAuth2::EXIT_TRIGGER_EXCEPTION);
        $result = $oauth2->getLoginObject(new ConfigObject([]));
        $this->assertInstanceOf(Login::class, $result);
    }
}