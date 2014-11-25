<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Driver\AmazonS3;

use Aws\S3\Exception\NoSuchKeyException;
use Webiny\Component\Amazon\S3;
use Webiny\Component\StdLib\StdObjectTrait;
use Webiny\Component\Storage\Driver\DriverInterface;
use Webiny\Component\Storage\StorageException;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * AmazonS3 storage driver
 *
 * Make sure you sync your system time with amazon:
 *   ntpdate -u 0.amazon.pool.ntp.org
 *   ntpdate -d 0.amazon.pool.ntp.org
 *
 * @package  Webiny\Component\Storage\Driver\AmazonS3
 */
class AmazonS3 implements DriverInterface
{
    use StdObjectTrait;

    /**
     * @var S3
     */
    protected $_s3Client;
    protected $_dateFolderStructure;
    protected $_recentKey = null;
    protected $_bucket;
    protected $_recentFiles = [];
    protected $_cdnDomain = false;

    /**
     * Constructor
     *
     * @param      $accessKeyId
     * @param      $secretAccessKey
     * @param      $bucket
     * @param bool $dateFolderStructure If true, will append Y/m/d to the key
     *
     * @internal param $config
     */
    public function __construct($accessKeyId, $secretAccessKey, $bucket, $dateFolderStructure = false, $cdnDomain = false)
    {
        $this->_s3Client = new \Webiny\Component\Amazon\S3($accessKeyId, $secretAccessKey);

        $this->_bucket = $bucket;
        $this->_dateFolderStructure = $dateFolderStructure;
        $this->_cdnDomain = $cdnDomain;
    }


    /**
     * @inheritdoc
     */
    public function getTimeModified($key)
    {
        $data = $this->_s3Client->getObject($this->_bucket, $key);

        return strtotime($data['LastModified']);
    }

    /**
     * @inheritdoc
     */
    public function getSize($key)
    {
        return $this->_s3Client->getObject($this->_bucket, $key)['ContentLength'];
    }

    /**
     * @inheritdoc
     */
    public function renameKey($sourceKey, $targetKey)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getContents($key)
    {
        $this->_recentKey = $key;
        try {
            $data = (string)$this->_s3Client->getObject($this->_bucket, $key)['Body'];
        } catch (NoSuchKeyException $e) {
            throw new StorageException(StorageException::FAILED_TO_READ);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function setContents($key, $contents)
    {
        if ($this->_dateFolderStructure) {
            if (!$this->keyExists($key)) {
                $key = new StringObject($key);
                $key = date('Y/m/d') . '/' . $key->trimLeft('/');
            }
        }
        $this->_recentKey = $key;
        $params = [
            'ACL' => 'public-read'
        ];
        $this->_recentFiles[$key] = $this->_s3Client->putObject($this->_bucket, $key, $contents, $params);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key)
    {
        $this->_recentKey = $key;

        return $this->_s3Client->doesObjectExist($this->_bucket, $key);
    }


    /**
     * @inheritdoc
     */
    public function getKeys($key = '', $recursive = false)
    {
        $s3Data = [
            'Prefix' => $key
        ];

        $iterator = $this->_s3Client->getListObjectsIterator($this->_bucket, $s3Data);
        $files = [];
        foreach ($iterator as $file) {
            $files[] = $file['Key'];
        }

        return $files;
    }

    /**
     * @inheritdoc
     */
    public function deleteKey($key)
    {
        $this->_s3Client->deleteObject($this->_bucket, $key);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getURL($key)
    {
        if (!$this->arr($this->_recentFiles)->keyExists($key)) {
            $this->_recentFiles[$key]['ObjectURL'] = $this->_s3Client->getObjectUrl($this->_bucket, $key);
        }

        if($this->_cdnDomain){
            $objectUrl = $this->url($this->_recentFiles[$key]['ObjectURL']);
            $cdnDomain = $this->urAmazonl($this->_cdnDomain);

            $objectUrl->setHost($cdnDomain->getHost())->setScheme($cdnDomain->getScheme());
            return $objectUrl->val();
        }
        return $this->_recentFiles[$key]['ObjectURL'];
    }


    /**
     * @inheritdoc
     */
    public function getRecentKey()
    {
        return $this->_recentKey;
    }
}