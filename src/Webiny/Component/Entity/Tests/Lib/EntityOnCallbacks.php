<?php
namespace Webiny\Component\Entity\Tests\Lib;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Tests\Lib\Classes;

/**
 * Class Entity
 *
 * This class tests all validators except 'required' on relevant attribute types
 *
 * @package Webiny\Component\Entity\Tests\Lib
 */
class EntityOnCallbacks extends AbstractEntity
{
    protected static $collection = "OnCallbacks_Entity";

    public function  __construct()
    {
        parent::__construct();
        $this->attr('char')->char()->setAfterPopulate()->onSet(function ($value) {
            return 'set-' . $this->number . '-' . $value;
        })->onGet(function ($value) {
            return 'get-' . $value;
        })->onToArray(function () {
            return ['key' => 'value'];
        })->onToDb(function ($value) {
            return 'db-' . $value;
        })->setToArrayDefault();
        $this->attr('number')->integer()->onFromDb(function ($value) {
            return $value * 10;
        });
    }
}