<?php
namespace Webiny\Component\Entity\Attribute\Validation\Validators;

use Webiny\Component\Entity\Attribute\AttributeAbstract;
use Webiny\Component\Entity\Attribute\Validation\ValidatorInterface;
use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidationTrait;

class MinLength implements ValidatorInterface
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'minLength';
    }

    /**
     * @inheritDoc
     */
    public function validate(AttributeAbstract $attribute, $data, $params = [])
    {
        try {
            return $this->validation()->validate($data, 'minLength:' . $params[0]);
        } catch (ValidationException $e) {
            throw ExceptionFactory::getInstance()->attributeValidationException($attribute, $e);
        }
    }
}