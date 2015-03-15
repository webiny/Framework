<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Response;

use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * This class controls the cache headers like Cache-Control, Expires and Last-Modified.
 *
 * @package         Webiny\Component\Http\Response
 */
class CacheControl
{
    use StdObjectTrait;

    /**
     * @var \Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject
     */
    private $cacheControl;

    /**
     * @var array A list of valid cache control headers.
     */
    private static $cacheControlHeaders = [
        'cache-control',
        'expires',
        'vary',
        'pragma',
        'last-modified',
        'if-modified-since',
        'if-none-match',
        'etag'
    ];


    /**
     * Base constructor.
     */
    public function __construct()
    {
        $this->cacheControl = $this->arr([]);
    }

    /**
     * Populates the current cache control headers with options so the content is not cached by the browser.
     * These headers are returned with each response by default.
     *
     * @return $this
     */
    public function setAsDontCache()
    {
        $this->cacheControl->key('Expires', -1);
        $this->cacheControl->key('Pragma', 'no-cache');
        $this->cacheControl->key('Cache-Control', 'no-cache, must-revalidate');

        return $this;
    }

    /**
     * Populates the cache control headers with options so that the browser caches the content.
     *
     * @param DateTimeObject $expirationDate Defines the date when the cache should expire.
     *
     * @return $this
     */
    public function setAsCache(DateTimeObject $expirationDate)
    {
        $expirationDateFormatted = date('D, d M Y H:i:s', $expirationDate->getTimestamp());

        $this->cacheControl->key('Expires', $expirationDateFormatted . ' GMT');

        $maxAge = $expirationDate->getTimestamp() - time();
        $this->cacheControl->key('Cache-Control', 'private, max-age=' . $maxAge);
        $this->cacheControl->key('Last-Modified', date('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Returns the current cache control headers as an array.
     *
     * @return array
     */
    public function getCacheControl()
    {
        return $this->cacheControl->val();
    }

    /**
     * Overwrites the current cache control headers.
     *
     * @param array $cacheControl Array containing new cache control headers.
     *
     * @throws ResponseException
     * @return $this
     */
    public function setCacheControl(array $cacheControl)
    {
        //validate headers
        foreach ($cacheControl as $k => $v) {
            if (!$this->validateCacheControlHeader($k)) {
                throw new ResponseException('Invalid cache control header "' . $v . '".');
            }
        }

        $this->cacheControl = $this->arr($cacheControl);

        return $this;
    }

    /**
     * Sets or adds an entry to cache control headers.
     *
     * @param string $key   Cache control header name.
     * @param string $value Cache control header value.
     *
     * @throws ResponseException
     * @return $this
     */
    public function setCacheControlEntry($key, $value)
    {
        if (!$this->validateCacheControlHeader($key)) {
            throw new ResponseException('Invalid cache control header "' . $key . '".');
        }

        $this->cacheControl->key($key, $value);

        return $this;
    }

    /**
     * Checks if the give header name is a valid cache control header.
     *
     * @param string $header Header name.
     *
     * @return bool
     */
    private function validateCacheControlHeader($header)
    {
        if (!in_array(strtolower($header), self::$cacheControlHeaders)) {
            return false;
        }

        return true;
    }
}