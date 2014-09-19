<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Mocks;

use Webiny\Component\Rest\Interfaces\VersionInterface;

class MockApiClass implements VersionInterface
{
    /**
     * @rest.method post
     * @rest.default
     * @rest.role SECRET
     * @rest.rateControl.ignore
     * @rest.cache.ttl 3600
     * @rest.header.cache.expires 3600
     * @rest.header.status.success 201
     * @rest.header.status.error 403
     * @rest.header.status.errorMessage No Author for specified id.
     *
     * @param string $param1 Some param.
     * @param string $param2 Other param.
     * @param int    $param3
     *
     * @return array
     */
    public function someMethod($param1, $param2 = "default", $param3 = 22)
    {
        return [
            'name' => 'some method',
            'p1'   => $param1,
            'p2'   => $param2,
            'p3'   => $param3
        ];
    }

    public function simpleMethod()
    {
        return 'test';
    }

    /**
     * Returns the latest (highest) available api version.
     * Version should be in format of A.B, for example: 1.1
     *
     * @rest.ignore
     *
     * @return string
     */
    public static function getLatestVersion()
    {
        return '1.1';
    }

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
    public static function getCurrentVersion()
    {
        return '1.0';
    }

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
    public static function getAllVersions()
    {
        return [
            '1.0' => 'Webiny\Component\Rest\Tests\Mocks\MockApiClass',
            '1.1' => 'Webiny\Component\Rest\Tests\Mocks\MockApiClassNew'
        ];
    }
}