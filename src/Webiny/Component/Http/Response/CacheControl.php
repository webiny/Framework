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
    private $_cacheControl;

    /**
     * @var array A list of valid cache control headers.
     */
    private static $_cacheControlHeaders = [
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
        $this->_cacheControl = $this->arr([]);
    }

    /**
     * Populates the current cache control headers with options so the content is not cached by the browser.
     * These headers are returned with each response by default.
     *
     * @return $this
     */
    public function setAsDontCache()
    {
        $this->_cacheControl->key('Expires', -1);
        $this->_cacheControl->key('Pragma', 'no-cache');
        $this->_cacheControl->key('Cache-Control', 'no-cache, must-revalidate');

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

        $this->_cacheControl->key('Expires', $expirationDateFormatted . ' GMT');

        $maxAge = $expirationDate->getTimestamp() - time();
        $this->_cacheControl->key('Cache-Control', 'private, max-age=' . $maxAge);
        $this->_cacheControl->key('Last-Modified', date('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Returns the current cache control headers as an array.
     *
     * @return array
     */
    public function getCacheControl()
    {
        return $this->_cacheControl->val();
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
            if (!$this->_validateCacheControlHeader($k)) {
                throw new ResponseException('Invalid cache control header "' . $v . '".');
            }
        }

        $this->_cacheControl = $this->arr($cacheControl);

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
        if (!$this->_validateCacheControlHeader($key)) {
            throw new ResponseException('Invalid cache control header "' . $key . '".');
        }

        $this->_cacheControl->key($key, $value);

        return $this;
    }

    /**
     * Checks if the give header name is a valid cache control header.
     *
     * @param string $header Header name.
     *
     * @return bool
     */
    private function _validateCacheControlHeader($header)
    {
        if (!in_array(strtolower($header), self::$_cacheControlHeaders)) {
            return false;
        }

        return true;
    }
}