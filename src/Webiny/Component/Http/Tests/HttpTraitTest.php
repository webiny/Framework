<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Http\Request;

class HttpTraitTest extends \PHPUnit_Framework_TestCase
{
    use HttpTrait;

    const CONFIG = 'ExampleConfig.yaml';

    /**
     * @runInSeparateProcess
     */
    public function testRequest()
    {
        $this->assertInstanceOf(Request::class, $this->httpRequest());
    }
}