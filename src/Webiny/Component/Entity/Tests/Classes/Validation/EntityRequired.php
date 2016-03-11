<?php
namespace Webiny\Component\Entity\Tests\Classes\Validation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Classes\Classes;

class EntityRequired extends EntityAbstract
{
    protected static $entityCollection = "Validation_Entity";

    protected function entityStructure()
    {
        $this->attr('boolean')->boolean()->setValidators('required');
        $this->attr('char')->char()->setValidators('required');
        $this->attr('integer')->integer()->setValidators('required');
        $this->attr('float')->float()->setValidators('required');
        $this->attr('object')->object()->setKeyValidators([
            'key1' => 'required'
        ]);
        $this->attr('many2one')->many2one()->setEntity(Classes::MANY_2_ONE_NO_VALIDATION)->setValidators('required');
        $this->attr('one2many')->one2many('entity')->setEntity(Classes::ONE_2_MANY_NO_VALIDATION)->setValidators('required');
    }
}