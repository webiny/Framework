<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authentication\Providers\Form;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Session;
use Webiny\Component\Security\Authentication\Providers\Http\Http;

class HttpTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @runInSeparateProcess
     */
    public function testConstructor()
    {
        $this->assertInstanceOf('\Webiny\Component\Security\Authentication\Providers\Http\Http', new Http());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetLoginObject()
    {
        Session::getInstance()->save('username', 'un');
        Session::getInstance()->save('password', 'pw');

        $http = new Http();
        $c = new ConfigObject([]);
        $login = $http->getLoginObject($c);
        $this->assertInstanceOf('\Webiny\Component\Security\Authentication\Providers\Login', $login);
        $this->assertSame('un', $login->getUsername());
        $this->assertSame('pw', $login->getPassword());
    }

    /**
     * @expectedException \Webiny\Component\Security\Authentication\Providers\Http\HttpException
     * @runInSeparateProcess
     */
    public function testTriggerLoginFailed()
    {
        $http = new Http();
        $http->setExitTrigger('exception');
        $c = new ConfigObject([]);
        $http->getLoginObject($c);
    }

    /**
     * @runInSeparateProcess
     */
    public function testTriggerLogin()
    {
        // mock server vars
        $_SERVER = [
            'PHP_AUTH_USER' => 'name',
            'PHP_AUTH_PW'   => 'pass'
        ];

        $http = new Http();
        $http->setExitTrigger('exception');
        $c = new ConfigObject([]);
        $http->triggerLogin($c);

        $this->assertSame('name', Session::getInstance()->get('username'));
        $this->assertSame('pass', Session::getInstance()->get('password'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testInvalidLoginProvidedCallback()
    {
        Session::getInstance()->save('username', 'uname');
        Session::getInstance()->save('password', 'pw');

        $this->assertSame('uname', Session::getInstance()->get('username'));
        $this->assertSame('pw', Session::getInstance()->get('password'));

        $http = new Http();
        $http->invalidLoginProvidedCallback();

        $this->assertSame(null, Session::getInstance()->get('username'));
        $this->assertSame(null, Session::getInstance()->get('password'));
        $this->assertSame('true', Session::getInstance()->get('login_retry'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogoutCallback()
    {
        Session::getInstance()->save('username', 'uname');
        Session::getInstance()->save('password', 'pw');

        $this->assertSame('uname', Session::getInstance()->get('username'));
        $this->assertSame('pw', Session::getInstance()->get('password'));

        $http = new Http();
        $http->logoutCallback();

        $this->assertSame(null, Session::getInstance()->get('username'));
        $this->assertSame(null, Session::getInstance()->get('password'));
        $this->assertSame(null, Session::getInstance()->get('login_retry'));
        $this->assertSame('true', Session::getInstance()->get('logout'));
    }
}