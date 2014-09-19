<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\EntityCollection;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;


/**
 * CollectionAttributeAbstract
 * @package Webiny\Component\Entity\AttributeType
 */
abstract class CollectionAttributeAbstract extends AttributeAbstract implements \IteratorAggregate, \ArrayAccess
{
    use StdLibTrait;

    protected $_entityClass;

    /**
     * @var null|EntityCollection
     */
    protected $_value = null;

    /**
     * Get string of masked entity values when array of instances is being converted to string
     *
     * @return mixed|null|string
     */
    /**
     * Get string of masked entity values when array of instances is being converted to string
     *
     * @return mixed|null|string
     */
    public function __toString()
    {
        $references = [];
        foreach ($this->getValue() as $item) {
            $references[] = $item->getMaskedValue();
        }

        return $this->arr($references)->implode(', ')->val();
    }

    /**
     * Add item to this entity collection
     *
     * @param $item
     *
     * @return $this
     */
    public function add($item)
    {
        $this->getValue()->add($item);

        return $this;
    }

    /**
     * Count items in result set
     * @return int
     */
    public function count()
    {
        return $this->getValue()->count();
    }

    /**
     * Delete all items in the result set
     * @return bool
     * @throws EntityException
     */
    public function delete()
    {
        return $this->getValue()->delete();
    }


    /**
     * Set related entity class for this attribute<br>
     * You can either use absolute namespace path or <b>App.Component.Entity</b> notation:<br><br>
     *
     * <b>'Cms.Content.PageEntity'</b> will be translated to: <b>'\WebinyPlatform\Apps\Cms\Components\Content\Entities\PageEntity'</b>
     *
     * @param string $entityClass
     *
     * @return $this
     */
    public function setEntity($entityClass)
    {
        $entityClass = $this->str($entityClass);
        if ($entityClass->contains('.')) {
            $parts = $entityClass->explode('.');
            $entityClass = '\\WebinyPlatform\\Apps\\' . $parts[0] . '\\Components\\' . $parts[1] . '\\Entities\\' . $parts[2];
        }
        $this->_entityClass = StdObjectWrapper::toString($entityClass);

        return $this;
    }

    /**
     * Get related entity class for this attribute
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->_entityClass;
    }

    /**
     * Returns entity instance to which this attribute belongs
     * @return EntityAbstract
     */
    public function getParentEntity()
    {
        return $this->_entity;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->getValue();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->getValue()[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getValue()[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->getValue()[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->getValue()[$offset]);
    }
}