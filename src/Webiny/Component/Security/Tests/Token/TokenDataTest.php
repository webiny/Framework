<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Token;

use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\Token\TokenData;

class TokenDataTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param TokenData $tokenData
     *
     * @dataProvider dataProvider
     */
    public function testConstructor($tokenData)
    {
        $this->assertInstanceOf(TokenData::class, $tokenData);
    }

    /**
     * @param TokenData $tokenData
     *
     * @dataProvider dataProvider
     */
    public function testGetUsername($tokenData)
    {
        $this->assertSame('username', $tokenData->getUsername());
    }

    /**
     * @param TokenData $tokenData
     *
     * @dataProvider dataProvider
     */
    public function testGetRoles($tokenData)
    {
        $roles = $tokenData->getRoles();
        $this->assertSame('ROLE_MOCK', $roles[0]->getRole());
    }

    /**
     * @param TokenData $tokenData
     *
     * @dataProvider dataProvider
     */
    public function testGetAuthProviderName($tokenData)
    {
        $this->assertSame('MockProvider', $tokenData->getAuthProviderName());
    }

    public function dataProvider()
    {
        $tokenArray = [
            'u'  => 'username',
            'r'  => [new Role('ROLE_MOCK')],
            'vu' => time() + 3600,
            'ap' => 'MockProvider'
        ];

        $tokenData = new TokenData($tokenArray);

        return [[$tokenData]];
    }
}