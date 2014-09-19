<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authentication\Providers\Form;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Security\Authentication\Providers\Form\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $this->assertInstanceOf('\Webiny\Component\Security\Authentication\Providers\Form\Form', new Form());
    }

    public function testGetLoginObject()
    {
        // mock POST
        $_POST = [
            'username'   => 'un',
            'password'   => 'pw',
            'rememberme' => 'yes'
        ];

        $form = new Form();
        $c = new ConfigObject([]);
        $login = $form->getLoginObject($c);
        $this->assertInstanceOf('\Webiny\Component\Security\Authentication\Providers\Login', $login);
        $this->assertSame('un', $login->getUsername());
        $this->assertSame('pw', $login->getPassword());
        $this->assertSame('yes', $login->getRememberMe());
    }
}