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

    public function testGetOutput()
    {
        $cr = new CallbackResult();
        $cr->setHeaderResponse(403, 'TestMessage')->setEnvToDevelopment()->setData('test content');

        $response = $cr->getOutput();
        $this->assertSame(['data' => 'test content'], $response);
    }

    public function testGetSetData()
    {
        $obj = new \stdClass();
        $obj->title = 'Rock star';
        $cr = new CallbackResult();
        $cr->setData($obj);

        $this->assertSame('Rock star', $cr->getData()->title);
    }

    public function testErrorResponse()
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

    public function testGetErrorTrue()
    {
        $cr = new CallbackResult();
        $cr->setErrorResponse('Error message', 'Error desc', '555');
        $cr->addErrorMessage(['ref' => 'testing']);

        $error = $cr->getError();
        $this->assertNotFalse($error);
        $this->assertSame('testing', $error['errors'][0]['ref']);
    }

    public function testGetErrorFalse()
    {
        $obj = new \stdClass();
        $obj->title = 'Rock star';
        $cr = new CallbackResult();
        $cr->setData($obj);

        $this->assertFalse($cr->getError());
    }
}