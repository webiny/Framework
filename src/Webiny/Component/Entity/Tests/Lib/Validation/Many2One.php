<?php
namespace Webiny\Component\Entity\Tests\Lib\Validation;

use Webiny\Component\Entity\EntityAbstract;

class Many2One extends EntityAbstract
{
    protected static $entityCollection = "Validation_Many2One";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('char')->char()->setValidators('required');
    }
}