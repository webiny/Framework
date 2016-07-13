<?php
namespace Webiny\Component\Security\User\Providers\Entity;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\Exceptions\UserNotFoundException;
use Webiny\Component\Security\User\AbstractUser;
use Webiny\Component\Security\User\UserProviderInterface;

class Entity implements UserProviderInterface
{

    private $params;

    public function __construct($params)
    {
        $this->params = $params;
    }
    
    /**
     * Get the user from user provided for the given instance of Login object.
     *
     * @param Login $login Instance of Login object.
     *
     * @return AbstractUser
     * @throws UserNotFoundException
     */
    public function getUser(Login $login)
    {
        $user = new User();
        $user->setParams($this->params);
        $user->populate($login->getUsername(), $login->getPassword(), []);

        return $user;
    }
}
