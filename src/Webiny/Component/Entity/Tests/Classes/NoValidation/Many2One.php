<?php
namespace Webiny\Component\Entity\Tests\Classes\NoValidation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Classes\Classes;

class Many2One extends EntityAbstract
{
    protected static $entityCollection = "NoValidation_Many2One";

    protected function entityStructure()
    {
        $this->attr('char')->char();
        $this->attr('relations')->one2many('many2one')->setEntity(Classes::ENTITY_NO_VALIDATION);
    }
}