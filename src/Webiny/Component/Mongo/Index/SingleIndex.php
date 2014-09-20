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
 * Single index
 *
 * @package Webiny\Component\Mongo\Index
 */
class SingleIndex implements IndexInterface
{
    use StdLibTrait;

    private $_name = '';
    private $_fields = [];
    private $_sparse = false;
    private $_unique = false;
    private $_dropDuplicates = false;
    private $_ttl = false;

    /**
     * @param string $name
     * @param array  $fields
     * @param bool   $sparse
     * @param bool   $unique
     * @param bool   $ttl
     * @param bool   $dropDuplicates
     */
    public function __construct($name, array $fields, $sparse = false, $unique = false, $ttl = false,
                                $dropDuplicates = false) {
        $this->_name = $name;
        $this->_fields = $fields;
        $this->_unique = $unique;
        $this->_sparse = $sparse;
        $this->_ttl = $ttl;
        $this->_dropDuplicates = $dropDuplicates;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name) {
        $this->_name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setFields($fields) {
        $this->_fields = $fields;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->_fields;
    }

    public function setSparse($flag) {
        $this->_sparse = $flag;

        return $this;
    }

    public function getSparse() {
        return $this->_sparse;
    }

    /**
     * @param boolean $dropDuplicates
     *
     * @return $this
     */
    public function setDropDuplicates($dropDuplicates) {
        $this->_dropDuplicates = $dropDuplicates;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDropDuplicates() {
        return $this->_dropDuplicates;
    }

    /**
     * @param boolean $unique
     *
     * @return $this
     */
    public function setUnique($unique) {
        $this->_unique = $unique;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getUnique() {
        return $this->_unique;
    }

    public function getOptions() {
        $options = [
            'name'     => $this->_name,
            'sparse'   => $this->_sparse,
            'dropDups' => $this->_dropDuplicates,
            'unique'   => $this->_unique
        ];

        if($this->isNumber($this->_ttl)) {
            $options['ttl'] = $this->_ttl;
        }

        return $options;
    }
}