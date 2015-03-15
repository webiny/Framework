<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\ServiceManager;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Exception class for the ServiceManager component.
 *
 * @package         Webiny\Component\ServiceManager
 */
class ServiceManagerException extends ExceptionAbstract
{

    const SERVICE_DEFINITION_NOT_FOUND = 101;
    const SERVICE_IS_NOT_ABSTRACT = 102;
    const SERVICE_CLASS_KEY_NOT_FOUND = 103;
    const SERVICE_CIRCULAR_REFERENCE = 104;
    const INVALID_SERVICE_ARGUMENTS_TYPE = 105;
    const SERVICE_CLASS_DOES_NOT_EXIST = 106;
    const FACTORY_SERVICE_METHOD_KEY_MISSING = 107;
    const SERVICE_NAME_ALREADY_EXISTS = 108;
    const PARAMETER_NOT_FOUND = 109;

    protected static $messages = [
        101 => 'Service "%s" is not defined in services configuration file.',
        102 => 'Service "%s" must contain `abstract` key in order to be available for inheritance.',
        103 => 'Service "%s" must contain `class` or `factory` parameter!',
        104 => 'Service "%s" is creating a circular reference. Check your service definitions and remove circular referencing.',
        105 => 'Service/class "%s" arguments must be in form of an array.',
        106 => 'Service class "%s" does not exist!',
        107 => 'Factory service "%s" `method` key is missing.',
        108 => 'Service with name "%s" already exists.',
        109 => 'Parameter "%s" is not registered but is used in service "%s".'
    ];

}