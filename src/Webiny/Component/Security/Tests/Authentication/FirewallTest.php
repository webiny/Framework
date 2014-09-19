<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authentication;


use Webiny\Component\Config\Config;
use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\Security;
use Webiny\Component\Security\Tests\Mocks\UserProviderMock;

/**
 * Class FirewallTest
 * @package Webiny\Component\Security\Tests\Authentication
 */
class FirewallTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Security::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testConstructor($firewall)
    {
        $this->assertInstanceOf('\Webiny\Component\Security\Authentication\Firewall', $firewall);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testProcessLoginNoAuthProviderName($firewall)
    {
        $result = $firewall->processLogin();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testProcessLoginWithAuthProviderName($firewall)
    {
        $result = $firewall->processLogin('MockProvider');
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testProcessLoginWithAuthProviderNameAndNotAuthenticatedUser($firewall)
    {
        UserProviderMock::$returnLoginObject = false;
        $result = $firewall->processLogin('MockProvider');
        $this->assertFalse($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider             firewallProvider
     * @runInSeparateProcess
     * @expectedException \Webiny\Component\Security\Authentication\FirewallException
     * @expectedExceptionMessage Unable to detect configuration for authentication provide
     */
    public function testProcessLoginAuthProviderNameException($firewall)
    {
        $result = $firewall->processLogin('fake auth provider');
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testProcessLogout($firewall)
    {
        $result = $firewall->processLogin('MockProvider');
        $this->assertTrue($result);
        $result = $firewall->processLogout();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testGetUserWhileNotAuthenticated($firewall)
    {
        $user = $firewall->getUser();
        $this->assertInstanceOf('\Webiny\Component\Security\User\AnonymousUser', $user);
        $this->assertFalse($user->isAuthenticated());
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testGetUserWhileAuthenticated($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user
        $user = $firewall->getUser();
        $this->assertInstanceOf('\Webiny\Component\Security\Tests\Mocks\UserMock', $user);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testIsUserAllowedAccessOnAddressThatIsNotInAccessRules($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user

        // lets mock the address
        $_SERVER = [
            'REQUEST_URI' => '/batman-is-better-than-superman/?batman=one&superman=two',
            'SERVER_NAME' => 'admin.w3.com'
        ];

        $result = $firewall->isUserAllowedAccess();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testIsUserAllowedAccessOnAddressThatIsInsideAccessRules($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user

        // lets mock the address
        $_SERVER = [
            'REQUEST_URI' => '/allowed/?batman=one&superman=two',
            'SERVER_NAME' => 'admin.w3.com'
        ];

        $result = $firewall->isUserAllowedAccess();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testIsUserAllowedAccessOnAddressThatIsInsideAccessRulesRegex($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user

        // lets mock the address
        $_SERVER = [
            'REQUEST_URI' => '/iamturganbaev/more-about-kyrgyzstan/532c08a1afb28/',
            'SERVER_NAME' => 'admin.w3.com'
        ];

        $result = $firewall->isUserAllowedAccess();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testIsUserDeniedAccessOnAddressThatIsInsideAccessRules($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user

        // lets mock the address
        $_SERVER = [
            'REQUEST_URI' => '/about/',
            'SERVER_NAME' => 'admin.w3.com'
        ];

        $result = $firewall->isUserAllowedAccess();
        $this->assertFalse($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testGetRealName($firewall)
    {
        $this->assertSame("Administration", $firewall->getRealmName());
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testGetAnonymousAccess($firewall)
    {
        $this->assertTrue($firewall->getAnonymousAccess());
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testGetConfig($firewall)
    {
        $config = $firewall->getConfig();
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertSame('MockEncoder', $config->get('Encoder'));
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testGetToken($firewall)
    {
        $this->assertInstanceOf('\Webiny\Component\Security\Token\Token', $firewall->getToken());
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     * @runInSeparateProcess
     */
    public function testGetFirewallKey($firewall)
    {
        $this->assertSame('Admin', $firewall->getFirewallKey());
    }

    public function firewallProvider()
    {
        Security::setConfig(__DIR__ . '/../ExampleConfig.yaml');
        $config = Config::getInstance()->yaml(__DIR__ . '/../ExampleConfig.yaml');
        $firewallConfig = $config->Security->Firewalls->Admin;

        $userProviderMock = new UserProviderMock();
        $encoder = new Encoder($config->Security->Encoders->MockEncoder->Driver, '', []);

        $firewall = new Firewall('Admin', $firewallConfig, [$userProviderMock], $encoder);

        return [
            [$firewall]
        ];
    }
}