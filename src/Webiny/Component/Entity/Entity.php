<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Entity component main class
 * Use this class to configure Entity component.
 *
 * @package \Webiny\Component\Entity
 */
class Entity
{
    use MongoTrait, ComponentTrait, SingletonTrait;

    /**
     * @var null|Mongo
     */
    protected static $database = null;

    /**
     * Get entity database
     * @return Mongo
     */
    public function getDatabase()
    {
        if (self::$database === null) {
            self::$database = self::mongo(self::getConfig()->Database);
        }

        return self::$database;
    }

    /**
     * Set entity database
     *
     * @param Mongo $mongoDatabase
     */
    public function setDatabase(Mongo $mongoDatabase)
    {
        self::$database = $mongoDatabase;
    }

}