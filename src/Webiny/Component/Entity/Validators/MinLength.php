<?php
namespace Webiny\Component\Entity\Validators;

use Webiny\Component\Entity\Attribute\AttributeAbstract;
use Webiny\Component\Entity\EntityValidatorInterface;
use Webiny\Component\Validation\ValidationTrait;

class MinLength implements EntityValidatorInterface
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
        return $this->validation()->validate($data, 'minLength:' . $params[0]);
    }
}