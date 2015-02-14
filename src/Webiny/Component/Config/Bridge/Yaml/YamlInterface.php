<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */
namespace Webiny\Component\Config\Bridge\Yaml;

use Webiny\Component\Config\Config;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\StdLib\StdObject\FileObject\FileObject;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Yaml bridge interface
 *
 * @package      Webiny\Component\Config\Bridge\Yaml
 */
interface YamlInterface
{

    /**
     * Get current Yaml value as string
     *
     * @param int $indent
     *
     * @return string
     */
    function getString($indent = 4);

    /**
     * Get Yaml value as array
     *
     * @return array
     */
    function getArray();


    /**
     * Set driver resource to work on
     *
     * @param mixed $resource
     *
     * @return $this
     */
    function setResource($resource);

}