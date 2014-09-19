<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests;

use Webiny\Component\Security\SecurityTrait;

class SecurityTraitTest extends \PHPUnit_Framework_TestCase
{
    use SecurityTrait;

    public function testSecurity()
    {
        // before we can use security we need to set the config
        \Webiny\Component\Security\Security::setConfig(__DIR__ . '/ExampleConfig.yaml');

        $this->assertInstanceOf('\Webiny\Component\Security\Security', $this->security());
    }

}