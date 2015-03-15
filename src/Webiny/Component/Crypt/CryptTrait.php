<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt;

/**
 * Crypt trait.
 *
 * @package         Webiny\Component\Crypt
 */
trait CryptTrait
{

    /**
     * Get Crypt component instance.
     *
     * @return Crypt
     */
    public function crypt()
    {
        return new Crypt();
    }
}
