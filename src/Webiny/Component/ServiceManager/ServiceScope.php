<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright @ 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */
namespace Webiny\Component\ServiceManager;

/**
 * ServiceScope class is used for service scope validation and auto completion
 *
 * @package         Webiny\Component\ServiceManager
 */
class ServiceScope
{
    const CONTAINER = 'container';
    const PROTOTYPE = 'prototype';

    public static function exists($scope)
    {
        if ($scope == self::CONTAINER) {
            return true;
        }

        if ($scope == self::PROTOTYPE) {
            return true;
        }

        return false;
    }

}