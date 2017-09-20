<?php

namespace Webiny\Component\Entity\Tests\Lib\Validation;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Tests\Lib\Classes;

/**
 * Class Entity
 *
 * This class tests all validators except 'required' on relevant attribute types
 *
 * @package Webiny\Component\Entity\Tests\Lib\Validation
 */
class Entity extends AbstractEntity
{
    protected static $collection = "Validation_Entity";

    public function __construct()
    {
        parent::__construct();
        $this->attr('char')->char()->setValidators('minLength:3,maxLength:5,in:abc:def');
        $this->attr('integer')->integer()->setValidators('integer,gt:2,lt:5');
        $this->attr('float')->float()->setValidators('gte:2,lte:5');
        $this->attr('object')->object()->setKeyValidators([
            'key1' => 'required',
            'key2' => 'email'
        ]);
        $this->attr('geoPoint')->geoPoint();
        $this->attr('many2one')->many2one()->setEntity(Classes::MANY_2_ONE_VALIDATION);
        $this->attr('one2many')->one2many('entity')->setEntity(Classes::ONE_2_MANY_VALIDATION)->setValidators('minLength:2');
        $this->attr('many2many')
             ->many2many('Whatever', 'Entity', 'Many2Many')
             ->setEntity(Classes::MANY_2_MANY_NO_VALIDATION)
             ->setValidators('minLength:2');
        $this->attr('vatNumber')->char()->setValidators('euVatNumber');
        $this->attr('creditCardNumber')->char()->setValidators('creditCardNumber');
        $this->attr('email')->char()->setValidators('email');
        $this->attr('number')->char()->setValidators('number');
        $this->attr('password')->char()->setValidators('password');
        $this->attr('url')->char()->setValidators('url');
        $this->attr('phone')->char()->setValidators('phone');
        $this->attr('regex')->char()->setValidators('regex:/^[-+0-9()]+$/');
        $this->attr('unique')->char()->setValidators('unique');
    }
}