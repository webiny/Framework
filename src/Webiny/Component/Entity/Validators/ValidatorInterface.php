<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Validators;

use Webiny\Component\Entity\Attribute\AttributeAbstract;
use Webiny\Component\Entity\Attribute\ValidationException;

interface ValidatorInterface
{
    /**
     * Validate given data
     *
     * @param mixed $data
     *
     * @throws ValidationException
     * @return void
     */
    public function validate($data, AttributeAbstract $attribute, $params = null);
}