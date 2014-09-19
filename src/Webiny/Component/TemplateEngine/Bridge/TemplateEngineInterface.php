<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Bridge;

use Webiny\Component\Config\ConfigObject;

/**
 * Template engine bridge interface.
 *
 * @package         Webiny\Component\TemplateEngine\Bridge
 */
interface TemplateEngineInterface extends \Webiny\Component\TemplateEngine\TemplateEngineInterface
{

    /**
     * Base constructor.
     *
     * @param ConfigObject $config Configuration for the template engine.
     */
    function __construct(ConfigObject $config);

}