<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Driver;

/**
 * @package   Webiny\Component\Storage\Driver
 */

interface AbsolutePathInterface
{
    public function getAbsolutePath($key);
}