<?php
namespace Webiny\Component\Entity\Tests\lib\NoValidation;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Tests\Lib\Classes;

class Many2One extends AbstractEntity
{
    protected static $collection = "NoValidation_Many2One";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('char')->char()->setToArrayDefault();
        $this->attr('relations')->one2many('many2oneNew')->setEntity(Classes::ENTITY_NO_VALIDATION);
    }
}