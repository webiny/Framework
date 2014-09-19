<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Cookie;

use Webiny\Component\Config\ConfigObject;

/**
 * Cookie storage interface.
 * If you wish to create your own cookie storage, you must implement this interface.
 *
 * @package         Webiny\Component\Http\Cookie
 */
interface CookieStorageInterface
{

    /**
     * Constructor.
     *
     * @param ConfigObject $config Cookie config.
     */
    public function __construct(ConfigObject $config);

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
    public function save($name, $value, $expiration, $httpOnly = true, $path = '/');

    /**
     * Get all stored cookies.
     *
     * @return array A list of all stored cookies.
     */
    public function getAll();

    /**
     * Delete the given cookie.
     *
     * @param string $name Name of the cookie.
     *
     * @return bool True if cookie was deleted, otherwise false.
     */
    public function delete($name);

}