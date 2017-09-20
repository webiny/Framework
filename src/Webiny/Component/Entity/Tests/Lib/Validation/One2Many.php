<?php
namespace Webiny\Component\Entity\Tests\Lib\Validation;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Tests\Lib\Classes;

class One2Many extends AbstractEntity
{
    protected static $collection = "Validation_One2Many";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('char')->char()->setValidators('required');
        $this->attr('entity')->many2one()->setEntity(Classes::ENTITY_VALIDATION);
    }
}