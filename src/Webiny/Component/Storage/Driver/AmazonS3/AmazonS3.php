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
use Webiny\Component\Storage\Storage;
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
    protected $s3Client;
    protected $dateFolderStructure;
    protected $recentKey = null;
    protected $bucket;
    protected $recentFiles = [];
    protected $cdnDomain = false;

    /**
     * Constructor
     *
     * @param      $accessKeyId
     * @param      $secretAccessKey
     * @param      $bucket
     * @param bool $dateFolderStructure If true, will append Y/m/d to the key
     * @param bool $cdnDomain
     *
     * @internal param $config
     */
    public function __construct($accessKeyId, $secretAccessKey, $bucket, $dateFolderStructure = false, $cdnDomain = false)
    {
        $bridge = Storage::getConfig()->get('Bridges.AmazonS3', '\Webiny\Component\Amazon\S3');
        $this->s3Client = new $bridge($accessKeyId, $secretAccessKey);

        $this->bucket = $bucket;
        $this->dateFolderStructure = $dateFolderStructure;
        $this->cdnDomain = $cdnDomain;
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
        if ($this->dateFolderStructure) {
            if (!$this->keyExists($key)) {
                $key = new StringObject($key);
                $key = date('Y/m/d') . '/' . $key->trimLeft('/');
            }
        }
        $this->recentKey = $key;
        $params = [
            'ACL' => 'public-read'
        ];
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
        if (!$this->arr($this->recentFiles)->keyExists($key)) {
            $this->recentFiles[$key]['ObjectURL'] = $this->s3Client->getObjectUrl($this->bucket, $key);
        }

        if($this->cdnDomain){
            $objectUrl = $this->url($this->recentFiles[$key]['ObjectURL']);
            $cdnDomain = $this->url($this->cdnDomain);

            $objectUrl->setHost($cdnDomain->getHost())->setScheme($cdnDomain->getScheme());
            return $objectUrl->val();
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
}