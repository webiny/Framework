<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Driver\AmazonS3;

use Aws\S3\Exception\NoSuchKeyException;
use Webiny\Component\Amazon\S3;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObjectTrait;
use Webiny\Component\Storage\Driver\DriverInterface;
use Webiny\Component\Storage\Driver\SizeAwareInterface;
use Webiny\Component\Storage\Storage;
use Webiny\Component\Storage\StorageException;

/**
 * AmazonS3 storage driver
 *
 * Make sure you sync your system time with amazon:
 *   ntpdate -u 0.amazon.pool.ntp.org
 *   ntpdate -d 0.amazon.pool.ntp.org
 *
 * @package  Webiny\Component\Storage\Driver\AmazonS3
 */
class S3StorageDriver implements DriverInterface, SizeAwareInterface
{
    use StdObjectTrait;

    /**
     * @var S3
     */
    protected $s3Client;
    protected $recentKey = null;
    protected $bucket;
    protected $recentFiles = [];
    protected $cdnDomain = false;
    protected $params = [];

    /**
     * Constructor
     *
     * @param array|ArrayObject $config
     *
     * @throws StorageException
     */
    public function __construct($config)
    {
        if (is_array($config)) {
            $config = new ArrayObject($config);
        }

        if (!$config instanceof ArrayObject) {
            throw new StorageException('Storage driver config must be an array or ArrayObject!');
        }

        $bridge = Storage::getConfig()->get('Bridges.AmazonS3', '\Webiny\Component\Amazon\S3');
        $accessKeyId = $config->key('AccessKeyId');
        $secretAccessKey = $config->key('SecretAccessKey');
        $region = $config->key('Region');
        $endpoint = $config->key('Endpoint');
        $this->s3Client = new $bridge($accessKeyId, $secretAccessKey, $region, $endpoint);

        $this->bucket = $config->key('Bucket');
        $this->cdnDomain = $config->key('CdnDomain');
        $this->params = $config->key('Params');
        if (!is_array($this->params)) {
            $this->params = [];
        }
    }


    /**
     * @inheritdoc
     */
    public function getTimeModified($key)
    {
        $data = $this->s3Client->getObject($this->bucket, $key);

        return strtotime($data['LastModified']);
    }

    /**
     * @inheritdoc
     */
    public function getSize($key)
    {
        return $this->s3Client->getObject($this->bucket, $key)['ContentLength'];
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
        $this->recentKey = $key;
        try {
            $data = (string)$this->s3Client->getObject($this->bucket, $key)['Body'];
        } catch (NoSuchKeyException $e) {
            throw new StorageException(StorageException::FAILED_TO_READ);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function setContents($key, $contents, $append = false)
    {
        $this->recentKey = $key;
        $params = $this->params;
        $params['ACL'] = 'public-read';
        $this->recentFiles[$key] = $this->s3Client->putObject($this->bucket, $key, $contents, $params);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key)
    {
        $this->recentKey = $key;

        return $this->s3Client->doesObjectExist($this->bucket, $key);
    }


    /**
     * @inheritdoc
     */
    public function getKeys($key = '', $recursive = false)
    {
        $s3Data = [
            'Prefix' => $key
        ];

        $iterator = $this->s3Client->getListObjectsIterator($this->bucket, $s3Data);
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
        $this->s3Client->deleteObject($this->bucket, $key);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getURL($key)
    {
        if ($this->cdnDomain) {
            return $this->cdnDomain . '/' . $key;
        }

        if (!$this->arr($this->recentFiles)->keyExists($key)) {
            $this->recentFiles[$key]['ObjectURL'] = $this->s3Client->getObjectUrl($this->bucket, $key);
        }

        return $this->recentFiles[$key]['ObjectURL'];
    }


    /**
     * @inheritdoc
     */
    public function getRecentKey()
    {
        return $this->recentKey;
    }

    /**
     * @inheritDoc
     */
    public function createDateFolderStructure()
    {
        return false;
    }
}