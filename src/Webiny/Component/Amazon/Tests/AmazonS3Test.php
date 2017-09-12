<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Amazon\Tests;


use Webiny\Component\Amazon\S3;
use Webiny\Component\Amazon\Tests\Mocks\S3BridgeMock;
use Webiny\Component\Config\ConfigObject;

class AmazonS3Test extends \PHPUnit_Framework_TestCase
{
    private $bucket = 'webiny-test';
    private $key = 'webiny-test.txt';

    /**
     * @dataProvider driverSet
     */
    public function testConstructor($S3)
    {
        $this->assertInstanceOf(S3::class, $S3);
    }

    /**
     * @dataProvider driverSet
     */
    public function testAmazon(S3 $S3)
    {
        $S3->createBucket($this->bucket);
        $this->assertTrue($S3->doesBucketExist($this->bucket));

        $S3->putObject($this->bucket, $this->key, 'Component test');
        $this->assertTrue($S3->doesObjectExist($this->bucket, $this->key));

        $this->assertSame('Component test', (string)$S3->getObject($this->bucket, $this->key)['Body']);

        $S3->deleteObject($this->bucket, $this->key);
        $this->assertFalse($S3->doesObjectExist($this->bucket, $this->key));

        $S3->deleteBucket($this->bucket);
        $this->assertFalse($S3->doesBucketExist($this->bucket));
    }

    public function driverSet()
    {
        S3::setConfig(new ConfigObject(['Bridge' => S3BridgeMock::class]));

        return [
            [new S3(false, false, false)]
        ];
    }

}