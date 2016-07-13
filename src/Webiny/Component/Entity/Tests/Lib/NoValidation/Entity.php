<?php
namespace Webiny\Component\Entity\Tests\Lib\NoValidation;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Tests\Lib\Classes;

class Entity extends AbstractEntity
{
    protected static $entityCollection = "NoValidation_Entity";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('boolean')->boolean();
        $this->attr('char')->char()->setToArrayDefault();
        $this->attr('skip')->char()->setSkipOnPopulate();
        $this->attr('after')->char();
        $this->attr('calculation')->integer()->onGet(function ($value, $multiplier = 1) {
            return $value * $multiplier;
        });
        $this->attr('integer')->integer();
        $this->attr('float')->float()->onGet(function ($value, $x = null) {
            return $x ? 2 * $x : $value;
        });
        $this->attr('date')->date()->setToArrayDefault();
        $this->attr('datetime')->datetime()->setToArrayDefault();
        $this->attr('arr')->arr();
        $this->attr('object')->object();
        $this->attr('geoPoint')->geoPoint()->setToArrayDefault();
        $this->attr('dynamic')->dynamic(function () {
            return 'dynamic-value';
        })->setToArrayDefault();
        $this->attr('dynamicDb')->dynamic(function () {
            return 'dynamic-value-db';
        })->setStoreToDb()->setToArrayDefault();
        $this->attr('dynamicWithDefaultParams')->dynamic(function ($multiplier = 2) {
            return $this->integer * $multiplier;
        })->setToArrayDefault();
        $this->attr('dynamicWithParams')->dynamic(function ($multiplier = 1) {
            return $this->integer * $multiplier;
        });
        $this->attr('dynamicEntity')->dynamic(function () {
            return Many2One::findOne(['char' => 'many2oneNew']);
        })->setToArrayDefault();
        $this->attr('dynamicEntityCollection')->dynamic(function () {
            return Many2One::find();
        })->setToArrayDefault();
        $this->attr('many2oneNew')->many2one()->setEntity(Classes::MANY_2_ONE_NO_VALIDATION);
        $this->attr('many2oneExisting')->many2one()->setEntity(Classes::MANY_2_ONE_NO_VALIDATION);
        $this->attr('one2many')->one2many('entity')->setEntity(Classes::ONE_2_MANY_NO_VALIDATION);
        $this->attr('many2many')->many2many('NoValidation_Many2Many2Entity')->setEntity(Classes::MANY_2_MANY_NO_VALIDATION);
    }
}