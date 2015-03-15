<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Role;

/**
 * This class holds the name of current role.
 *
 * @package         Webiny\Component\Security\Authorization
 */

class Role
{

    /**
     * Role name
     * @var string
     */
    private $role;


    /**
     * Constructor.
     *
     * @param string $role The role name.
     */
    public function __construct($role)
    {
        $this->role = (string)$role;
    }

    /**
     * Returns the name of the role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
}