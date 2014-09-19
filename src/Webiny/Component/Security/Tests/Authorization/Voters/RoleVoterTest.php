<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authorization\Voters;

use Webiny\Component\Security\Authorization\Voters\RoleVoter;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\Tests\Mocks\UserMock;

class RoleVoterTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsRoleTrue()
    {
        $voter = new RoleVoter();
        $this->assertTrue($voter->supportsRole(new Role("ROLE_MOCK")));
    }

    public function testSupportsRoleFalse()
    {
        $voter = new RoleVoter();
        $this->assertFalse($voter->supportsRole(new Role("MOCK")));
    }

    public function testSupportsUserClass()
    {
        $voter = new RoleVoter();
        $this->assertTrue($voter->supportsUserClass('UserMock'));
    }

    public function testVoteAccessGranted()
    {
        $user = new UserMock();
        $user->populate("test", "test", [new Role('ROLE_MOCK')], true);

        $voter = new RoleVoter();
        $this->assertSame(RoleVoter::ACCESS_GRANTED, $voter->vote($user, [new Role('ROLE_MOCK')]));
    }

    public function testVoteAccessDenied()
    {
        $user = new UserMock();
        $user->populate("test", "test", [new Role('ROLE_MOCK')], true);

        $voter = new RoleVoter();
        $this->assertSame(RoleVoter::ACCESS_DENIED, $voter->vote($user, [new Role('ROLE_ADMIN')]));
    }
}