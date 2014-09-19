<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Session\Storage;

use Webiny\Component\Cache\CacheException;
use Webiny\Component\Cache\CacheTrait;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Session\SessionException;
use Webiny\Component\Http\Session\SessionStorageInterface;

/**
 * Session storage based on Cache component.
 *
 * @package         Webiny\Component\Http\Session\Storage
 */
class CacheStorage implements SessionStorageInterface
{
    use CacheTrait;

    private $_cacheDriver;
    private $_cache;
    private $_prefix;
    private $_ttl;

    /**
     * @param ConfigObject $config Config options.
     *
     * @throws \Webiny\Component\Http\Session\SessionException
     */
    public function __construct(ConfigObject $config)
    {

        $this->_prefix = $config->get('Storage.Prefix', 'wfs_');
        $this->_ttl = $config->get('Storage.ExpireTime', 86400);
        $this->_cache = $config->get('Storage.Params.Cache', false);

        try {
            $this->_cacheDriver = $this->cache($this->_cache);
        } catch (CacheException $e) {
            throw new SessionException('Unable to get cache driver "' . $this->_cache . '" for session storage.');
        }
    }

    /**
     * PHP >= 5.4.0<br/>
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterafce.close.php
     * @return bool <p>
     *       The return value (usually TRUE on success, FALSE on failure).
     *       Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function close()
    {
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterafce.destroy.php
     *
     * @param int $session_id The session ID being destroyed.
     *
     * @return bool <p>
     *       The return value (usually TRUE on success, FALSE on failure).
     *       Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function destroy($session_id)
    {
        return $this->_cacheDriver->delete($this->_prefix . $session_id);
    }

    /**
     * PHP >= 5.4.0<br/>
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterafce.gc.php
     *
     * @param int $maxlifetime <p>
     *                         Sessions that have not updated for
     *                         the last maxlifetime seconds will be removed.
     *                         </p>
     *
     * @return bool <p>
     *       The return value (usually TRUE on success, FALSE on failure).
     *       Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function gc($maxlifetime)
    {
        return true; // not required because the Cache component auto-expires the records.
    }

    /**
     * PHP >= 5.4.0<br/>
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterafce.open.php
     *
     * @param string $save_path  The path where to store/retrieve the session.
     * @param string $session_id The session id.
     *
     * @return bool <p>
     *       The return value (usually TRUE on success, FALSE on failure).
     *       Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function open($save_path, $session_id)
    {
        return true;
    }

    /**
     * PHP >= 5.4.0<br/>
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterafce.read.php
     *
     * @param string $session_id The session id to read data for.
     *
     * @return string <p>
     *       Returns an encoded string of the read data.
     *       If nothing was read, it must return an empty string.
     *       Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function read($session_id)
    {
        return $this->_cacheDriver->read($this->_prefix . $session_id) ?: '';
    }

    /**
     * PHP >= 5.4.0<br/>
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterafce.write.php
     *
     * @param string $session_id   The session id.
     * @param string $session_data <p>
     *                             The encoded session data. This data is the
     *                             result of the PHP internally encoding
     *                             the $_SESSION superglobal to a serialized
     *                             string and passing it as this parameter.
     *                             Please note sessions use an alternative serialization method.
     *                             </p>
     *
     * @return bool <p>
     *       The return value (usually TRUE on success, FALSE on failure).
     *       Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function write($session_id, $session_data)
    {
        return $this->_cacheDriver->save($this->_prefix . $session_id, $session_data, $this->_ttl);
    }
}