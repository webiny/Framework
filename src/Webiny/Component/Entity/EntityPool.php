<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;


/**
 * EntityPool class holds instantiated entities
 *
 * @package Webiny\Component\Entity
 */
class EntityPool
{
    use SingletonTrait, StdLibTrait;

    /**
     * @var ArrayObject
     */
    private $pool;

    protected function init()
    {
        $this->pool = $this->arr();
    }

    /**
     * Get entity instance or false if entity does not exist
     *
     * @param $class
     * @param $id
     *
     * @return bool|EntityAbstract
     */
    public function get($class, $id)
    {
        $entityPool = $this->pool->key($class, $this->arr(), true);

        if ($entityPool->keyExists($id)) {
            return $entityPool->key($id);
        }

        return false;
    }

    /**
     * Add instance to the pool
     *
     * @param $instance
     *
     * @return mixed
     */
    public function add($instance)
    {
        $class = get_class($instance);
        $entityPool = $this->pool->key($class, $this->arr(), true);
        $entityPool->key($instance->getId()->getValue(), $instance);

        return $instance;
    }

    /**
     * Remove instance from pool
     *
     * @param $instance
     *
     * @return bool
     */
    public function remove(EntityAbstract $instance)
    {
        $entityPool = $this->pool->key(get_class($instance), $this->arr(), true);
        $entityPool->removeKey($instance->getId()->getValue());
        unset($instance);

        return true;
    }

    /**
     * Remove all loaded instances from pool
     */
    public function reset()
    {
        $this->pool = $this->arr();
    }

    /**
     * Get entity database
     * @return \Webiny\Component\Mongo\Mongo
     */
    public function getDatabase()
    {
        return Entity::getInstance()->getDatabase();
    }
}