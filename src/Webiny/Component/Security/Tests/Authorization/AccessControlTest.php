<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authorization;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Security\Authorization\AccessControl;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\Tests\Mocks\UserMock;

class AccessControlTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $user = new UserMock();
        $config = new ConfigObject(['DecisionStrategy' => 'affirmative']);
        $instance = new AccessControl($user, $config);

        $this->assertInstanceOf('\Webiny\Component\Security\Authorization\AccessControl', $instance);
    }

    /**
     * @expectedException \Webiny\Component\Security\Authorization\AccessControlException
     * @expectedExceptionMessage Invalid access control decision strategy
     */
    public function testConstructorDecisionStrategyException()
    {
        $user = new UserMock();
        $config = new ConfigObject(['DecisionStrategy' => 'test']);
        new AccessControl($user, $config);
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsUserAllowedAccessNoRolesDefined()
    {
        $user = new UserMock();
        $config = new ConfigObject(['DecisionStrategy' => 'unanimous']);
        $instance = new AccessControl($user, $config);

        $this->assertTrue($instance->isUserAllowedAccess());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsUserAllowedAccessNoRolesRequired()
    {
        // lets mock the address to one that doesn't match any rules
        $_SERVER = [
            'REQUEST_URI' => '/some-page/',
            'SERVER_NAME' => 'admin.w3.com'
        ];

        $user = new UserMock();
        $user->populate('test', 'test', [new Role('ROLE_MOCK')], true);
        $config = new ConfigObject([
                                       'DecisionStrategy' => 'unanimous',
                                       'Rules'            => [
                                           [
                                               'Path'  => '/^\/about/',
                                               'Roles' => 'ROLE_GOD'
                                           ]
                                       ]
                                   ]
        );
        $instance = new AccessControl($user, $config);

        $this->assertTrue($instance->isUserAllowedAccess());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsUserAllowedAccessRoleRequiredButDenied()
    {
        // lets mock the address to one that doesn't match any rules
        $_SERVER = [
            'REQUEST_URI' => '/about/',
            'SERVER_NAME' => 'admin.w3.com'
        ];

        $user = new UserMock();
        $user->populate('test', 'test', [new Role('ROLE_MOCK')], true);
        $config = new ConfigObject([
                                       'DecisionStrategy' => 'unanimous',
                                       'Rules'            => [
                                           [
                                               'Path'  => '/^\/about/',
                                               'Roles' => 'ROLE_GOD'
                                           ]
                                       ]
                                   ]
        );
        $instance = new AccessControl($user, $config);

        $this->assertFalse($instance->isUserAllowedAccess());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsUserAllowedAccessRoleRequiredButGranted()
    {
        // lets mock the address to one that doesn't match any rules
        $_SERVER = [
            'REQUEST_URI' => '/about/',
            'SERVER_NAME' => 'admin.w3.com'
        ];

        $user = new UserMock();
        $user->populate('test', 'test', [new Role('ROLE_MOCK')], true);
        $config = new ConfigObject([
                                       'DecisionStrategy' => 'unanimous',
                                       'Rules'            => [
                                           [
                                               'Path'  => '/^\/about/',
                                               'Roles' => 'ROLE_MOCK'
                                           ]
                                       ]
                                   ]
        );
        $instance = new AccessControl($user, $config);

        $this->assertTrue($instance->isUserAllowedAccess());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsUserAllowedAccessDecisionStrategyAffirmative()
    {
        // lets mock the address to one that doesn't match any rules
        $_SERVER = [
            'REQUEST_URI' => '/about/',
            'SERVER_NAME' => 'admin.w3.com'
        ];

        $user = new UserMock();
        $user->populate('test', 'test', [new Role('ROLE_MOCK')], true);
        $config = new ConfigObject([
                                       'DecisionStrategy' => 'affirmative',
                                       'Rules'            => [
                                           [
                                               'Path'  => '/^\/about/',
                                               'Roles' => 'ROLE_GOD'
                                           ]
                                       ]
                                   ]
        );
        $instance = new AccessControl($user, $config);

        $this->assertTrue($instance->isUserAllowedAccess());
    }

}