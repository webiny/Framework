<?php
namespace Webiny\Component\Entity\Tests\Lib\NoValidation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Lib\Classes;

class One2Many extends EntityAbstract
{
    protected static $entityCollection = "NoValidation_One2Many";

    protected function entityStructure()
    {
        $this->attr('char')->char()->setToArrayDefault();
        $this->attr('entity')->many2one()->setEntity(Classes::ENTITY_NO_VALIDATION);
    }
}