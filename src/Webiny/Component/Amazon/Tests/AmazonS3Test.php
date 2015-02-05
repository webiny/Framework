<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Amazon\Tests;


use Webiny\Component\Amazon\S3;
use Webiny\Component\Config\ConfigObject;

class AmazonS3Test extends \PHPUnit_Framework_TestCase
{
    private $_bucket = 'webiny-test';
    private $_key = 'webiny-test.txt';
    /**
     * @dataProvider driverSet
     */
    public function testConstructor($S3)
    {
        $this->assertInstanceOf('Webiny\Component\Amazon\S3', $S3);
    }

    /**
     * @dataProvider driverSet
     */
    public function testAmazon(S3 $S3)
    {
        $S3->createBucket($this->_bucket);
        $this->assertTrue($S3->doesBucketExist($this->_bucket));

        $S3->putObject($this->_bucket, $this->_key, 'Component test');
        $this->assertTrue($S3->doesObjectExist($this->_bucket, $this->_key));

        $this->assertSame('Component test', (string)$S3->getObject($this->_bucket, $this->_key)['Body']);

        $S3->deleteObject($this->_bucket, $this->_key);
        $this->assertFalse($S3->doesObjectExist($this->_bucket, $this->_key));

        $S3->deleteBucket($this->_bucket);
        $this->assertFalse($S3->doesBucketExist($this->_bucket));
    }

    public function driverSet()
    {
        $config = new ConfigObject(['Bridge' => '\Webiny\Component\Amazon\Tests\Mocks\S3BridgeMock']);

        S3::setConfig($config);
        
        return [
            [new S3(false, false)]
        ];
    }

}