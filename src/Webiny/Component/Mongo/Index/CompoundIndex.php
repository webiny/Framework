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
 * Compound index
 *
 * @package Webiny\Component\Mongo\Index
 */
class CompoundIndex extends IndexAbstract
{
    /**
     * @param string $name           Index name
     * @param array  $fields         Index fields, ex: title (ascending), -title (descending), title => 'text'
     * @param bool   $sparse         Is index sparse?
     * @param bool   $unique         Is index unique?
     * @param bool   $dropDuplicates Drop duplicate documents (only if $unique is used)
     *
     * @throws MongoException
     */
    public function __construct($name, array $fields, $sparse = false, $unique = false, $dropDuplicates = false)
    {
        if(count($fields) < 2) {
            throw new MongoException(MongoException::COMPOUND_INDEX_NOT_ENOUGH_FIELDS);
        }

        parent::__construct($name, $fields, $sparse, $unique, $dropDuplicates);
    }
}