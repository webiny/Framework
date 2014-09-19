<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Token;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Security\Tests\Mocks\TokenCryptMock;
use Webiny\Component\Security\Token\Token;

class TokenTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $token = new Token('wf_token_test_realm', false, '', new TokenCryptMock(new ConfigObject([])));
        $this->assertInstanceOf('\Webiny\Component\Security\Token\Token', $token);
    }
}