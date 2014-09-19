<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Role;

use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\Role\RoleHierarchy;

class RoleHierarchyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $roleHierarchy = new RoleHierarchy(['ROLE_MOCK' => 'ROLE_USER']);
        $this->assertInstanceOf('\Webiny\Component\Security\Role\RoleHierarchy', $roleHierarchy);
    }

    public function testGetAccessibleRoles()
    {
        $roleHierarchy = new RoleHierarchy([
                                               'ROLE_USER'  => 'ROLE_EDITOR',
                                               'ROLE_ADMIN' => 'ROLE_USER',
                                               'ROLE_MOCK'  => 'ROLE_ADMIN'
                                           ]
        );

        $roles = [new Role('ROLE_EDITOR')];
        $this->assertCount(1, $roleHierarchy->getAccessibleRoles($roles));

        $roles = [new Role('ROLE_USER')];
        $this->assertCount(2, $roleHierarchy->getAccessibleRoles($roles));

        $roles = [new Role('ROLE_ADMIN')];
        $this->assertCount(3, $roleHierarchy->getAccessibleRoles($roles));

        $roles = [new Role('ROLE_MOCK')];
        $this->assertCount(4, $roleHierarchy->getAccessibleRoles($roles));
    }
}