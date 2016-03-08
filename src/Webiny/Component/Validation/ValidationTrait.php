<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Validation;

/**
 * A library of Validation functions
 *
 * @package         Webiny\Component\Validation
 */
trait ValidationTrait
{
    /**
     * Get Validation component
     *
     * @return Validation
     */
    protected static function validation()
    {
        return Validation::getInstance();
    }
}