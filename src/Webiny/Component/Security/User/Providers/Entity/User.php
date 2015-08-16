<?php
namespace Webiny\Component\Security\User\Providers\Entity;

use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\User\UserAbstract;

class User extends UserAbstract
{
    private $entity;
    private $username = 'username';
    private $password = 'password';
    private $role = '';

    public function setParams(array $params)
    {
        if (!isset($params['Entity'])) {
            throw new EntityException('The "Entity" parameter must be defined for the Entity User Provider.');
        } else {
            $this->entity = $params['Entity'];
        }

        if (isset($params['Username'])) {
            $this->username = $params['Username'];
        }

        if (isset($params['Password'])) {
            $this->password = $params['Password'];
        }

        if (isset($params['Role'])) {
            $this->role = $params['Role'];
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
        $user = call_user_func_array([$this->entity, 'findOne'], [[$this->username => $login->getUsername()]]);
        if ($user) {
            if ($firewall->verifyPasswordHash($login->getPassword(), $user[$this->password])) {

                if(isset($user[$this->role])){
                    $role = $user[$this->role];
                }else{
                    $role = $this->role;
                }
                $role = new Role($role);
                $this->setRoles([$role]);

                return true;
            }
        }

        return false;
    }
}
