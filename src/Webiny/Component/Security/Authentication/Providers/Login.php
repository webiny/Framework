<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Authentication\Providers;

/**
 * Login object is a wrapper that holds the username and password submitted by current request amongst some other
 * optional attributes.
 *
 * @package         Webiny\Component\Security\Authentication
 */

class Login
{

    /**
     * @var string
     */
    private $username = '';
    /**
     * @var string
     */
    private $password = '';

    /**
     * @var bool
     */
    private $rememberMe = false;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var integer
     */
    private $timeZoneOffset = 0;

    /**
     * @var string Name of the auth provider.
     */
    protected $authProviderName = '';

    /**
     * Base constructor.
     *
     * @param string $username   Username.
     * @param string $password   Password.
     * @param bool   $rememberMe Is rememberMe set or not.
     *
     * @internal param $timeZoneOffset
     */
    public function __construct($username, $password, $rememberMe = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->rememberMe = $rememberMe;
    }

    /**
     * Sets an optional attribute into the current instance.
     *
     * @param string $name  Attribute name.
     * @param mixed  $value Attribute value.
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Returns the stored attribute for the defined $name.
     *
     * @param string $name Name of the attribute that you wish to return.
     *
     * @return null|mixed Null is returned if attribute doesn't exist, otherwise attribute value is returned.
     */
    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the time zone offset in hours.
     *
     * @param integer $offset Offset in hours.
     */
    public function setTimeZoneOffset($offset)
    {
        $this->timeZoneOffset = intval($offset);
    }

    /**
     * Returns the time zone offset.
     *
     * @return string
     */
    public function getTimeZoneOffset()
    {
        return $this->timeZoneOffset;
    }

    /**
     * Return the status of remember me.
     *
     * @return bool
     */
    public function getRememberMe()
    {
        return $this->rememberMe;
    }

    /**
     * Set the name of auth provider.
     *
     * @param $authProviderName
     */
    public function setAuthProviderName($authProviderName)
    {
        $this->authProviderName = $authProviderName;
    }

    /**
     * Returns the name of auth provider.
     *
     * @return string
     */
    public function getAuthProviderName()
    {
        return $this->authProviderName;
    }
}