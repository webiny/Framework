<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Validation\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Validation\Validation;
use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidationPool;
use Webiny\Component\Validation\ValidationTrait;
use Webiny\Component\Validation\Tests\Classes\Author;
use Webiny\Component\Validation\Tests\Classes\Comment;
use Webiny\Component\Validation\Tests\Classes\Label;
use Webiny\Component\Validation\Tests\Classes\Page;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class ValidationTest extends PHPUnit_Framework_TestCase
{
    use ValidationTrait;

    public function testEmail()
    {
        $this->assertTrue($this->validation()->validate('pavel@webiny.com', ['email']));
        $this->assertTrue($this->validation()->validate('pavel@webiny.com', 'email'));
        $this->assertInternalType('string', $this->validation()->validate(123, ['email'], false));
        $this->assertInternalType('string', $this->validation()->validate('notAnEmail', 'email', false));
        $this->assertInternalType('string', $this->validation()->validate('wrong@domain.123', 'email', false));
        $this->setExpectedException('\Webiny\Component\Validation\ValidationException');
        $this->validation()->validate(123, 'email');
    }

    public function testGreaterThan()
    {
        $this->assertTrue($this->validation()->validate(50, 'gt:21'));
        $this->assertInternalType('string', $this->validation()->validate(123, 'gt:150', false));
        $this->setExpectedException('\Webiny\Component\Validation\ValidationException');
        $this->validation()->validate(123, 'gt:200');
    }
}