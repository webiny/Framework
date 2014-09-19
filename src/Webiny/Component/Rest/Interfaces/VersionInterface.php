<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Interfaces;

/**
 * Implement this interface on your api class when you wish to control api versions.
 * The interfaces must be only implemented on the class that your are sending to: Rest::registerClass method.
 * Other versions don't need to implement the interface, since from the main class
 * we can get to all of the versions, and it's easier for you to maintain one file.
 *
 * @package    Webiny\Component\Rest\Interfaces
 */

interface VersionInterface
{
    /**
     * Returns the latest (highest) available api version.
     * Version should be in format of A.B, for example: 1.1
     *
     * @rest.ignore
     *
     * @return string
     */
    public static function getLatestVersion();

    /**
     * Returns the current api version. For example, you might have the latest version in a "beta" state,
     * so you wish to keep your users using the latest stable versions.
     * Users can still access the latest version if the specify the latest version
     * number or "latest" as a version name in the X-Webiny-Rest-Version header.
     *
     * Version should be in format of A.B, for example: 1.1
     *
     * @rest.ignore
     *
     * @return string
     */
    public static function getCurrentVersion();

    /**
     * Returns a list of all version, where the key is the version, and value is the namespace.
     * Example:
     * [
     *     '1.0' => 'MyApp/Apis/Cms/Page/V1x1/',
     *     '1.2' => 'MyApp/Apis/Cms/Page/V1x2/',
     *     '2.0' => 'MyApp/Apis/Cms/Page/',
     * ]
     *
     * How you organize your namespace is up to you.
     *
     * @rest.ignore
     *
     * @return array
     */
    public static function getAllVersions();
}