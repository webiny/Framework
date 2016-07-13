<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Mongo\Index;

use Webiny\Component\Mongo\MongoException;

/**
 * Single index
 *
 * @package Webiny\Component\Mongo\Index
 */
class SingleIndex extends AbstractIndex
{
    protected $ttl = false;

    /**
     * @param string $name           Index name
     * @param string $field          Index field, ex: title (ascending), -title (descending)
     * @param bool   $sparse         Is index sparse?
     * @param bool   $unique         Is index unique?
     * @param bool   $dropDuplicates Drop duplicate documents (only if $unique is used)
     * @param bool   $ttl            Document TTL (will only work for date and datetime fields)
     *
     * @throws \Webiny\Component\Mongo\MongoException
     */
    public function __construct($name, $field, $sparse = false, $unique = false, $dropDuplicates = false, $ttl = false)
    {
        $this->ttl = $ttl;
        $fields = $this->isArray($field) ? $field : [$field];

        if(count($fields) > 1) {
            throw new MongoException(MongoException::SINGLE_INDEX_TOO_MANY_FIELDS, [count($fields)]);
        }

        parent::__construct($name, $fields, $sparse, $unique, $dropDuplicates);
    }

    public function getOptions()
    {
        $options = parent::getOptions();
        if($this->isNumber($this->ttl)) {
            $options['ttl'] = $this->ttl;
        }

        return $options;
    }
}