<?php
namespace Webiny\Component\Security\User\Providers\Entity;

use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\User\UserAbstract;

class User extends UserAbstract
{
    private $_entity;
    private $_username = 'username';
    private $_password = 'password';
    private $_role = '';

    public function setParams(array $params)
    {
        if (!isset($params['Entity'])) {
            throw new EntityException('The "Entity" parameter must be defined for the Entity User Provider.');
        } else {
            $this->_entity = $params['Entity'];
        }

        if (isset($params['Username'])) {
            $this->_username = $params['Username'];
        }

        if (isset($params['Password'])) {
            $this->_password = $params['Password'];
        }

        if (isset($params['Role'])) {
            $this->_role = $params['Role'];
        }
    }

    /**
     * This method verifies the credentials of current user with the credentials provided from the Login object.
     *
     * @param Login    $login
     * @param Firewall $firewall
     *
     * @return bool Return true if credentials are valid, otherwise return false.
     */
    public function authenticate(Login $login, Firewall $firewall)
    {
        $entityInstance = new $this->_entity;
        $user = $entityInstance->find([$this->_username => $login->getUsername()]);
        if ($user && isset($user[0])) {
            $user = $user[0];
            if ($firewall->verifyPasswordHash($login->getPassword(), $user[$this->_password])) {

                if(isset($user[$this->_role])){
                    $role = $user[$this->_role];
                }else{
                    $role = $this->_role;
                }
                $role = new Role($role);
                $this->setRoles([$role]);

                return true;
            }
        }

        return false;
    }
}
