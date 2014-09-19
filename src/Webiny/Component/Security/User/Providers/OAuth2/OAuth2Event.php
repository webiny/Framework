<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User\Providers\OAuth2;

use Webiny\Component\EventManager\Event;
use Webiny\Component\OAuth2\OAuth2;
use Webiny\Component\OAuth2\OAuth2User;

/**
 * This class is passed along with events fired by OAuth2 user provider.
 *
 * @package         Webiny\Component\Security\User\Providers\OAuth2
 */
class OAuth2Event extends Event
{

    const OAUTH2_AUTH_SUCCESS = 'wf.security.user.oauth2';

    /**
     * @var \Webiny\Component\OAuth2\OAuth2User
     */
    private $_oauth2User;

    /**
     * @var \Webiny\Component\OAuth2\OAuth2
     */
    private $_oauth2;

    /**
     * Base constructor.
     *
     * @param OAuth2User $OAuth2User The current user.
     * @param OAuth2     $OAuth2     OAuth2 class, containing the access key.
     */
    public function __construct(OAuth2User $OAuth2User, OAuth2 $OAuth2)
    {
        $this->_oauth2User = $OAuth2User;
        $this->_oauth2 = $OAuth2;
    }

    /**
     * Returns current user.
     *
     * @return null|OAuth2User
     */
    public function getUser()
    {
        return $this->_oauth2User;
    }

    /**
     * Get OAuth2 instance used to authenticate the user.
     * It holds an active access key.
     *
     * @return OAuth2
     */
    public function getOAuth2Instance()
    {
        return $this->_oauth2;
    }
}