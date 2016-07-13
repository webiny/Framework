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
 * Sphere index (2dSphere)
 *
 * @package Webiny\Component\Mongo\Index
 */
class SphereIndex extends AbstractIndex
{
    /**
     * @param string $name Index name
     * @param string $field Index field
     *
     * @throws \Webiny\Component\Mongo\MongoException
     */
    public function __construct($name, $field)
    {
        if (!is_string($field)) {
            throw new MongoException('Index field must be a string');
        }

        parent::__construct($name, [$field => '2dsphere']);
    }
}