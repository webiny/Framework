<?php
namespace Webiny\Component\Entity\Tests\Lib\NoValidation;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Tests\Lib\Classes;

class Many2Many extends AbstractEntity
{
    protected static $collection = "NoValidation_Many2Many";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('char')->char()->setToArrayDefault();
        $this->attr('many2many')->many2many('NoValidation_Many2Many2Entity', 'Many2Many', 'Entity')->setEntity(Classes::ENTITY_NO_VALIDATION);
    }
}