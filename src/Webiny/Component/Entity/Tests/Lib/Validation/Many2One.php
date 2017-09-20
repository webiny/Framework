<?php
namespace Webiny\Component\Entity\Tests\Lib\Validation;

use Webiny\Component\Entity\AbstractEntity;

class Many2One extends AbstractEntity
{
    protected static $collection = "Validation_Many2One";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('char')->char()->setValidators('required');
    }
}