<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Amazon\Tests;


use Webiny\Component\Amazon\S3;

class AmazonS3Test extends \PHPUnit_Framework_TestCase
{
    private $_accessKeyId = ''; // set this
    private $_secretAccessKey = ''; // set this
    private $_bucket = ''; // set this
    private $_key = ''; // set this

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
    public function testCreateBucket(S3 $S3)
    {
        $S3->createBucket($this->_bucket);
        $this->assertTrue($S3->doesBucketExist($this->_bucket));
    }

    /**
     * @dataProvider driverSet
     */
    public function testPutObject(S3 $S3)
    {
        $S3->putObject($this->_bucket, $this->_key, 'Component test');
        $this->assertTrue($S3->doesObjectExist($this->_bucket, $this->_key));
    }

    /**
     * @dataProvider driverSet
     */
    public function testGetObject(S3 $S3)
    {
        $this->assertSame('Component test', (string)$S3->getObject($this->_bucket, $this->_key)['Body']);
    }

    /**
     * @dataProvider driverSet
     */
    public function testDeleteObject(S3 $S3)
    {
        $S3->deleteObject($this->_bucket, $this->_key);
        $this->assertFalse($S3->doesObjectExist($this->_bucket, $this->_key));
    }

    /**
     * @dataProvider driverSet
     */
    public function testDeleteBucket(S3 $S3)
    {
        $S3->deleteBucket($this->_bucket);
        $this->assertFalse($S3->doesBucketExist($this->_bucket));
    }

    public function driverSet()
    {
        return [
            [new S3($this->_accessKeyId, $this->_secretAccessKey)]
        ];
    }

}