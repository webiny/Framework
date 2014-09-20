<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config;

/**
 * Helper trait to get the config instance.
 *
 * @package         Webiny\Component\Config
 */
trait ConfigTrait
{

    /**
     * Get Config tool
     *
     * @return Config
     */
    protected static function config()
    {
        return Config::getInstance();
    }
}