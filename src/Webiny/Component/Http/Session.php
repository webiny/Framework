<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Session\SessionException;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * Session Http component.
 *
 * @package         Webiny\Component\Http
 */
class Session
{
    use StdLibTrait, SingletonTrait;

    /**
     * @var ArrayObject
     */
    private $sessionBag;

    /**
     * @var string
     */
    private $sessionId;


    /**
     * Constructor.
     *
     * @throws Session\SessionException
     */
    protected function init()
    {
        $config = self::getConfig();

        // validate that headers have not already been sent
        if (headers_sent()) {
            throw new SessionException('Unable to register session handler because headers have already been sent.');
        }

        // remove any shut down functions
        session_register_shutdown();

        // get the driver
        $saveHandler = $config->get('Storage.Driver', '\Webiny\Component\Http\Session\Storage\NativeStorage');

        try {
            // try to create driver instance
            $saveHandler = new $saveHandler($config);

            // register driver as session handler
            session_set_save_handler($saveHandler, false);

            // start the session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // get session id
            $this->sessionId = session_id();

            // save current session locally
            $this->sessionBag = $this->arr($_SESSION);
        } catch (\Exception $e) {
            throw new SessionException($e->getMessage());
        }
    }

    /**
     * Get a session value for the given $key.
     * If key doesn't not exist, $value will be returned and assigned under that key.
     *
     * @param string $key   Key for which you wish to get the value.
     * @param mixed  $value Default value that will be returned if $key doesn't exist.
     *
     * @return string Value of the given $key.
     */
    public function get($key, $value = null)
    {
        $return = $this->sessionBag->key($key, $value, true);
        $_SESSION[$key] = $return;

        return $return;
    }

    /**
     * Save, or overwrite, a session value under the given $key with the given $value.
     *
     * @param string $key   Key for which you wish to get the value.
     * @param mixed  $value Value that will be stored under the $key.
     *
     * @return $this
     */
    public function save($key, $value)
    {
        $this->sessionBag->removeKey($key)->append($key, $value);
        $_SESSION[$key] = $value;

        return $this;
    }

    /**
     * Removes the given $key from session.
     *
     * @param string $key Name of the session key you wish to remove.
     *
     * @return bool
     */
    public function delete($key)
    {
        $this->sessionBag->removeKey($key);
        unset($_SESSION[$key]);

        return true;
    }

    /**
     * Get current session id.
     *
     * @return string Session id.
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Get all session values.
     *
     * @return array Key-value array of all session entries.
     */
    public function getAll()
    {
        return $this->sessionBag->val();
    }

    /**
     * Returns session config from Http object.
     *
     * @return ConfigObject
     */
    public static function getConfig()
    {
        return Http::getConfig()->get('Session', new ConfigObject([]));
    }
}