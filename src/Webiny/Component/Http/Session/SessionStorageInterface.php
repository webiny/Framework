<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Session;

use Webiny\Component\Config\ConfigObject;

/**
 * SessionStorage interface
 *
 * @package         Webiny\Component\Http\Session
 */
interface SessionStorageInterface extends \SessionHandlerInterface
{

    /**
     * Constructor.
     *
     * @param ConfigObject $config Session config.
     */
    public function __construct(ConfigObject $config);
}