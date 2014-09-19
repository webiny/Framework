<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright @ 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
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