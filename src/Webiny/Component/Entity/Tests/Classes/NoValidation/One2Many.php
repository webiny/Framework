<?php
namespace Webiny\Component\Entity\Tests\Classes\NoValidation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Classes\Classes;

class One2Many extends EntityAbstract
{
    protected static $entityCollection = "NoValidation_One2Many";

    protected function entityStructure()
    {
        $this->attr('char')->char();
        $this->attr('entity')->many2one()->setEntity(Classes::ENTITY_NO_VALIDATION);
    }
}