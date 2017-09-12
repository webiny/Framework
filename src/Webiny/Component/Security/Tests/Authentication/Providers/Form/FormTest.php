<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Authentication\Providers\Form;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\Request;
use Webiny\Component\Security\Authentication\Providers\Form\Form;
use Webiny\Component\Security\Authentication\Providers\Login;

/**
 * Class FormTest
 * @package Webiny\Component\Security\Tests\Authentication\Providers\Form
 */
class FormTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $this->assertInstanceOf(Form::class, new Form());
    }

    public function testGetLoginObject()
    {
        Request::deleteInstance();

        // mock POST
        $_POST = [
            'username'   => 'un',
            'password'   => 'pw',
            'rememberme' => true
        ];

        $form = new Form();
        $c = new ConfigObject([]);
        $login = $form->getLoginObject($c);
        $this->assertInstanceOf(Login::class, $login);
        $this->assertSame('un', $login->getUsername());
        $this->assertSame('pw', $login->getPassword());
        // We pass an empty configuration to Form provider, which means `RememberMe` is disabled and we expect to receive `false`
        $this->assertSame(false, $login->getRememberMe());
    }
}