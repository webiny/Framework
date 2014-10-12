<?php
namespace Webiny\Component\Entity\Tests\Classes;

use Webiny\Component\Entity\EntityAbstract;

class Page extends EntityAbstract
{
    protected static $_entityCollection = "Page";
    protected static $_entityMask = "{title} ({id})";

    protected function _entityStructure()
    {

        // Char
        $this->attr('title')->char();

        // Many2One
        $this->attr('author')->many2one()->setEntity('\Webiny\Component\Entity\Tests\Classes\Author');

        // DateTime attribute
        $this->attr('createdOn')->datetime()->setOnce()->setDefaultValue('now');

        // Date attribute
        $this->attr('publishOn')->date();

        // Date attribute
        $this->attr('remindOn')->date();

        // Recursive
        $this->attr('parent')->many2one()->setEntity('\Webiny\Component\Entity\Tests\Classes\Page');

        // One2Many
        $this->attr('comments')->one2many('page')->setEntity('\Webiny\Component\Entity\Tests\Classes\Comment');

        // Many2Many
        $this->attr('labels')->many2many('Label2Page')->setEntity('\Webiny\Component\Entity\Tests\Classes\Label');

        // Array
        $this->attr('settings')->arr();
    }
}