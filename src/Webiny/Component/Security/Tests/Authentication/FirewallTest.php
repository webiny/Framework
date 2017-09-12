<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authentication;


use Webiny\Component\Config\Config;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Request;
use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\Security;
use Webiny\Component\Security\Tests\Mocks\UserMock;
use Webiny\Component\Security\Tests\Mocks\UserProviderMock;
use Webiny\Component\Security\Token\Token;
use Webiny\Component\Security\User\AnonymousUser;

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
     */
    public function testConstructor($firewall)
    {
        $this->assertInstanceOf(Firewall::class, $firewall);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
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
     * @expectedException \Webiny\Component\Security\Authentication\FirewallException
     * @expectedExceptionMessage Authentication provider "fake auth provider" is not defined on "Admin" firewall.
     */
    public function testProcessLoginAuthProviderNameException($firewall)
    {
        $result = $firewall->processLogin('fake auth provider');
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testProcessLogout($firewall)
    {
        UserProviderMock::$returnLoginObject = true;
        $result = $firewall->processLogin('MockProvider');
        $this->assertTrue($result);
        $result = $firewall->processLogout();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testGetUserWhileNotAuthenticated($firewall)
    {
        $user = $firewall->getUser();
        $this->assertInstanceOf(AnonymousUser::class, $user);
        $this->assertFalse($user->isAuthenticated());
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testGetUserWhileAuthenticated($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user
        $user = $firewall->getUser();
        $this->assertInstanceOf(UserMock::class, $user);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testIsUserAllowedAccessOnAddressThatIsNotInAccessRules($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user

        Request::getInstance()->setCurrentUrl('http://admin.w3.com/batman-is-better-than-superman/?batman=one&superman=two');

        $result = $firewall->isUserAllowedAccess();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testIsUserAllowedAccessOnAddressThatIsInsideAccessRules($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user

        Request::getInstance()->setCurrentUrl('http://admin.w3.com/allowed/?batman=one&superman=two');

        $result = $firewall->isUserAllowedAccess();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testIsUserAllowedAccessOnAddressThatIsInsideAccessRulesRegex($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user

        Request::getInstance()->setCurrentUrl('http://admin.w3.com/iamturganbaev/more-about-kyrgyzstan/532c08a1afb28/');
        $result = $firewall->isUserAllowedAccess();
        $this->assertTrue($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testIsUserDeniedAccessOnAddressThatIsInsideAccessRules($firewall)
    {
        $firewall->processLogin('MockProvider'); // let's authenticate the user

        Request::getInstance()->setCurrentUrl('http://admin.w3.com/about/');
        $result = $firewall->isUserAllowedAccess();
        $this->assertFalse($result);
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testGetRealName($firewall)
    {
        $this->assertSame("Administration", $firewall->getRealmName());
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testGetAnonymousAccess($firewall)
    {
        $this->assertTrue($firewall->getAnonymousAccess());
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testGetConfig($firewall)
    {
        $config = $firewall->getConfig();
        $this->assertInstanceOf(ConfigObject::class, $config);
        $this->assertSame('MockEncoder', $config->get('Encoder'));
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testGetToken($firewall)
    {
        $this->assertInstanceOf(Token::class, $firewall->getToken());
    }

    /**
     * @param Firewall $firewall
     *
     * @dataProvider firewallProvider
     */
    public function testGetFirewallKey($firewall)
    {
        $this->assertSame('Admin', $firewall->getFirewallKey());
    }

    public function firewallProvider()
    {
        Security::deleteInstance();
        Security::setConfig(__DIR__ . '/../ExampleConfig.yaml');
        $config = Config::getInstance()->yaml(__DIR__ . '/../ExampleConfig.yaml');
        $firewallConfig = $config->Security->Firewalls->Admin;

        $userProviderMock = new UserProviderMock();
        $encoder = new Encoder($config->Security->Encoders->MockEncoder->Driver, []);

        $firewall = new Firewall('Admin', $firewallConfig, [$userProviderMock], $encoder);

        return [
            [$firewall]
        ];
    }
}