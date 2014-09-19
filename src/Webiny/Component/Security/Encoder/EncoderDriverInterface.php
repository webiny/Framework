<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Encoder;

/**
 * Encoder driver interface.
 * Every encoder driver must implement this interface so that is compatible with the encoder requirements.
 *
 * @package         Webiny\Component\Security\Encoder
 */

interface EncoderDriverInterface
{

    /**
     * Create a hash for the given password.
     *
     * @param string $password
     *
     * @return string Password hash.
     */
    function createPasswordHash($password);

    /**
     * Verify if the $password matches the $hash.
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool True if $password matches $hash. Otherwise false is returned.
     */
    function verifyPasswordHash($password, $hash);
}