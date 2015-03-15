<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\User\Providers\Memory;

use Webiny\Component\Config\Config;
use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\Security;
use Webiny\Component\Security\Tests\Mocks\UserProviderMock;
use Webiny\Component\Security\User\Providers\Memory\User;

class UserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testAuthenticateTrue($firewall)
    {
        $user = new User();
        $user->populate('kent', 'superman', [new Role('ROLE_SUPERHERO')]);

        $login = $login = new Login('kent', 'superman');

        $this->assertTrue($user->authenticate($login, $firewall));
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testAuthenticateFalse($firewall)
    {
        $user = new User();
        $user->populate('kent', 'batman', [new Role('ROLE_SUPERHERO')]);

        $login = new Login('kent', 'superman');

        $this->assertFalse($user->authenticate($login, $firewall));
    }


    public function firewallProvider()
    {
        Security::setConfig(__DIR__ . '/../../../ExampleConfig.yaml');
        $config = Config::getInstance()->yaml(__DIR__ . '/../../../ExampleConfig.yaml');
        $firewallConfig = $config->Security->Firewalls->Admin;

        $userProviderMock = new UserProviderMock();
        $encoder = new Encoder($config->Security->Encoders->MockEncoder->Driver, []);

        $firewall = new Firewall('Admin', $firewallConfig, [$userProviderMock], $encoder);
        
        return [
            [$firewall]
        ];
    }

}