<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests;

use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Security;
use Webiny\Component\Security\SecurityTrait;

class SecurityTraitTest extends \PHPUnit_Framework_TestCase
{
    use SecurityTrait;

    public function testSecurity()
    {
        // before we can use security we need to set the config
        Security::setConfig(__DIR__ . '/ExampleConfig.yaml');

        // Test instance of Security
        $this->assertInstanceOf(Security::class, $this->security());

        // Test instance of Firewall
        $this->assertInstanceOf(Firewall::class, $this->security()->firewall('Admin'));

        // Test shorter access to Firewall
        $this->assertInstanceOf(Firewall::class, $this->security('Admin'));
    }

}