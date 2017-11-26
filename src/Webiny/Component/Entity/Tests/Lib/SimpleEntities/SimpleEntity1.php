<?php
namespace Webiny\Component\Entity\Tests\Lib\SimpleEntities;

use Webiny\Component\Entity\AbstractEntity;

class SimpleEntity1 extends AbstractEntity
{
    protected static $collection = "SimpleEntity1";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('price')->float();
    }
}