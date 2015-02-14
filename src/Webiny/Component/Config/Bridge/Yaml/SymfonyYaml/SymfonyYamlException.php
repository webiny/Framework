<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */
namespace Webiny\Component\Config\Bridge\Yaml\Spyc;

use Webiny\Component\Config\Bridge\Yaml\YamlException;
use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Spyc bridge exception class.
 *
 * @package      Webiny\Component\Config\Bridge\Yaml\Spyc
 */
class SymfonyYamlException extends YamlException
{
    const UNABLE_TO_PARSE = 101;

    protected static $_messages = [
        101 => 'SymfonyYaml Bridge - Unable to parse given resource of type %s'
    ];
}