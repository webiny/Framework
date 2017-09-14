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
abstract class AbstractIndex implements IndexInterface
{
    use StdLibTrait;

    protected $name = '';
    protected $fields = [];
    protected $sparse = false;
    protected $unique = false;
    protected $dropDuplicates = false;

    /**
     * @param string     $name Index name
     * @param array      $fields Index fields, ex: title (ascending), -title (descending)
     * @param bool|array $sparse If boolean `true`, creates a `sparse` index. If array, creates a partial index.
     * @param bool       $unique Is index unique?
     * @param bool       $dropDuplicates Drop duplicate documents (only if $unique is used)
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function getSparse()
    {
        return $this->sparse;
    }

    /**
     * @return boolean
     */
    public function getDropDuplicates()
    {
        return $this->dropDuplicates;
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
            'sparse'   => is_bool($this->sparse) ? $this->sparse : false,
            'dropDups' => $this->dropDuplicates,
            'unique'   => $this->unique
        ];

        if (is_array($this->sparse)) {
            $options['partialFilterExpression'] = $this->sparse;
        }

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
        foreach ($this->fields as $key => $field) {
            if ($this->isNumber($key)) {
                $direction = 1;
                if (substr($field, 0, 1) === '-') {
                    $direction = -1;
                }
                $normalizedFields[ltrim($field, '-+')] = $direction;
            } else {
                $normalizedFields[$key] = $field;
            }
        }
        $this->fields = $normalizedFields;
    }
}