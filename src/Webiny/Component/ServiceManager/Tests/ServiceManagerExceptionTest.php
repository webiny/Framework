<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\ServiceManager\ServiceManager;
use Webiny\Component\ServiceManager\ServiceManagerException;
use Webiny\Component\ServiceManager\Tests\Classes\ConstructorArgumentClass;

class ServiceManagerExceptionTest extends PHPUnit_Framework_TestCase
{

    protected static $services = [
        'First' => [
            'Class' => '%ExceptionService.Class%'
        ],

        'Second' => [
            'Class'     => ConstructorArgumentClass::class,
            'Arguments' => ['@Exception.Unknown']
        ],

        'Third' => [
            'Class'     => ConstructorArgumentClass::class,
            'Arguments' => ['@Exception.First']
        ]
    ];

    public static function setUpBeforeClass()
    {
        $servicesConfig = new ConfigObject(self::$services);
        ServiceManager::getInstance()->registerServices('Exception', $servicesConfig);
    }

    /**
     * Using inexistent parameter should throw ServiceManagerException
     */
    public function testMissingParameterException()
    {
        $this->setExpectedException(ServiceManagerException::class);
        ServiceManager::getInstance()->getService('Exception.First');
    }

    /**
     * Using inexistent service should throw a ServiceManagerException
     */
    public function testMissingServiceException()
    {
        $this->setExpectedException(ServiceManagerException::class);
        ServiceManager::getInstance()->getService('Exception.Second');
    }

    /**
     * Using services that reference each other should throw a ServiceManagerException
     */
    public function testCircularReferencingException()
    {
        $this->setExpectedException(ServiceManagerException::class);
        ServiceManager::getInstance()->getService('Exception.Third');
    }
}