<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Driver;

/**
 * @package  Webiny\Component\Storage\Driver
 */

interface TouchableInterface
{
    /**
     * Touch a file (change time modified)
     *
     * @param $key
     *
     * @return mixed
     */
    public function touchKey($key);
}