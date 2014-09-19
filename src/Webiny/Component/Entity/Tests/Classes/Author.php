<?php
namespace Webiny\Component\Entity\Tests\Classes;

use Webiny\Component\Entity\EntityAbstract;

class Author extends EntityAbstract
{
    protected static $_entityCollection = "Author";
    protected static $_entityMask = "{name} ({id})";

    protected function _entityStructure()
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