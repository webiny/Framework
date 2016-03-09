<?php
namespace Webiny\Component\Entity\Attribute\Validation\Validators;

use Webiny\Component\Entity\Attribute\AttributeAbstract;
use Webiny\Component\Entity\Attribute\Validation\ValidatorInterface;
use Webiny\Component\Validation\ValidationTrait;

class CountryCode implements ValidatorInterface
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'countryCode';
    }
    
    /**
     * @inheritDoc
     */
    public function validate(AttributeAbstract $attribute, $data, $params = [])
    {
        return $this->validation()->validate($data, 'countryCode');
    }
}