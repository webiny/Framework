<?php
namespace Webiny\Component\Entity\Tests\Lib\NoValidation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Lib\Classes;

class Entity extends EntityAbstract
{
    protected static $entityCollection = "NoValidation_Entity";

    protected function entityStructure()
    {
        $this->attr('boolean')->boolean();
        $this->attr('char')->char();
        $this->attr('skip')->char()->setSkipOnPopulate();
        $this->attr('after')->char();
        $this->attr('integer')->integer();
        $this->attr('float')->float();
        $this->attr('date')->date();
        $this->attr('datetime')->datetime();
        $this->attr('arr')->arr();
        $this->attr('object')->object();
        $this->attr('dynamic')->dynamic(function () {
            return 'dynamic-value';
        });
        $this->attr('dynamicDb')->dynamic(function () {
            return 'dynamic-value-db';
        })->setStoreToDb();
        $this->attr('many2oneNew')->many2one()->setEntity(Classes::MANY_2_ONE_NO_VALIDATION);
        $this->attr('many2oneExisting')->many2one()->setEntity(Classes::MANY_2_ONE_NO_VALIDATION);
        $this->attr('one2many')->one2many('entity')->setEntity(Classes::ONE_2_MANY_NO_VALIDATION);
        $this->attr('many2many')->many2many('NoValidation_Many2Many2Entity')->setEntity(Classes::MANY_2_MANY_NO_VALIDATION);
    }
}