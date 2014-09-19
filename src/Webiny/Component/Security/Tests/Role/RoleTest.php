<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Role;


use Webiny\Component\Security\Role\Role;

class RoleTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $role = new Role('ROLE_MOCK');
        $this->assertInstanceOf('\Webiny\Component\Security\Role\Role', $role);
    }

    public function testGetRole()
    {
        $role = new Role('ROLE_MOCK');
        $this->assertSame('ROLE_MOCK', $role->getRole());
    }

}