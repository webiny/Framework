<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * EntityMongoAdapter class adapts Mongo values for use with EntityAbstract
 *
 * @package Webiny\Component\Entity
 */
class EntityMongoAdapter
{
    use SingletonTrait;

    public function adaptValues($data)
    {
        // Convert _id to id
        $data['id'] = (string)$data['_id'];
        unset($data['_id']);

        return $data;
    }

}