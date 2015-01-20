<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Amazon\Bridge\Sns;

use Aws\Sns\SnsClient;

/**
 * Amazon SNS Client Bridge
 *
 * @package Webiny\Component\Amazon
 */
class S3 implements SnsClientInterface
{

    /**
     * @var \Aws\S3\S3Client
     */
    private $_instance;

    public function __construct($accessKeyId, $secretAccessKey)
    {
        $this->_instance = SnsClient::factory([
                                                 'key'    => $accessKeyId,
                                                 'secret' => $secretAccessKey,
                                             ]
        );
    }
}