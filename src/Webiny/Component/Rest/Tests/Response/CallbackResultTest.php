<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Response;


use Webiny\Component\Rest\Response\CallbackResult;

class CallbackResultTest extends \PHPUnit_Framework_TestCase
{

    public function testCallbackResultData()
    {
        $cr = new CallbackResult();
        $cr->setHeaderResponse(403, 'TestMessage')->setEnvToDevelopment()->setCallbackContent('test content');

        $response = $cr->getOutput();
        $this->assertSame(['data' => 'test content'], $response);
    }

    public function testCallbackResultError()
    {
        $cr = new CallbackResult();
        $cr->setErrorResponse('Error message', 'Error desc', '555');
        $cr->addErrorMessage(['ref' => 'testing']);

        $response = $cr->getOutput();
        $expectedResponse = [
            'errorReport' => [
                'message'     => 'Error message',
                'description' => 'Error desc',
                'code'        => '555',
                'errors'      => [
                    [
                        'ref' => 'testing'
                    ]
                ]
            ]
        ];
        $this->assertSame($expectedResponse, $response);
    }
}