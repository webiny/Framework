<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Encoder\Drivers;

use Webiny\Component\Crypt\CryptTrait;
use Webiny\Component\Security\Encoder\EncoderDriverInterface;

/**
 * This is the Crypt implementation of EncoderProviderInterface.
 *
 * @package         Webiny\Component\Security\Encoder\Drivers
 */
class Crypt implements EncoderDriverInterface
{
    use CryptTrait;

    /**
     * @var null|\Webiny\Component\Crypt\Crypt
     */
    private $_instance = null;

    /**
     * Constructor
     *
     * @param string $cryptName Name of the crypt service.
     *
     * @throws \Exception
     */
    public function __construct($cryptName)
    {
        try {
            $this->_instance = $this->crypt($cryptName);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a hash for the given password.
     *
     * @param string $password
     *
     * @return string Password hash.
     */
    function createPasswordHash($password)
    {
        return $this->_instance->createPasswordHash($password);
    }

    /**
     * Verify if the $password matches the $hash.
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool True if $password matches $hash. Otherwise false is returned.
     */
    function verifyPasswordHash($password, $hash)
    {
        return $this->_instance->verifyPasswordHash($password, $hash);
    }
}