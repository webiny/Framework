<?php
namespace Webiny\Component\Entity\Tests\Classes;

use Webiny\Component\Entity\EntityAbstract;

class Comment extends EntityAbstract
{
    protected static $entityCollection = "Comment";
    protected static $entityMask = "Comment #{id}";

    protected function entityStructure()
    {

        // Char
        $this->attr('text')->char();

        // Many2One
        $this->attr('page')->many2one()->setEntity('\Webiny\Component\Entity\Tests\Classes\Page');

    }
}