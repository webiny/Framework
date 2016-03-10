<?php
namespace Webiny\Component\Entity\Attribute\Validation\Validators;

use Webiny\Component\Entity\Attribute\AttributeAbstract;
use Webiny\Component\Entity\EntityValidationException;
use Webiny\Component\Entity\Attribute\Validation\ValidatorInterface;

class Unique implements ValidatorInterface
{

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'unique';
    }

    /**
     * @inheritDoc
     */
    public function validate(AttributeAbstract $attribute, $data, $params = [])
    {
        if (empty($data)) {
            return;
        }

        $query = [
            $attribute->attr() => $data
        ];

        $id = $attribute->getEntity()->id;
        if ($id) {
            $query['id'] = [
                '$ne' => $id
            ];
        }

        $exists = call_user_func_array([$attribute->getEntity(), 'findOne'], [$query]);
        if ($exists) {
            throw new EntityValidationException('A record with this attribute value already exists.');
        }
    }
}