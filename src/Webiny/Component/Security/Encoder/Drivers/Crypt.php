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
    private $instance = null;
    

    /**
     * Constructor
     *
     * @throws \Exception
     */
    public function __construct()
    {
        try {
            $this->instance = $this->crypt();
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
    public function createPasswordHash($password)
    {
        return $this->instance->createPasswordHash($password);
    }

    /**
     * Verify if the $password matches the $hash.
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool True if $password matches $hash. Otherwise false is returned.
     */
    public function verifyPasswordHash($password, $hash)
    {
        return $this->instance->verifyPasswordHash($password, $hash);
    }
}