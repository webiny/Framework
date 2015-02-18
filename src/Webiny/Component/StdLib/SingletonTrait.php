<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 * @package   WebinyFramework
 */

namespace Webiny\Component\StdLib;

/**
 * SingletonTrait is a helper, once implemented on a class, automatically adds the singleton pattern to that class.
 *
 * @package         Webiny\Component\StdLib
 */

trait SingletonTrait
{
    protected static $_wfInstance;

    /**
     * Return the current instance.
     * If instance doesn't exist, a new instance will be created.
     *
     * @return $this
     */
    final public static function getInstance()
    {
        if (isset(static::$_wfInstance)) {
            return static::$_wfInstance;
        } else {
            static::$_wfInstance = new static;
            static::$_wfInstance->init();
            static::$_wfInstance->_init();

            return static::$_wfInstance;
        }
    }

    /**
     * Deletes the current singleton instance.
     */
    final public static function deleteInstance()
    {
        static::$_wfInstance = null;
    }

    /**
     * The constructor is set to private to prevent creating new instances.
     * If you want to fire a function after the singleton instance is created, just implement 'init' method into your class.
     */
    final private function __construct()
    {
        //
    }

    /**
     * Override this if you wish to do some stuff once the singleton instance has been created.
     */
    protected function init()
    {
    }

    /**
     * Override this if you wish to do some stuff once the singleton instance has been created.
     */
    protected function _init()
    {
    }

    /**
     * Declare it as private.
     */
    final private function __wakeup()
    {
    }

    /**
     * Declare it as private.
     */
    final private function __clone()
    {
    }
}