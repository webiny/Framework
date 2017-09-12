<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Validation\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\ServiceManager\ServiceManager;
use Webiny\Component\ServiceManager\ServiceManagerTrait;
use Webiny\Component\Validation\Tests\Lib\CustomValidator;
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
    use ValidationTrait, ServiceManagerTrait;

    public function testEmail()
    {
        $this->assertTrue($this->validation()->validate('pavel@webiny.com', ['email']));
        $this->assertTrue($this->validation()->validate('pavel@webiny.com', 'email'));
        $this->assertInternalType('string', $this->validation()->validate(123, ['email'], false));
        $this->assertInternalType('string', $this->validation()->validate('notAnEmail', 'email', false));
        $this->assertInternalType('string', $this->validation()->validate('wrong@domain.123', 'email', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate(123, 'email');
    }

    public function testGreaterThan()
    {
        $this->assertTrue($this->validation()->validate(50, 'gt:21'));
        $this->assertInternalType('string', $this->validation()->validate(123, 'gt:150', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate(123, 'gt:200');
    }

    public function testGreaterThanOrEqual()
    {
        $this->assertTrue($this->validation()->validate(50, 'gte:21'));
        $this->assertTrue($this->validation()->validate(21, 'gte:21'));
        $this->assertInternalType('string', $this->validation()->validate(123, 'gt:150', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate(123, 'gt:200');
    }

    public function testGeoLocation()
    {
        $geoLocation = ['lat' => 75.25, 'lng' => 0.15];
        $this->assertTrue($this->validation()->validate($geoLocation, 'geoLocation'));
        $this->assertInternalType('string', $this->validation()->validate(123, 'geoLocation', false));
        $this->assertInternalType('string', $this->validation()->validate('not-a-geolocation', 'geoLocation', false));
        $this->assertInternalType('string', $this->validation()->validate(['lat' => 12], 'geoLocation', false));
        $this->assertInternalType('string', $this->validation()->validate(['lng' => 12], 'geoLocation', false));
        $this->assertInternalType('string', $this->validation()->validate([1, 2, 3], 'geoLocation', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate(123, 'geoLocation');
    }

    public function testLessThan()
    {
        $this->assertTrue($this->validation()->validate(10, 'lt:21'));
        $this->assertInternalType('string', $this->validation()->validate(200, 'lt:150', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate(220, 'lt:200');
    }

    public function testLessThanOrEqual()
    {
        $this->assertTrue($this->validation()->validate(21, 'lte:50'));
        $this->assertTrue($this->validation()->validate(21, 'lte:21'));
        $this->assertInternalType('string', $this->validation()->validate(155, 'lte:150', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate(220, 'lte:200');
    }

    public function testMaxLength()
    {
        $this->assertTrue($this->validation()->validate('abc', 'maxLength:5'));
        $this->assertTrue($this->validation()->validate([1, 2, 3], 'maxLength:5'));
        $this->assertInternalType('string', $this->validation()->validate('abcdef', 'maxLength:5', false));
        $this->assertInternalType('string', $this->validation()->validate([1, 2, 3, 4, 5, 6], 'maxLength:5', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('abcd', 'maxLength:2');
    }

    public function testMinLength()
    {
        $this->assertTrue($this->validation()->validate('abc', 'minLength:2'));
        $this->assertTrue($this->validation()->validate([1, 2, 3], 'minLength:2'));
        $this->assertInternalType('string', $this->validation()->validate('ab', 'minLength:3', false));
        $this->assertInternalType('string', $this->validation()->validate([1, 2], 'minLength:3', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('a', 'minLength:2');
    }

    public function testInArray()
    {
        $this->assertTrue($this->validation()->validate('c', 'in:a:b:c'));
        $this->assertInternalType('string', $this->validation()->validate('c', 'in:a:b', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('a', 'in:b:c');
    }

    public function testNumber()
    {
        $this->assertTrue($this->validation()->validate('10', 'number'));
        $this->assertTrue($this->validation()->validate('10', 'number'));
        $this->assertTrue($this->validation()->validate(10.5, 'number'));
        $this->assertInternalType('string', $this->validation()->validate('abc', 'number', false));
        $this->assertInternalType('string', $this->validation()->validate([], 'number', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('a', 'number');
    }

    public function testInteger()
    {
        $this->assertTrue($this->validation()->validate('10', 'integer'));
        $this->assertTrue($this->validation()->validate(12, 'integer'));
        $this->assertInternalType('string', $this->validation()->validate(10.5, 'integer', false));
        $this->assertInternalType('string', $this->validation()->validate('abc', 'integer', false));
        $this->assertInternalType('string', $this->validation()->validate([], 'integer', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('10.5', 'integer');
    }

    public function testUrl()
    {
        $this->assertTrue($this->validation()->validate('http://google.com', 'url'));
        $this->assertTrue($this->validation()->validate('https://google.com/something', 'url'));
        $this->assertTrue($this->validation()->validate('ftp://google.com/something', 'url'));
        $this->assertInternalType('string', $this->validation()->validate('http://', 'url', false));
        $this->assertInternalType('string', $this->validation()->validate(10.5, 'url', false));
        $this->assertInternalType('string', $this->validation()->validate(true, 'url', false));
        $this->assertInternalType('string', $this->validation()->validate('abc', 'url', false));
        $this->assertInternalType('string', $this->validation()->validate([], 'url', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('not-a-url', 'url');
    }

    public function testPassword()
    {
        $this->assertTrue($this->validation()->validate('dev', 'password'));
        $this->assertTrue($this->validation()->validate('admin', 'password'));
        $this->assertTrue($this->validation()->validate('Webiny123!', 'password'));
        $this->assertInternalType('string', $this->validation()->validate('password', 'password', false));
        $this->assertInternalType('string', $this->validation()->validate('123', 'password', false));
        $this->assertInternalType('string', $this->validation()->validate('Password', 'password', false));
        $this->assertInternalType('string', $this->validation()->validate('NoNumbers', 'password', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('12345678', 'password');
    }

    public function testRequired()
    {
        $this->assertTrue($this->validation()->validate('dev', 'required'));
        $this->assertTrue($this->validation()->validate(123, 'required'));
        $this->assertInternalType('string', $this->validation()->validate('', 'required', false));
        $this->assertInternalType('string', $this->validation()->validate(null, 'required', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('', 'required');
    }

    public function testPhone()
    {
        $this->assertTrue($this->validation()->validate('12345-123', 'phone'));
        $this->assertInternalType('string', $this->validation()->validate('', 'phone', false));
        $this->assertInternalType('string', $this->validation()->validate(null, 'phone', false));
        $this->assertInternalType('string', $this->validation()->validate('123/123_123', 'phone', false));
        $this->assertInternalType('string', $this->validation()->validate('123 123 123', 'phone', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('', 'phone');
    }

    public function testCountryCode()
    {
        $this->assertTrue($this->validation()->validate('HR', 'countryCode'));
        $this->assertTrue($this->validation()->validate('DE', 'countryCode'));
        $this->assertInternalType('string', $this->validation()->validate('GER', 'countryCode', false));
        $this->assertInternalType('string', $this->validation()->validate(null, 'countryCode', false));
        $this->assertInternalType('string', $this->validation()->validate(123, 'countryCode', false));
        $this->assertInternalType('string', $this->validation()->validate('de', 'countryCode', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('america', 'countryCode');
    }

    public function testCreditCardNumber()
    {
        $this->assertTrue($this->validation()->validate('4242424242424242', 'creditCardNumber'));
        $this->assertTrue($this->validation()->validate('5555555555554444', 'creditCardNumber'));
        $this->assertTrue($this->validation()->validate('6011111111111117', 'creditCardNumber'));
        $this->assertTrue($this->validation()->validate('30569309025904', 'creditCardNumber'));
        $this->assertTrue($this->validation()->validate('3566002020360505', 'creditCardNumber'));
        $this->assertTrue($this->validation()->validate('371449635398431', 'creditCardNumber'));
        $this->assertInternalType('string', $this->validation()->validate('4242424242424241', 'creditCardNumber', false));
        $this->assertInternalType('string', $this->validation()->validate('randomString123', 'creditCardNumber', false));
        $this->assertInternalType('string', $this->validation()->validate('4135624544434141', 'creditCardNumber', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('4242424242424241', 'creditCardNumber');
    }

    public function testEuVatNumber()
    {
        $this->assertTrue($this->validation()->validate('ATU99999999', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('BE0999999999', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('BG999999999', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('HR12345678901', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('CY99999999L', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('CZ12345678', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('DK99999999', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('FI99999999', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('DE999999999', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('HU12345678', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('NL999999999B99', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('SE999999999901', 'euVatNumber'));
        $this->assertTrue($this->validation()->validate('GB999999973', 'euVatNumber'));
        $this->assertInternalType('string', $this->validation()->validate('123456', 'euVatNumber', false));
        $this->assertInternalType('string', $this->validation()->validate('vatnumber', 'euVatNumber', false));
        $this->assertInternalType('string', $this->validation()->validate('GB9999AB973', 'euVatNumber', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('12345678', 'euVatNumber');
    }

    public function testRegex()
    {
        $this->assertTrue($this->validation()->validate('+385(0)98000-0000', 'regex:/^[-+0-9()]+$/'));
        $this->assertInternalType('string', $this->validation()->validate('abcdefg', 'regex:/^[-+0-9()]+$/', false));
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('abcdefg', 'regex:/^[-+0-9()]+$/');
    }

    public function testCustomValidator()
    {
        $this->validation()->addValidator(new CustomValidator());
        $this->assertTrue($this->validation()->validate(12, 'customValidator'));
        $this->assertInternalType('string', $this->validation()->validate(9, 'customValidator', false));
        $this->assertInternalType('string', $this->validation()->validate(13, 'customValidator', false));
    }

    public function testCustomValidatorService()
    {
        $service = new ConfigObject([
            'Class' => CustomValidator::class,
            'Tags'  => ['validation-plugin']
        ]);

        ServiceManager::getInstance()->registerService('CustomValidator', $service);

        Validation::deleteInstance();
        $this->assertTrue($this->validation()->validate(12, 'customValidator'));
        $this->assertInternalType('string', $this->validation()->validate(9, 'customValidator', false));
        $this->assertInternalType('string', $this->validation()->validate(13, 'customValidator', false));
    }

    public function testUnknownValidator()
    {
        $this->setExpectedException(ValidationException::class);
        $this->validation()->validate('whatever', 'missing');
    }
}