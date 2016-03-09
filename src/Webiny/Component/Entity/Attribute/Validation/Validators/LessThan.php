<?php
namespace Webiny\Component\Entity\Attribute\Validation\Validators;

use Webiny\Component\Entity\Attribute\AttributeAbstract;
use Webiny\Component\Entity\Attribute\Validation\ValidatorInterface;
use Webiny\Component\Validation\ValidationTrait;

class LessThan implements ValidatorInterface
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'lt';
    }

    /**
     * @inheritDoc
     */
    public function validate(AttributeAbstract $attribute, $data, $params = [])
    {
        return $this->validation()->validate($data, 'lt:' . $params[0]);
    }
}