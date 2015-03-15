<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\User\Providers\Memory;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\Providers\Memory\Memory;

class MemoryProviderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param MemoryProvide $instance
     *
     * @dataProvider dataProvider
     */
    public function testConstructor($instance)
    {
        $this->assertInstanceOf('\Webiny\Component\Security\User\Providers\Memory\Memory', $instance);
    }

    /**
     * @param Memory $instance
     *
     * @dataProvider dataProvider
     */
    public function testGetUser($instance)
    {
        $login = new Login('kent', '');
        $user = $instance->getUser($login);
        $this->assertInstanceOf('\Webiny\Component\Security\User\Providers\Memory\User', $user);
    }

    /**
     * @param MemoryProvider $instance
     *
     * @dataProvider dataProvider
     * @expectedException \Webiny\Component\Security\User\Exceptions\UserNotFoundException
     */
    public function testGetUserException($instance)
    {
        $login = new Login('gandalf', '');
        $instance->getUser($login);
    }

    public function dataProvider()
    {
        $users = [
            'wayne' => [
                'password' => 'batman',
                'roles'    => 'ROLE_HERO'
            ],
            'kent'  => [
                'password' => 'superman',
                'roles'    => 'ROLE_SUPERHERO'
            ],
        ];

        $instance = new Memory($users);

        return [[$instance]];
    }
}