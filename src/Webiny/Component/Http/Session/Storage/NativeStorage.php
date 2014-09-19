<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Session\Storage;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Session\SessionStorageInterface;

/**
 * Native session storage.
 *
 * @package         Webiny\Component\Http\Session\Storage
 */
class NativeStorage extends \SessionHandler implements SessionStorageInterface
{

    /**
     * Constructor.
     *
     * @param ConfigObject $config Session config.
     */
    public function __construct(ConfigObject $config)
    {

    }

}