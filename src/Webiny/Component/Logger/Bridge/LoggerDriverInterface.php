<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Bridge;

use Psr\Log\LoggerInterface;


/**
 * Logger driver interface
 * Uses PSR-3 Logger Interface
 *
 * @package   Webiny\Component\Logger\Bridge
 */
interface LoggerDriverInterface extends LoggerInterface
{
    public function setName($name);
}