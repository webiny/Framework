<?php
namespace Webiny\Component\Entity\Attribute\Validation\Validators;

use Webiny\Component\Entity\Attribute\AbstractAttribute;
use Webiny\Component\Entity\Attribute\Validation\ValidationException as AttributeValidationException;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\Validation\ValidationException;

class ExceptionFactory
{
    use SingletonTrait;

    /**
     * @param AbstractAttribute   $attribute
     * @param ValidationException $e
     *
     * @return AttributeValidationException
     */
    public function attributeValidationException(AbstractAttribute $attribute, ValidationException $e)
    {
        $attrException = new AttributeValidationException($e->getMessage());
        $attrException->addError($attribute->getName(), $e->getMessage());

        return $attrException;
    }

}