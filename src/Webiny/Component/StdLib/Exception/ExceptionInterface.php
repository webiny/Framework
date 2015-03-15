<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */
namespace Webiny\Component\StdLib\Exception;

/**
 * Exception interface.
 * Use it if you want to throw custom exceptions.
 *
 * @package         Webiny\Component\StdLib\Exception
 */
interface ExceptionInterface
{
    /**
     * Constructor
     * Set the exception message that will be thrown.
     * Current line and file will be set as exception origin.
     *
     * Make sure you return:
     * parent::construct($message, $params);
     *
     * @param string|int $message       Message you what to throw. If $message is type of integer,
     *                                  than the method will treat that as an exception code.
     * @param null|array $params        If message has variables inside, send an array of values using this argument,
     *                                  and the variables will be replaced with those values in the same order they appear.
     */
    public function __construct($message, $params = null);
}