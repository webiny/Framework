<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authorization\Voters;

use Webiny\Component\Security\Authorization\Voters\AuthenticationVoter;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\Tests\Mocks\UserMock;

class AuthenticationVoterTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsRole()
    {
        $voter = new AuthenticationVoter();
        $this->assertTrue($voter->supportsRole(new Role("ROLE_MOCK")));
    }

    public function testSupportsUserClass()
    {
        $voter = new AuthenticationVoter();
        $this->assertTrue($voter->supportsUserClass('UserMock'));
    }

    public function testVoteAccessGranted()
    {
        $user = new UserMock();
        $user->populate("test", "test", [], true);

        $voter = new AuthenticationVoter();
        $this->assertSame(AuthenticationVoter::ACCESS_GRANTED, $voter->vote($user, []));
    }

    public function testVoteAccessDenied()
    {
        $user = new UserMock();
        $user->populate("test", "test", [], false);

        $voter = new AuthenticationVoter();
        $this->assertSame(AuthenticationVoter::ACCESS_DENIED, $voter->vote($user, []));
    }
}