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

    protected $name = '';
    protected $fields = [];
    protected $sparse = false;
    protected $unique = false;
    protected $dropDuplicates = false;

    /**
     * @param string $name           Index name
     * @param array  $fields         Index fields, ex: title (ascending), -title (descending)
     * @param bool   $sparse         Is index sparse?
     * @param bool   $unique         Is index unique?
     * @param bool   $dropDuplicates Drop duplicate documents (only if $unique is used)
     */
    public function __construct($name, $fields, $sparse = false, $unique = false, $dropDuplicates = false)
    {
        $this->name = $name;
        $this->fields = $fields;
        $this->unique = $unique;
        $this->sparse = $sparse;
        $this->dropDuplicates = $dropDuplicates;

        $this->normalizeFields();
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        $this->normalizeFields();

        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function setSparse($flag)
    {
        $this->sparse = $flag;

        return $this;
    }

    public function getSparse()
    {
        return $this->sparse;
    }

    /**
     * @param boolean $dropDuplicates
     *
     * @return $this
     */
    public function setDropDuplicates($dropDuplicates)
    {
        $this->dropDuplicates = $dropDuplicates;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDropDuplicates()
    {
        return $this->dropDuplicates;
    }

    /**
     * @param boolean $unique
     *
     * @return $this
     */
    public function setUnique($unique)
    {
        $this->unique = $unique;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getUnique()
    {
        return $this->unique;
    }

    /**
     * Get mongo index options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = [
            'name'     => $this->name,
            'sparse'   => $this->sparse,
            'dropDups' => $this->dropDuplicates,
            'unique'   => $this->unique
        ];

        return $options;
    }

    /**
     * If fields are in '+/-' syntax, convert them to associative array suitable for Mongo
     *
     * @return void
     */
    protected function normalizeFields()
    {
        $normalizedFields = [];
        foreach($this->fields as $key => $field){
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
        $this->fields = $normalizedFields;
    }
}