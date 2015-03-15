<?php
namespace Webiny\Component\Entity\Tests\Classes;

use Webiny\Component\Entity\EntityAbstract;

class Author extends EntityAbstract
{
    protected static $entityCollection = "Author";
    protected static $entityMask = "{name} ({id})";

    protected function entityStructure()
    {

        // Char
        $this->attr('name')->char();

        // One2Many
        $this->attr('pages')
             ->one2many('author')
             ->setEntity('\Webiny\Component\Entity\Tests\Classes\Page')
             ->setOnDelete('restrict');
    }
}