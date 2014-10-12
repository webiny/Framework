<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Mongo\Index;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Abstract index class
 *
 * @package Webiny\Component\Mongo\Index
 */
abstract class IndexAbstract implements IndexInterface
{
    use StdLibTrait;

    protected $_name = '';
    protected $_fields = [];
    protected $_sparse = false;
    protected $_unique = false;
    protected $_dropDuplicates = false;

    /**
     * @param string $name           Index name
     * @param array  $fields         Index fields, ex: title (ascending), -title (descending)
     * @param bool   $sparse         Is index sparse?
     * @param bool   $unique         Is index unique?
     * @param bool   $dropDuplicates Drop duplicate documents (only if $unique is used)
     */
    public function __construct($name, $fields, $sparse = false, $unique = false, $dropDuplicates = false)
    {
        $this->_name = $name;
        $this->_fields = $fields;
        $this->_unique = $unique;
        $this->_sparse = $sparse;
        $this->_dropDuplicates = $dropDuplicates;

        $this->_normalizeFields();
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setFields($fields)
    {
        $this->_fields = $fields;

        $this->_normalizeFields();

        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    public function setSparse($flag)
    {
        $this->_sparse = $flag;

        return $this;
    }

    public function getSparse()
    {
        return $this->_sparse;
    }

    /**
     * @param boolean $dropDuplicates
     *
     * @return $this
     */
    public function setDropDuplicates($dropDuplicates)
    {
        $this->_dropDuplicates = $dropDuplicates;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDropDuplicates()
    {
        return $this->_dropDuplicates;
    }

    /**
     * @param boolean $unique
     *
     * @return $this
     */
    public function setUnique($unique)
    {
        $this->_unique = $unique;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getUnique()
    {
        return $this->_unique;
    }

    /**
     * Get mongo index options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = [
            'name'     => $this->_name,
            'sparse'   => $this->_sparse,
            'dropDups' => $this->_dropDuplicates,
            'unique'   => $this->_unique
        ];

        return $options;
    }

    /**
     * If fields are in '+/-' syntax, convert them to associative array suitable for Mongo
     *
     * @return void
     */
    protected function _normalizeFields()
    {
        $normalizedFields = [];
        foreach($this->_fields as $key => $field){
            if($this->isNumber($key)){
                $direction = 1;
                if($this->str($field)->startsWith('-')){
                    $direction = -1;
                }
                $normalizedFields[$this->str($field)->trimLeft('-+')->val()] = $direction;
            } else {
                $normalizedFields[$key] = $field;
            }
        }
        $this->_fields = $normalizedFields;
    }
}