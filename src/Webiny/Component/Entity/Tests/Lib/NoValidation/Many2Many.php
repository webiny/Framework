<?php
namespace Webiny\Component\Entity\Tests\Lib\NoValidation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Lib\Classes;

class Many2Many extends EntityAbstract
{
    protected static $entityCollection = "NoValidation_Many2Many";

    protected function entityStructure()
    {
        $this->attr('char')->char();
        $this->attr('many2many')->many2many('NoValidation_Many2Many2Entity')->setEntity(Classes::ENTITY_NO_VALIDATION);
    }
}