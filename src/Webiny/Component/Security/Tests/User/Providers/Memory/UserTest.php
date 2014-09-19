<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\User\Providers\Memory;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\User\Providers\Memory\User;

class UserTest extends \PHPUnit_Framework_TestCase
{

    public function testAuthenticateTrue()
    {
        $user = new User();
        $user->populate('kent', 'superman', [new Role('ROLE_SUPERHERO')]);

        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock', '');
        $login = $login = new Login('kent', 'superman');

        $this->assertTrue($user->authenticate($login, $encoder));
    }

    public function testAuthenticateFalse()
    {
        $user = new User();
        $user->populate('kent', 'batman', [new Role('ROLE_SUPERHERO')]);

        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock', '');
        $login = $login = new Login('kent', 'superman');

        $this->assertFalse($user->authenticate($login, $encoder));
    }

}