<?php
namespace Webiny\Component\Entity\Attribute\Validation\Validators;

use Webiny\Component\Entity\Attribute\AbstractAttribute;
use Webiny\Component\Entity\Attribute\Validation\ValidatorInterface;
use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidationTrait;

class InArray implements ValidatorInterface
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'in';
    }

    /**
     * @inheritDoc
     */
    public function validate(AbstractAttribute $attribute, $data, $params = [])
    {
        $params = is_string($params) ? $params : implode(':', $params);

        try {
            return $this->validation()->validate($data, 'in:' . $params);
        } catch (ValidationException $e) {
            throw ExceptionFactory::getInstance()->attributeValidationException($attribute, $e);
        }
    }
}