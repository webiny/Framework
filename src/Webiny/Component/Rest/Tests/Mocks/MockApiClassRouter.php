<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Mocks;

class MockApiClassRouter
{

    /**
     * @rest.default
     * @rest.method post
     * @return string
     */
    public function defaultNoParamsPost()
    {
        return 'testProcessRequestDefaultNoParams';
    }

    /**
     * @rest.default
     *
     * @param string $param
     *
     * @return string
     */
    public function defaultOneParamNotRequired($param = 'd3f')
    {
        return 'testProcessRequestOneStringParamRequired - ' . $param;
    }

    /**
     * @rest.default
     *
     * @param int    $p1
     * @param string $param
     *
     * @return string
     */
    public function defaultTwoParam($p1, $param = 'd3f')
    {
        return 'testProcessRequestOneStringParamRequired - ' . $p1 . ' - ' . $param;
    }

    /**
     * @param int $int
     *
     * @return string
     */
    public function testInteger($int)
    {
        return 'testProcessRequestOneIntegerParamRequired - ' . $int;
    }

    /**
     * @rest.default
     *
     * @param string $p1
     * @param int    $p2
     * @param string $p3
     *
     * @return string
     */
    public function testStringIntDefaultString($p1, $p2, $p3 = 'd3f')
    {
        return 'testProcessRequestStringIntDefString - ' . $p1 . ' ' . $p2 . ' ' . $p3;
    }
}