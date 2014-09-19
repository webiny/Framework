<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */

namespace Webiny\Component\StdLib\StdObject;

/**
 * Standard object interface.
 *
 * @package         Webiny\Component\StdLib\StdObject
 */

interface StdObjectInterface
{
    /**
     * Constructor.
     * Set standard object value.
     *
     * @param mixed $value
     */
    public function __construct($value);

    /**
     * To string implementation.
     *
     * @return mixed
     */
    public function __toString();
}