<?php
namespace Webiny\Component\Entity\Tests\Classes\Validation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Classes\Classes;

class Entity extends EntityAbstract
{
    protected static $entityCollection = "Validation_Entity";

    protected function entityStructure()
    {
        $this->attr('char')->char()->setValidators('minLength:3,maxLength:5,in:abc:def');
        $this->attr('integer')->integer()->setValidators('gt:2,lt:5');
        $this->attr('float')->float()->setValidators('gte:2,lte:5');
        $this->attr('object')->object()->setKeyValidators([
            'key1' => 'required',
            'key2' => 'email'
        ]);
        $this->attr('many2one')->many2one()->setEntity(Classes::MANY_2_ONE_NO_VALIDATION);
        $this->attr('one2many')->one2many('entity')->setEntity(Classes::ONE_2_MANY_NO_VALIDATION)->setValidators('minLength:2');
        $this->attr('vatNumber')->char()->setValidators('euVatNumber');
        $this->attr('creditCardNumber')->char()->setValidators('creditCardNumber');
    }
}