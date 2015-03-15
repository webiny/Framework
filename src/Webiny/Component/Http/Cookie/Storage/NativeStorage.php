<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Cookie\Storage;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Cookie\CookieException;
use Webiny\Component\Http\Cookie\CookieStorageInterface;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Native cookie storage.
 *
 * @package         Webiny\Component\Http\Cookie\Storage
 */
class NativeStorage implements CookieStorageInterface
{
    use HttpTrait;

    private $domain = '';
    private $https = false;

    /**
     * Constructor.
     *
     * @param ConfigObject $config Cookie config.
     */
    public function __construct(ConfigObject $config)
    {
        $this->domain = $this->httpRequest()->getHostName();
        $this->https = $this->httpRequest()->isRequestSecured();
    }

    /**
     * Save a cookie.
     *
     * @param string $name       Name of the cookie.
     * @param string $value      Cookie value.
     * @param int    $expiration Timestamp when the cookie should expire.
     * @param bool   $httpOnly   Is the cookie https-only or not.
     * @param string $path       Path under which the cookie is accessible.
     *
     * @return bool True if cookie was save successfully, otherwise false.
     * @throws CookieException
     */
    public function save($name, $value, $expiration, $httpOnly = true, $path = '/')
    {
        try {
            return setcookie($name, $value, $expiration, $path, $this->domain, $this->https, $httpOnly);
        } catch (\ErrorException $e) {
            throw new CookieException($e->getMessage());
        }
    }

    /**
     * Get all stored cookies.
     *
     * @return array A list of all stored cookies.
     */
    public function getAll()
    {
        return $_COOKIE;
    }

    /**
     * Delete the given cookie.
     *
     * @param string $name Name of the cookie.
     *
     * @return bool True if cookie was deleted, otherwise false.
     */
    public function delete($name)
    {
        return setcookie($name, '', (time() - 86400), '/', $this->domain, $this->https, true);
    }
}