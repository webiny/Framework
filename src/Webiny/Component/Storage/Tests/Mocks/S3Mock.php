<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Tests\Mocks;

use Webiny\Component\Storage\Storage;

/**
 * S3 Client Component - Amazon S3 Client
 *
 * @package Webiny\Component\Amazon
 */
class S3Mock extends \PHPUnit_Framework_TestCase
{
    private $_instance;

    public function __construct($accessKeyId, $secretAccessKey)
    {
        $bridgeClass = Storage::getConfig()->get('Bridges.AmazonS3', '\Webiny\Component\Amazon\S3');

        $mock = $this->getMockBuilder($bridgeClass)->disableOriginalConstructor()->getMock();

        $mock->expects($this->any())->method('getObject')->willReturn(['Body' => 'Test contents']);
        $mock->expects($this->any())->method('deleteObject')->willReturn([]);
        $mock->expects($this->any())->method('putObject')->willReturn([]);
        $mock->expects($this->any())->method('doesObjectExist')->will($this->onConsecutiveCalls(true, false));
        $this->_instance = $mock;
    }

    public function getObject($bucket, $key, array $params = [])
    {
        return $this->_instance->getObject($bucket, $key, $params);
    }

    public function deleteObject($bucket, $key, array $params = [])
    {
        return $this->_instance->deleteObject($bucket, $key, $params);
    }

    public function putObject($bucket, $key, $content, array $params = [])
    {
        return $this->_instance->putObject($bucket, $key, $content, $params);
    }

    public function doesObjectExist($bucket, $key, array $params = [])
    {
        return $this->_instance->doesObjectExist($bucket, $key, $params);
    }
}