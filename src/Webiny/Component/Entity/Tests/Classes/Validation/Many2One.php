<?php
namespace Webiny\Component\Entity\Tests\Classes\Validation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Classes\Classes;

class Many2One extends EntityAbstract
{
    protected static $entityCollection = "Validation_Many2One";

    protected function entityStructure()
    {
        $this->attr('char')->char();
    }
}