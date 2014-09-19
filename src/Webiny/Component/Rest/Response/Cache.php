<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Response;

use Webiny\Component\Cache\CacheTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Rest\RestException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Cache class checks if we have cached the callback result.
 * It is also used to acquire the cache key.
 *
 * @package         Webiny\Component\Rest\Response
 */
class Cache
{
    use HttpTrait, StdLibTrait, CacheTrait;

    /**
     * @var RequestBag
     */
    private $_requestBag;


    /**
     * Tries to get the result for the given api request from the cache.
     *
     * @param RequestBag $requestBag
     *
     * @return bool|mixed False or the actual result from cache.
     */
    public static function getFromCache(RequestBag $requestBag)
    {
        // check if we have cache in settings
        if (!$requestBag->getApiConfig()->get('Cache', false) || $requestBag->getMethodData()['cache']['ttl'] <= 0) {
            return false;
        }

        $instance = new self($requestBag);

        return $instance->getCallbackResultFromCache();
    }

    /**
     * Saves the result from the given api request into cache.
     *
     * @param RequestBag $requestBag
     * @param mixed      $result
     *
     * @return bool
     */
    public static function saveResult(RequestBag $requestBag, $result)
    {
        // check if we have cache in settings
        if (!$requestBag->getApiConfig()->get('Cache', false) || $requestBag->getMethodData()['cache']['ttl'] <= 0) {
            return false;
        }

        $instance = new self($requestBag);

        return $instance->saveCallbackResult($result);
    }

    /**
     * Purges the cached result.
     *
     * @param RequestBag $requestBag
     *
     * @return bool
     */
    public static function purgeResult(RequestBag $requestBag)
    {
        // check if we have cache in settings
        if (!$requestBag->getApiConfig()->get('Cache', false) || $requestBag->getMethodData()['cache']['ttl'] <= 0) {
            return false;
        }

        $instance = new self($requestBag);

        return $instance->purgeCacheResult();
    }

    /**
     * Base constructor.
     *
     * @param RequestBag $requestBag
     */
    public function __construct(RequestBag $requestBag)
    {
        $this->_requestBag = $requestBag;
    }

    /**
     * Tries to get the result for the given api request from the cache.
     *
     * @throws \Webiny\Component\Rest\RestException
     * @internal param \Webiny\Component\Rest\Response\RequestBag $requestBag
     *
     * @return bool|mixed False or the actual result from cache.
     */
    public function getCallbackResultFromCache()
    {
        // get cache key
        $cacheKey = $this->_getCacheKey();

        // check if cached
        try {
            $cache = $this->cache($this->_requestBag->getApiConfig()->get('Cache'));

            return $cache->read($cacheKey);
        } catch (\Exception $e) {
            throw new RestException('Unable to read the cache. ' . $e->getMessage());
        }
    }

    /**
     * Saves the result into cache.
     *
     * @param mixed $result Result that should be saved.
     *
     * @return bool
     * @throws \Webiny\Component\Rest\RestException
     */
    public function saveCallbackResult($result)
    {
        // get cache key
        $cacheKey = $this->_getCacheKey();

        // cache the result
        try {
            $cache = $this->cache($this->_requestBag->getApiConfig()->get('Cache'));

            return $cache->save($cacheKey, $result, $this->_requestBag->getMethodData()['cache']['ttl']);
        } catch (\Exception $e) {
            throw new RestException('Unable to save the result into cache. ' . $e->getMessage());
        }
    }

    /**
     * Purges the cached result.
     *
     * @return bool
     * @throws \Webiny\Component\Rest\RestException
     */
    public function purgeCacheResult()
    {
        // get cache key
        $cacheKey = $this->_getCacheKey();

        // cache the result
        try {
            $cache = $this->cache($this->_requestBag->getApiConfig()->get('Cache'));

            return $cache->delete($cacheKey);
        } catch (\Exception $e) {
            throw new RestException('Unable to purge the result from cache. ' . $e->getMessage());
        }
    }

    /**
     * Computes the cache key, or gets it from the implemented interface from the api class.
     *
     * @return string Cache key.
     */
    private function _getCacheKey()
    {
        if ($this->_requestBag->getClassData()['cacheKeyInterface']) {
            $cacheKey = $this->_requestBag->getClassInstance()->getCacheKey();
        } else {
            $url = $this->httpRequest()->getCurrentUrl(true);
            $cacheKey = 'path-' . $url->getPath();
            $cacheKey .= 'query-' . $this->serialize($url->getQuery());
            $cacheKey .= 'method-' . $this->httpRequest()->getRequestMethod();
            $cacheKey .= 'post-' . $this->serialize($this->httpRequest()->getPost()->getAll());
            $cacheKey .= 'payload-' . $this->serialize($this->httpRequest()->getPayload()->getAll());
            $cacheKey .= 'version-' . $this->_requestBag->getClassData()['version'];
            $cacheKey = md5($cacheKey);
        }

        return $cacheKey;
    }
}