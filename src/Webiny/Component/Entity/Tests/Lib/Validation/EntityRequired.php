<?php
namespace Webiny\Component\Entity\Tests\Lib\Validation;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Tests\Lib\Classes;

/**
 * Class EntityRequired
 *
 * This class tests 'required' validator on all attribute types
 *
 * @package Webiny\Component\Entity\Tests\Lib\Validation
 */
class EntityRequired extends AbstractEntity
{
    protected static $entityCollection = "Validation_Entity";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('boolean')->boolean()->setValidators('required');
        $this->attr('char')->char()->setValidators('required');
        $this->attr('integer')->integer()->setValidators('required');
        $this->attr('float')->float()->setValidators('required');
        $this->attr('object')->object()->setKeyValidators([
            'key1' => 'required'
        ]);
        $this->attr('many2one')->many2one()->setEntity(Classes::MANY_2_ONE_VALIDATION)->setValidators('required');
        $this->attr('one2many')->one2many('entity')->setEntity(Classes::ONE_2_MANY_VALIDATION)->setValidators('required');
    }
}