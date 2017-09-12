<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Tests;

use Webiny\Component\OAuth2\OAuth2;
use Webiny\Component\OAuth2\OAuth2User;

class OAuth2UserTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = '/ExampleConfig.yaml';


    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testConstructor($u)
    {
        $this->assertInstanceOf(OAuth2User::class, $u);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetProfileUrl($u)
    {
        $url = 'http://www.webiny.com/profile/webiny';
        $u->setProfileUrl($url);

        $this->assertSame($url, $u->getProfileUrl());
        $this->assertSame($url, $u->profileUrl);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetAvatarUrl($u)
    {
        $avatar = 'http://www.webiny.com/avatar/webiny.jpg';
        $u->setAvatarUrl($avatar);

        $this->assertSame($avatar, $u->getAvatarUrl());
        $this->assertSame($avatar, $u->avatarUrl);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetProfileId($u)
    {
        $pid = 123;
        $u->setProfileId($pid);

        $this->assertSame($pid, $u->getProfileId());
        $this->assertSame($pid, $u->profileId);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetFirstName($u)
    {
        $fname = 'Sven';
        $u->setFirstName($fname);

        $this->assertSame($fname, $u->getFirstName());
        $this->assertSame($fname, $u->firstName);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetLastName($u)
    {
        $lname = 'Al Hamad';
        $u->setLastName($lname);

        $this->assertSame($lname, $u->getLastName());
        $this->assertSame($lname, $u->lastName);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetLastUpdateTime($u)
    {
        $time = time();
        $u->setLastUpdateTime($time);

        $this->assertSame($time, $u->getLastUpdateTime());
        $this->assertSame($time, $u->lastUpdated);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetServiceName($u)
    {
        $service = 'facebook';
        $u->setServiceName($service);

        $this->assertSame($service, $u->getServiceName());
        $this->assertSame($service, $u->serviceName);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetGenderFemale($u)
    {
        $gender = 'female';
        $u->setGender($gender);

        $this->assertSame($gender, $u->getGender());
        $this->assertSame($gender, $u->gender);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     */
    public function testSetGetGenderMale($u)
    {
        $gender = 'male';
        $u->setGender($gender);

        $this->assertSame($gender, $u->getGender());
        $this->assertSame($gender, $u->gender);
    }

    /**
     * @param OAuth2User $u
     *
     * @dataProvider dataProvider
     * @expectedException \Webiny\Component\OAuth2\OAuth2Exception
     */
    public function testSetGetGenderException($u)
    {
        $gender = 'exception';
        $u->setGender($gender);

        $this->assertSame($gender, $u->getGender());
        $this->assertSame($gender, $u->gender);
    }

    public function dataProvider()
    {
        OAuth2::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
        $oauth2User = new OAuth2User('webiny', 'info@webiny.com');

        return [
            [$oauth2User]
        ];
    }

}