<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Tests;

use Webiny\Component\Config\Config;
use Webiny\Component\Config\ConfigTrait;

class ConfigTraitTest extends \PHPUnit_Framework_TestCase
{
    use ConfigTrait;

    public function testTrait()
    {
        $this->assertInstanceOf(Config::class, $this->config());
    }
}