<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute\Validation;

use Webiny\Component\Entity\Attribute\AbstractAttribute;

interface ValidatorInterface
{

    /**
     * Get validator name, eg: email
     *
     * @return string
     */
    public function getName();

    /**
     * Validate given data
     *
     * @param AbstractAttribute $attribute
     * @param mixed             $data
     * @param array             $params
     *
     * @throws ValidationException
     * @return boolean
     */
    public function validate(AbstractAttribute $attribute, $data, $params = []);
}