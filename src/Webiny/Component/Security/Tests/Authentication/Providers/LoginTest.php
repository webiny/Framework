<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authentication\Providers;

use Webiny\Component\Security\Authentication\Providers\Login;

class LoginTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $login = new Login('username', 'password', true);
        $this->assertInstanceOf('\Webiny\Component\Security\Authentication\Providers\Login', $login);
    }

    public function testSetAttribute()
    {
        $login = new Login('username', 'password', true);
        $login->setAttribute('aname', 'avalue');
    }

    public function testGetAttribute()
    {
        $login = new Login('username', 'password', true);
        $login->setAttribute('aname', 'avalue');
        $login->setAttribute('bname', 'bvalue');

        $this->assertSame('avalue', $login->getAttribute('aname'));
        $this->assertSame('bvalue', $login->getAttribute('bname'));

        $login->setAttribute('aname', 'cvalue');
        $this->assertSame('cvalue', $login->getAttribute('aname'));

        $this->assertNull($login->getAttribute('doesnt exist'));
    }

    public function testGetUsername()
    {
        $login = new Login('username', 'password', true);
        $this->assertSame('username', $login->getUsername());
    }

    public function testGetPassword()
    {
        $login = new Login('username', 'password', true);
        $this->assertSame('password', $login->getPassword());
    }

    public function testSetTimeZoneOffset()
    {
        $login = new Login('username', 'password', true);
        $login->setTimeZoneOffset(-9);
    }

    public function testGetTimeZoneOffset()
    {
        $login = new Login('username', 'password', true);

        $this->assertSame(0, $login->getTimeZoneOffset());

        $login->setTimeZoneOffset(-9);

        $this->assertSame(-9, $login->getTimeZoneOffset());
    }

    public function testGetRememberMe()
    {
        $login = new Login('username', 'password', true);
        $this->assertTrue($login->getRememberMe());

        $login = new Login('username', 'password');
        $this->assertFalse($login->getRememberMe());

        $login = new Login('username', 'password', false);
        $this->assertFalse($login->getRememberMe());
    }

    public function testSetAuthProviderName()
    {
        $login = new Login('username', 'password', true);
        $login->setAuthProviderName('Facebook');
    }

    public function testGetAuthProviderName()
    {
        $login = new Login('username', 'password', true);
        $login->setAuthProviderName('Facebook');

        $this->assertSame('Facebook', $login->getAuthProviderName());
    }
}