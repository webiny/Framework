<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Validation;

use Webiny\Component\Entity\Attribute\AttributeAbstract;
use Webiny\Component\Entity\Validation\ValidationException;

interface ValidatorInterface
{
    /**
     * Validate given data
     *
     * @param mixed $data
     * @param AttributeAbstract $attribute
     * @param array $params
     *
     * @throws ValidationException
     * @return void
     */
    public function validate($data, AttributeAbstract $attribute, $params = []);
}