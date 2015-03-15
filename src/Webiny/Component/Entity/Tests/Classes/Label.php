<?php
namespace Webiny\Component\Entity\Tests\Classes;

use Webiny\Component\Entity\EntityAbstract;

class Label extends EntityAbstract
{
    protected static $entityCollection = "Label";
    protected static $entityMask = "{label} ({id})";

    protected function entityStructure()
    {

        // Char
        $this->attr('label')->char();

        // Many2Many
        $this->attr('pages')->many2many('Label2Page')->setEntity('\Webiny\Component\Entity\Tests\Classes\Page');
    }
}