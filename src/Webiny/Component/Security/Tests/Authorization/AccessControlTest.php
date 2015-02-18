<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authorization;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Request;
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

    public function testIsUserAllowedAccessNoRolesDefined()
    {
        $user = new UserMock();
        $config = new ConfigObject(['DecisionStrategy' => 'unanimous']);
        $instance = new AccessControl($user, $config);

        $this->assertTrue($instance->isUserAllowedAccess());
    }

    public function testIsUserAllowedAccessNoRolesRequired()
    {
        // lets mock the address to one that doesn't match any rules
        Request::getInstance()->setCurrentUrl('http://admin.w3.com/some-page/');

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

    public function testIsUserAllowedAccessRoleRequiredButDenied()
    {
        // lets mock the address to one that doesn't match any rules
        Request::getInstance()->setCurrentUrl('http://admin.w3.com/about/');

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

    public function testIsUserAllowedAccessRoleRequiredButGranted()
    {
        // lets mock the address to one that doesn't match any rules
        Request::getInstance()->setCurrentUrl('http://admin.w3.com/about/');

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

    public function testIsUserAllowedAccessDecisionStrategyAffirmative()
    {
        // lets mock the address to one that doesn't match any rules
        Request::getInstance()->setCurrentUrl('http://admin.w3.com/about/');

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