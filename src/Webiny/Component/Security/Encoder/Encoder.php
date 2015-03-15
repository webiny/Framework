<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Encoder;

use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Security encoder class.
 * This class loads the defined encoder and uses it to create a hash from the submitted password and verifies if it
 * matches the password from the user provider.
 *
 * @package         Webiny\Component\Security\Encoder
 */
class Encoder
{
    use StdLibTrait, FactoryLoaderTrait;

    /**
     * @var EncoderInterface
     */
    private $encoderProviderInstance;


    /**
     * @param string     $driver Name of the encoder provider class.
     * @param array|null $params Optional encoder params.
     *
     * @throws EncoderException
     */
    public function __construct($driver, $params = null)
    {
        try {
            $this->encoderProviderInstance = $this->factory($driver,
                                                             '\Webiny\Component\Security\Encoder\EncoderDriverInterface',
                                                             $params
            );
        } catch (\Exception $e) {
            throw new EncoderException($e->getMessage());
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
        return $this->encoderProviderInstance->createPasswordHash($password);
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
        return $this->encoderProviderInstance->verifyPasswordHash($password, $hash);
    }
}