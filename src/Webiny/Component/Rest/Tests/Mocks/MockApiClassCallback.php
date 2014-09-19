<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Mocks;

use Webiny\Component\Rest\RestErrorException;

class MockApiClassCallback
{

    public function testCallback()
    {
        return 'test result';
    }

    public function testCallbackRestErrorException()
    {
        throw new RestErrorException('This is a rest error.');
    }

    public function testCallbackException()
    {
        throw new \Exception('Some exception');
    }
}