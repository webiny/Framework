<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Entity\Validators\CountryCode;
use Webiny\Component\Entity\Validators\CreditCardNumber;
use Webiny\Component\Entity\Validators\Email;
use Webiny\Component\Entity\Validators\EuVatNumber;
use Webiny\Component\Entity\Validators\GeoLocation;
use Webiny\Component\Entity\Validators\GreaterThan;
use Webiny\Component\Entity\Validators\GreaterThanOrEqual;
use Webiny\Component\Entity\Validators\InArray;
use Webiny\Component\Entity\Validators\Integer;
use Webiny\Component\Entity\Validators\LessThan;
use Webiny\Component\Entity\Validators\LessThanOrEqual;
use Webiny\Component\Entity\Validators\MaxLength;
use Webiny\Component\Entity\Validators\MinLength;
use Webiny\Component\Entity\Validators\Number;
use Webiny\Component\Entity\Validators\Password;
use Webiny\Component\Entity\Validators\Phone;
use Webiny\Component\Entity\Validators\Required;
use Webiny\Component\Entity\Validators\Url;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\ServiceManager\ServiceManagerTrait;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;


/**
 * Entity component main class
 * Use this class to configure Entity component.
 *
 * @package \Webiny\Component\Entity
 */
class Entity
{
    use MongoTrait, ComponentTrait, SingletonTrait, ServiceManagerTrait, StdLibTrait;

    /**
     * @var null|Mongo
     */
    protected static $database = null;

    /**
     * @var array
     */
    private $validators = [];

    /**
     * @var ArrayObject
     */
    private $pool;

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
        $entityPool->key($instance->id, $instance);

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
        $entityPool->removeKey($instance->id);
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

    protected function init()
    {
        // Create entity cache pool
        $this->pool = $this->arr();

        // Load built-in validators
        $builtInValidators = [
            new Email(),
            new GreaterThan(),
            new GreaterThanOrEqual(),
            new GeoLocation(),
            new LessThan(),
            new LessThanOrEqual(),
            new MinLength(),
            new MaxLength(),
            new InArray(),
            new Number(),
            new Integer(),
            new Url(),
            new Password(),
            new Required(),
            new Phone(),
            new CountryCode(),
            new CreditCardNumber(),
            new EuVatNumber()
        ];

        /* @var $v EntityValidatorInterface */
        foreach ($builtInValidators as $v) {
            $this->validators[$v->getName()] = $v;
        }

        // Load validators registered as a service
        $validators = $this->servicesByTag('entity-validator', '\Webiny\Component\Entity\EntityValidatorInterface');
        foreach ($validators as $v) {
            $this->validators[$v->getName()] = $v;
        }
    }
}