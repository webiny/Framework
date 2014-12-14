<?php
namespace Webiny\Component\Security\User\Providers\Entity;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\Exceptions\UserNotFoundException;
use Webiny\Component\Security\User\UserAbstract;
use Webiny\Component\Security\User\UserProviderInterface;

class Entity implements UserProviderInterface
{

    private $_params;

    public function __construct($params)
    {
        $this->_params = $params;
    }
    
    /**
     * Get the user from user provided for the given instance of Login object.
     *
     * @param Login $login Instance of Login object.
     *
     * @return UserAbstract
     * @throws UserNotFoundException
     */
    function getUser(Login $login)
    {
        $user = new User();
        $user->setParams($this->_params);
        $user->populate($login->getUsername(), $login->getPassword(), []);

        return $user;
    }
}
