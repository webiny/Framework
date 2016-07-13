<?php
namespace Webiny\Component\Entity\Tests\Lib\NoValidation;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Tests\Lib\Classes;

class One2Many extends AbstractEntity
{
    protected static $entityCollection = "NoValidation_One2Many";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('char')->char()->setToArrayDefault();
        $this->attr('entity')->many2one()->setEntity(Classes::ENTITY_NO_VALIDATION);
    }
}